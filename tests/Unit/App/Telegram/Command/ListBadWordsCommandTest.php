<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\App\Telegram\Command;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Longman\TelegramBot\Entities\Update;
use Nikitades\ToxicAvenger\App\CommandDependencies;
use Nikitades\ToxicAvenger\App\Telegram\Command\ListBadWordsCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Tests\Unit\GenericTelegramCommandTest;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class ListBadWordsCommandTest extends GenericTelegramCommandTest
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
        $messageBus->expects(static::never())->method('dispatch');

        $badWordLibraryRecordRepository = $this->createMock(BadWordLibraryRecordRepositoryInterface::class);
        $badWordLibraryRecordRepository->expects(static::once())
            ->method('findActiveFromChat')
            ->with($chatId)
            ->willReturn($badWordLibraryRecords);

        $deps = new CommandDependencies(
            messageBusInterface: $messageBus,
            badWordLibraryRecordRepository: $badWordLibraryRecordRepository,
        );

        (new ListBadWordsCommand(
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
                        ],
                        'date' => $time,
                        'text' => '/listbadwords ' . $text,
                    ],
                ],
                bot_username: 'bot',
            ),
            commandDependencies: $deps,
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
            'expectedString' => 'chat_id=2222&text=No+bad+registered+in+this+chat&parse_mode=markdown',
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
                    updatedAt: (new DateTimeImmutable('now'))->setTimestamp(time()),
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('49fce569-bde6-47e9-9181-122b2281d960'),
                    telegramChatId: 2222,
                    telegramMessageId: 1111,
                    text: 'Ipsum',
                    active: true,
                    updatedAt: (new DateTimeImmutable('now'))->setTimestamp(time()),
                ),
            ],
            'expectedString' => 'chat_id=2222&text=Bad+words%3A+%0A%2ALorem%2A%0A%2AIpsum%2A&parse_mode=markdown',
        ];
    }
}
