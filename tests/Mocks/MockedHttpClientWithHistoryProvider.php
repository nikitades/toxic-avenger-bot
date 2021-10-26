<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Mocks;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Longman\TelegramBot\Request as TelegramRequest;
use PHPUnit\Framework\ExpectationFailedException;

class MockedHttpClientWithHistoryProvider
{
    /**
     * @var array<array{request: Request, response: Response, error: string | null, options: array<mixed>}>
     */
    private array $container = [];

    /**
     * @param array<Response> $responses
     */
    public function remember(array $responses): void
    {
        $history = Middleware::history($this->container);
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        TelegramRequest::setClient(new Client(['handler' => $handlerStack]));
    }

    public function getRequestOfNumber(int $number): Request
    {
        if (!isset($this->container[$number])) {
            throw new ExpectationFailedException('Request number ' . $number . ' was not found in the container!');
        }

        return $this->container[$number]['request'];
    }

    /**
     * @return array<array{
     *  request: Request,
     *  response: Response,
     *  error: string | null,
     *  options: array<mixed>
     * }>
     */
    public function getHistory(): array
    {
        return $this->container;
    }
}
