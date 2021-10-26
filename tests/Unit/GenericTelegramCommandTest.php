<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Nikitades\ToxicAvenger\Tests\Mocks\MockedHttpClientWithHistoryProvider;
use PHPUnit\Framework\TestCase;

abstract class GenericTelegramCommandTest extends TestCase
{
    protected MockedHttpClientWithHistoryProvider $httpClientContainer;
    protected Telegram $telegram;

    protected function setUp(): void
    {
        $this->httpClientContainer = new MockedHttpClientWithHistoryProvider();
        $this->telegram = $this->createMock(Telegram::class);
        Request::initialize($this->telegram);
    }
}
