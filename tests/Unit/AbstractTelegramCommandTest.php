<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\BusAwareTelegram;
use Nikitades\ToxicAvenger\App\CommandDependencies;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\CoolQuotesProviderInterface;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\ObsceneWordEscaper;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\UserRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\ToxicityMeasurer;
use Nikitades\ToxicAvenger\Tests\Mocks\MockedHttpClientWithHistoryProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractTelegramCommandTest extends TestCase
{
    protected MockedHttpClientWithHistoryProvider $httpClientContainer;

    /** @var MockObject&BusAwareTelegram */
    protected BusAwareTelegram $telegram;

    protected function setUp(): void
    {
        $this->httpClientContainer = new MockedHttpClientWithHistoryProvider();
        $this->httpClientContainer->remember([new Response(status: 200, headers: [], body: '{}')]);
        $this->telegram = $this->createMock(BusAwareTelegram::class);
        Request::initialize($this->telegram);
    }

    protected function setCommandDependencies(
        ?MessageBusInterface $messageBusInterface = null,
        ?BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository = null,
        ?BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository = null,
        ?UserRepositoryInterface $userRepository = null,
        ?BadWordsLibrary $badWordsLibrary = null,
        ?LemmatizerInterface $lemmatizer = null,
        ?ToxicityMeasurer $toxicityMeasurer = null,
        ?ObsceneWordEscaper $obsceneWordEscaper = null,
        ?CoolQuotesProviderInterface $coolQuotesProvider = null,
    ): void {
        $this->telegram->expects(static::once())->method('getCommandDependencies')->willReturn(
            value: new CommandDependencies(
                messageBusInterface: $messageBusInterface ?? $this->createMock(MessageBusInterface::class),
                badWordLibraryRecordRepository: $badWordLibraryRecordRepository ?? $this->createMock(BadWordLibraryRecordRepositoryInterface::class),
                badWordUsageRecordRepository: $badWordUsageRecordRepository ?? $this->createMock(BadWordUsageRecordRepositoryInterface::class),
                userRepositoryInterface: $userRepository ?? $this->createMock(UserRepositoryInterface::class),
                badWordsLibrary: $badWordsLibrary ?? $this->createMock(BadWordsLibrary::class),
                lemmatizer: $lemmatizer ?? $this->createMock(LemmatizerInterface::class),
                toxicityMeasurer: $toxicityMeasurer ?? $this->createMock(ToxicityMeasurer::class),
                obsceneWordEscaper: $obsceneWordEscaper ?? $this->createMock(ObsceneWordEscaper::class),
                coolQuotesProvider: $coolQuotesProvider ?? $this->createMock(CoolQuotesProviderInterface::class),
            )
        );
    }
}
