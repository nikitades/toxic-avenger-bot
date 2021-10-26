<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\App\Telegram\Command;

use Safe\DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Longman\TelegramBot\Entities\Update;
use Nikitades\ToxicAvenger\App\CommandDependencies;
use Nikitades\ToxicAvenger\App\Telegram\Command\AddBadWordCommand;
use Nikitades\ToxicAvenger\Domain\Command\AddBadWordToLibrary\AddBadWordToLibraryCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Tests\Unit\GenericTelegramCommandTest;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use stdClass;

class AddBadWordCommandTest extends GenericTelegramCommandTest
{
    /**
     * @dataProvider provideTestData
     * @param array<BadWordLibraryRecord> $badWordLibraryRecords
     */
    public function testExecute(
        int $messageId,
        int $chatId,
        int $userId,
        string $text,
        int $time,
        array $badWordLibraryRecords,
        string $expectedString,
    ): void {
        $this->httpClientContainer->remember([new Response(status: 200, headers: [], body: '{}')]);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(static::once())
            ->method('dispatch')
            ->with(
                new AddBadWordToLibraryCommand(
                    text: $text,
                    telegramChatId: $chatId,
                    telegramMessageId: $messageId,
                    telegramUserId: $userId,
                    addedAt: (new DateTimeImmutable('now'))->setTimestamp($time)
                )
            )
            ->willReturn(new Envelope(new stdClass()));

        $badWordLibraryRecordRepository = $this->createMock(BadWordLibraryRecordRepositoryInterface::class);
        $badWordLibraryRecordRepository->expects(static::once())
            ->method('findAddedByMessageId')
            ->with($messageId)
            ->willReturn($badWordLibraryRecords);

        $deps = new CommandDependencies(
            messageBusInterface: $messageBus,
            badWordLibraryRecordRepository: $badWordLibraryRecordRepository,
        );

        (new AddBadWordCommand(
            telegram: $this->telegram,
            update: new Update(
                data: [
                    'message' => [
                        'message_id' => 1111,
                        'chat' => [
                            'id' => 2222,
                        ],
                        'from' => [
                            'id' => 3333,
                        ],
                        'date' => $time,
                        'text' => '/addbadword ' . $text,
                    ],
                ],
                bot_username: 'bot',
            ),
            commandDependencies: $deps
        ))->execute();

        static::assertCount(1, $this->httpClientContainer->getHistory());
        $request = $this->httpClientContainer->getRequestOfNumber(0);
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(
            $expectedString,
            $request->getBody()->getContents()
        );
    }

    /**
     * @return iterable<string,array<string,int|array<BadWordLibraryRecord>|string>>
     */
    public function provideTestData(): iterable
    {
        yield 'no bad word library records found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'text' => 'privet',
            'time' => time(),
            'badWordLibraryRecords' => [],
            'expectedString' => 'chat_id=2222&text=No+new+words+registered&parse_mode=markdown',
        ];

        yield 'bad word library records are found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'text' => 'privet',
            'time' => time(),
            'badWordLibraryRecords' => [
                new BadWordLibraryRecord(
                    id: Uuid::fromString('dc169373-3d6c-4c21-a215-2438662cd1d1'),
                    telegramChatId: 2222,
                    telegramMessageId: 1111,
                    text: 'Lorem',
                    active: true,
                    addedAt: (new DateTimeImmutable('now'))->setTimestamp(time())
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('49fce569-bde6-47e9-9181-122b2281d960'),
                    telegramChatId: 2222,
                    telegramMessageId: 1111,
                    text: 'Ipsum',
                    active: true,
                    addedAt: (new DateTimeImmutable('now'))->setTimestamp(time())
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('88a600ff-441e-4d34-b2c6-681c8061e48a'),
                    telegramChatId: 2222,
                    telegramMessageId: 1111,
                    text: 'Dolor',
                    active: false,
                    addedAt: (new DateTimeImmutable('now'))->setTimestamp(time())
                ),
            ],
            'expectedString' => 'chat_id=2222&text=Successfully+added%3A+%2ALorem%2A%2C+%2AIpsum%2A&parse_mode=markdown',
        ];
    }
}
