<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Nikitades\ToxicAvenger\App\CommandDependencies;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\ObsceneWordEscaper;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\ToxicityMeasurer;
use Nikitades\ToxicAvenger\Tests\Mocks\MockedHttpClientWithHistoryProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractTelegramCommandTest extends TestCase
{
    protected MockedHttpClientWithHistoryProvider $httpClientContainer;
    protected Telegram $telegram;

    protected function setUp(): void
    {
        $this->httpClientContainer = new MockedHttpClientWithHistoryProvider();
        $this->telegram = $this->createMock(Telegram::class);
        Request::initialize($this->telegram);
    }

    protected function getDependencies(
        ?MessageBusInterface $messageBusInterface = null,
        ?BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository = null,
        ?BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository = null,
        ?BadWordsLibrary $badWordsLibrary = null,
        ?LemmatizerInterface $lemmatizer = null,
        ?ToxicityMeasurer $toxicityMeasurer = null,
        ?ObsceneWordEscaper $obsceneWordEscaper = null,
    ): CommandDependencies {
        return new CommandDependencies(
            messageBusInterface: $messageBusInterface ?? $this->createMock(MessageBusInterface::class),
            badWordLibraryRecordRepository: $badWordLibraryRecordRepository ?? $this->createMock(BadWordLibraryRecordRepositoryInterface::class),
            badWordUsageRecordRepository: $badWordUsageRecordRepository ?? $this->createMock(BadWordUsageRecordRepositoryInterface::class),
            badWordsLibrary: $badWordsLibrary ?? $this->createMock(BadWordsLibrary::class),
            lemmatizer: $lemmatizer ?? $this->createMock(LemmatizerInterface::class),
            toxicityMeasurer: $toxicityMeasurer ?? $this->createMock(ToxicityMeasurer::class),
            obsceneWordEscaper: $obsceneWordEscaper ?? $this->createMock(ObsceneWordEscaper::class),
        );
    }
}
