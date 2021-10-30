<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\App\Telegram\Command;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Longman\TelegramBot\Entities\Update;
use Nikitades\ToxicAvenger\App\Telegram\Command\GenericMessageCommand;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\Command\NewMessage\NewMessageCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordFrequencyRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\ObsceneWordEscaper;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\ToxicityMeasure;
use Nikitades\ToxicAvenger\Domain\ToxicityMeasurer;
use Nikitades\ToxicAvenger\Tests\Unit\AbstractTelegramCommandTest;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use stdClass;

class GenericMessageCommandTest extends AbstractTelegramCommandTest
{
    /**
     * @dataProvider provideTestData
     * @param array<BadWordLibraryRecord> $badWordLibraryRecords
     * @param array<string> $lemmatizationResult
     * @param array<BadWordFrequencyRecord> $frequencyData
     * @param array<ToxicityMeasure> $toxicityMeasurement
     */
    public function testExecute(
        int $messageId,
        int $chatId,
        int $userId,
        string $text,
        int $time,
        array $badWordLibraryRecords,
        array $lemmatizationResult,
        array $frequencyData,
        array $toxicityMeasurement,
        string $expectedString,
    ): void {
        $this->httpClientContainer->remember([new Response(status: 200, headers: [], body: '{}')]);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(static::once())
            ->method('dispatch')
            ->with(
                new NewMessageCommand(
                    text: $text,
                    userId: $userId,
                    userName: 'nikitades',
                    chatId: $chatId,
                    messageId: $messageId,
                    sentAt: (new DateTimeImmutable('now'))->setTimestamp($time),
                ),
            )
            ->willReturn(new Envelope(new stdClass()));

        $lemmatizer = $this->createMock(LemmatizerInterface::class);
        $lemmatizer->expects(static::once())
            ->method('lemmatizePhraseWithOnlyMeaningful')
            ->with($text)
            ->willReturn($lemmatizationResult);

        $badWordsLibrary = $this->createMock(BadWordsLibrary::class);
        $badWordsLibrary->expects(static::once())
            ->method('getForChat')
            ->with($chatId, $lemmatizationResult)
            ->willReturn($badWordLibraryRecords);

        $badWordUsageRecordRepository = $this->createMock(BadWordUsageRecordRepositoryInterface::class);
        $badWordUsageRecordRepository->expects(static::once())
            ->method('getBadWordsUsageFrequencyForList')
            ->with($userId, $chatId, $badWordLibraryRecords)
            ->willReturn($frequencyData);

        $toxicityMeasurer = $this->createMock(ToxicityMeasurer::class);
        $toxicityMeasurer->expects(static::exactly(count($toxicityMeasurement)))
            ->method('measureToxicityLevel')
            ->withConsecutive(...array_map(fn (BadWordFrequencyRecord $bwfr): array => [$bwfr->usagesCount], $frequencyData))
            ->willReturnOnConsecutiveCalls(...$toxicityMeasurement);

        $obsceneWordEscaper = $this->createMock(ObsceneWordEscaper::class);
        $obsceneWordEscaper->expects(static::exactly(count($toxicityMeasurement)))
            ->method('escape')
            ->withConsecutive(...array_map(fn (BadWordFrequencyRecord $bwfr): array => [$bwfr->word], $frequencyData))
            ->willReturnOnConsecutiveCalls(...array_map(fn (BadWordFrequencyRecord $bwfr): string => $bwfr->word, $frequencyData));

        $deps = $this->getDependencies(
            messageBusInterface: $messageBus,
            badWordsLibrary: $badWordsLibrary,
            badWordUsageRecordRepository: $badWordUsageRecordRepository,
            lemmatizer: $lemmatizer,
            toxicityMeasurer: $toxicityMeasurer,
            obsceneWordEscaper: $obsceneWordEscaper,
        );

        (new GenericMessageCommand(
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
            commandDependencies: $deps,
        ))->execute();

        if ([] === $badWordLibraryRecords) {
            static::assertCount(0, $this->httpClientContainer->getHistory());

            return;
        }

        static::assertCount(1, $this->httpClientContainer->getHistory());
        $request = $this->httpClientContainer->getRequestOfNumber(0);
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(
            $expectedString,
            $request->getBody()->getContents(),
        );
    }

    /**
     * @return iterable<string,array<string,int|array<BadWordLibraryRecord|string|BadWordFrequencyRecord|ToxicityMeasure>|string>>
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
            'lemmatizationResult' => [],
            'frequencyData' => [],
            'toxicityMeasurement' => [],
            'expectedString' => 'chat_id=2222&text=No+new+words+registered&parse_mode=markdown',
        ];

        yield 'bad word library records are found' => [
            'messageId' => 1111,
            'chatId' => 2222,
            'userId' => 3333,
            'text' => 'lala Lorem Ipsum lele',
            'time' => time(),
            'badWordLibraryRecords' => [
                new BadWordLibraryRecord(
                    id: Uuid::fromString('6aad5c3d-d7c2-4268-9144-b3f51c52d457'),
                    telegramChatId: 2222,
                    telegramMessageId: null,
                    text: 'lorem',
                    active: true,
                    updatedAt: null,
                ),
                new BadWordLibraryRecord(
                    id: Uuid::fromString('e71be6bc-abb3-469d-ae4e-f38c8c2c5e52'),
                    telegramChatId: 2222,
                    telegramMessageId: null,
                    text: 'ipsum',
                    active: true,
                    updatedAt: null,
                ),
            ],
            'lemmatizationResult' => ['lorem', 'ipsum'],
            'frequencyData' => [
                new BadWordFrequencyRecord(
                    word: 'lorem',
                    usagesCount: 2,
                ),
                new BadWordFrequencyRecord(
                    word: 'ipsum',
                    usagesCount: 5,
                ),
            ],
            'toxicityMeasurement' => [
                new ToxicityMeasure(
                    usagesCount: 2,
                    title: 'title1',
                ),
                new ToxicityMeasure(
                    usagesCount: 5,
                    title: 'title2',
                ),
            ],
            'expectedString' => 'chat_id=2222&text=User+%2A%40nikitades%2A+is%3A+%0Atitle1+for+%2A2%2A+usages+of+%2Alorem%2A%2C%0Atitle2+for+%2A5%2A+usages+of+%2Aipsum%2A&parse_mode=markdown',
        ];
    }
}
