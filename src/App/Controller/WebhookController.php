<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Controller;

use Longman\TelegramBot\Exception\TelegramException;
use Nikitades\ToxicAvenger\App\BusAwareTelegram;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController
{
    public function __construct(
        private BusAwareTelegram $telegram,
    ) {
    }

    #[Route(path: '/api/webhook', methods: ['POST'])]
    public function __invoke(): Response
    {
        $this->telegram->handle();

        $response = $this->telegram->getLastCommandResponse();

        if ($response->isOk()) {
            return new Response('ok', Response::HTTP_OK);
        } else {
            throw new TelegramException($response->getDescription(), $response->getErrorCode(), null);
        }
    }
}
