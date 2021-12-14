<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\App\Telegram\Command;

use DateTimeImmutable;
use Longman\TelegramBot\Entities\Update;
use Nikitades\ToxicAvenger\App\Telegram\Command\FindToxicCommand;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Tests\Unit\AbstractTelegramCommandTest;
use Symfony\Component\Uid\Uuid;

class FindToxicCommandTest extends AbstractTelegramCommandTest
{
    /**
     * @dataProvider provideTestData
     * @param array<array{username: string, usages: array<array{wordId: string, usagesCount: int}>}> $badWordsUsageCount
     * @param array<Uuid> $uniqueBadWordIds
     * @param array<BadWordLibraryRecord> $badWordsUsed
     */
    public function testExecute(
        int $messageId,
        int $chatId,
        int $userId,
        int $time,
        string $text,
        /* -- */
        array $badWordsUsageCount,
        array $uniqueBadWordIds,
        array $badWordsUsed,
        /* -- */
        string $expectedString,
    ): void {
        $badWordUsageRecordRepository = $this->createMock(BadWordUsageRecordRepositoryInterface::class);
        $badWordUsageRecordRepository->expects(static::once())
            ->method('findUsersWithBadWordUsageCount')
            ->with($chatId, 5)
            ->willReturn($badWordsUsageCount);

        $badWordsLibrary = $this->createMock(BadWordsLibrary::class);
        $badWordsLibrary->expects(static::exactly([] === $badWordsUsageCount ? 0 : 1))
            ->method('findManyById')
            ->with($uniqueBadWordIds)
            ->willReturn($badWordsUsed);

        $this->setCommandDependencies(
            badWordUsageRecordRepository: $badWordUsageRecordRepository,
            badWordsLibrary: $badWordsLibrary,
        );

        (new FindToxicCommand(
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
     * @return iterable<mixed>
     */
    public function provideTestData(): iterable
    {
        yield 'no users found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'time' => time(),
            'text' => '/findToxic',
            'badWordsUsageCount' => [],
            'uniqueBadWordIds' => [],
            'badWordsUsed' => [],
            'expectedString' => 'chat_id=2222&text=%E2%9D%A4%EF%B8%8F+No+toxic+users+found%21+%E2%9D%A4%EF%B8%8F&parse_mode=markdown',
        ];

        yield 'some users found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'time' => time(),
            'text' => '/findToxic',
            'badWordsUsageCount' => [
                [
                    'username' => 'nikitades',
                    'usages' => [
                        [
                            'wordId' => '8d553f49-f00d-43fa-9c05-59c14ae8cb3f',
                            'usagesCount' => 12,
                        ],
                        [
                            'wordId' => '1f53475d-56e9-4b63-ae03-5dad05359864',
                            'usagesCount' => 6,
                        ],
                        [
                            'wordId' => 'e01e3a1d-1d3b-4f0a-b509-85cad797b103',
                            'usagesCount' => 3,
                        ],
                    ],
                ],
                [
                    'username' => 'nikitades2',
                    'usages' => [
                        [
                            'wordId' => '1f53475d-56e9-4b63-ae03-5dad05359864',
                            'usagesCount' => 16,
                        ],
                        [
                            'wordId' => 'c1cc0b85-abe5-40f4-b093-0c6e911688a5',
                            'usagesCount' => 11,
                        ],
                    ],
                ],
            ],
            'uniqueBadWordIds' => [
                Uuid::fromString('8d553f49-f00d-43fa-9c05-59c14ae8cb3f'),
                Uuid::fromString('1f53475d-56e9-4b63-ae03-5dad05359864'),
                Uuid::fromString('e01e3a1d-1d3b-4f0a-b509-85cad797b103'),
                Uuid::fromString('c1cc0b85-abe5-40f4-b093-0c6e911688a5'),
            ],
            'badWordsUsed' => [
                new BadWordLibraryRecord(
                    id: Uuid::fromString('8d553f49-f00d-43fa-9c05-59c14ae8cb3f'),
                    telegramChatId: 2222,
                    telegramMessageId: 1234,
                    text: 'php',
                    active: true,
                    updatedAt: new DateTimeImmutable('@' . time()),
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('1f53475d-56e9-4b63-ae03-5dad05359864'),
                    telegramChatId: 2222,
                    telegramMessageId: 2143,
                    text: 'is',
                    active: true,
                    updatedAt: new DateTimeImmutable('@' . time()),
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('e01e3a1d-1d3b-4f0a-b509-85cad797b103'),
                    telegramChatId: 2222,
                    telegramMessageId: null,
                    text: 'hypertext',
                    active: true,
                    updatedAt: null,
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('c1cc0b85-abe5-40f4-b093-0c6e911688a5'),
                    telegramChatId: 2222,
                    telegramMessageId: 4123,
                    text: 'preprocessor',
                    active: true,
                    updatedAt: new DateTimeImmutable('@' . time()),
                ),
            ],
            'expectedString' => 'chat_id=2222&text=Most+toxic+users%3A%0A%40nikitades+with++%2812%29%2C++%286%29%2C++%283%29%2C%0A%2A%2A%2Atotal%2A%2A%2A%3A+21%2C%0A%0A%40nikitades2+with++%2816%29%2C++%2811%29%2C%0A%2A%2A%2Atotal%2A%2A%2A%3A+27&parse_mode=markdown',
        ];
    }
}
