<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\App\Telegram\Command;

use DateTimeImmutable;
use Longman\TelegramBot\Entities\Update;
use Nikitades\ToxicAvenger\App\Telegram\Command\IsToxicCommand;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordFrequencyRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Entity\User;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\UserRepositoryInterface;
use Nikitades\ToxicAvenger\Tests\Unit\AbstractTelegramCommandTest;
use Symfony\Component\Uid\Uuid;

class IsToxicCommandTest extends AbstractTelegramCommandTest
{
    /**
     * @dataProvider provideTestData
     * @param array<Uuid> $usedBadWordIds
     * @param array<BadWordLibraryRecord> $usedBadWords
     * @param array<BadWordFrequencyRecord> $usedBadWordFrequencies
     */
    public function testExecute(
        int $messageId,
        int $chatId,
        int $userId,
        string $username,
        int $time,
        string $text,
        /* -- */
        ?User $foundUser,
        array $usedBadWordIds,
        array $usedBadWords,
        array $usedBadWordFrequencies,
        /* -- */
        string $expectedString,
    ): void {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(static::once())
            ->method('findByUsername')
            ->with($username)
            ->willReturn($foundUser);

        $badWordUsageRepository = $this->createMock(BadWordUsageRecordRepositoryInterface::class);
        $badWordUsageRepository->expects(static::exactly(null === $foundUser ? 0 : 1))
            ->method('findBadWordIdsFromUser')
            ->with($userId, $chatId)
            ->willReturn($usedBadWordIds);
        $badWordUsageRepository->expects(static::exactly([] === $usedBadWordFrequencies ? 0 : 1))
            ->method('getBadWordsUsageFrequencyForList')
            ->with($userId, $chatId, $usedBadWords)
            ->willReturn($usedBadWordFrequencies);

        $badWordsLibrary = $this->createMock(BadWordsLibrary::class);
        $badWordsLibrary->expects(static::exactly([] === $usedBadWords ? 0 : 1))
            ->method('findManyById')
            ->willReturn($usedBadWords);

        $this->setCommandDependencies(
            userRepository: $userRepository,
            badWordUsageRecordRepository: $badWordUsageRepository,
            badWordsLibrary: $badWordsLibrary,
        );

        (new IsToxicCommand(
            telegram: $this->telegram,
            update: new Update(
                data: [
                    'message' => [
                        'message_id' => $messageId,
                        'chat' => [
                            'id' => $chatId,
                        ],
                        'from' => [
                            'id' => $userId,
                            'username' => 'nikitades',
                        ],
                        'date' => $time,
                        'text' => $text,
                    ],
                ],
                bot_username: 'bot',
            ),
        ))->execute();

        static::assertCount(1, $this->httpClientContainer->getHistory());
        $request = $this->httpClientContainer->getRequestOfNumber(0);
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(
            $expectedString,
            $request->getBody()->getContents(),
        );
    }

    /**
     * @return iterable<array<string,int|string|User|array<Uuid|BadWordLibraryRecord|BadWordFrequencyRecord>|null>>
     */
    public function provideTestData(): iterable
    {
        $user = new User(
            id: Uuid::fromString('e85bbcde-716e-4e16-80f0-7861311f3c63'),
            telegramId: 3333,
            name: 'nikitades',
            addedAt: new DateTimeImmutable('@' . time()),
        );

        yield 'no user found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'username' => 'nonexistentuser',
            'time' => time(),
            'text' => '/isToxic @nonexistentuser',
            /* -- */
            'foundUser' => null,
            'usedBadWordIds' => [],
            'usedBadWords' => [],
            'usedBadWordFrequencies' => [],
            /* -- */
            'expectedString' => 'chat_id=2222&text=User+%40nonexistentuser+is+not+found&parse_mode=markdown',
        ];

        yield 'no bad word library records found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'username' => 'nikitades',
            'time' => time(),
            'text' => '/isToxic @nikitades',
            /* -- */
            'foundUser' => $user,
            'usedBadWordIds' => [],
            'usedBadWords' => [],
            'usedBadWordFrequencies' => [],
            /* -- */
            'expectedString' => 'chat_id=2222&text=%E2%9D%A4%EF%B8%8F+No%2C+user+%40nikitades+is+not+toxic%21+%E2%9D%A4%EF%B8%8F&parse_mode=markdown',
        ];

        yield 'bad word library records are found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'username' => 'nikitades',
            'time' => time(),
            'text' => '/isToxic @nikitades',
            /* -- */
            'foundUser' => $user,
            'usedBadWordIds' => [
                Uuid::fromString('25d89c62-710a-4b3f-961c-194c182d502f'),
                Uuid::fromString('9d3d2309-48e5-42cd-829b-bcfd7f2ad642'),
                Uuid::fromString('d2122035-9368-4466-bf35-3994fd42114e'),
            ],
            'usedBadWords' => [
                new BadWordLibraryRecord(
                    id: Uuid::fromString('25d89c62-710a-4b3f-961c-194c182d502f'),
                    telegramChatId: 2222,
                    telegramMessageId: 1111,
                    text: 'incapsulation',
                    active: true,
                    updatedAt: null,
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('9d3d2309-48e5-42cd-829b-bcfd7f2ad642'),
                    telegramChatId: 2222,
                    telegramMessageId: null,
                    text: 'polymorphism',
                    active: true,
                    updatedAt: null,
                ),
            ],
            'usedBadWordFrequencies' => [
                new BadWordFrequencyRecord(
                    word: 'incapsulation',
                    usagesCount: 3,
                ),
                new BadWordFrequencyRecord(
                    word: 'polymorphism',
                    usagesCount: 12,
                ),
            ],
            /* -- */
            'expectedString' => 'chat_id=2222&text=%E2%9A%A0%EF%B8%8F+Yes%2C+user+%40nikitades+is+toxic%21+Especially+for+%2A%2A+%2812%29+%E2%9A%A0%EF%B8%8F&parse_mode=markdown',
        ];
    }
}
