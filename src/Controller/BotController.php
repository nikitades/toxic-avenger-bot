<?php

namespace App\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use Longman\TelegramBot\Telegram;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Longman\TelegramBot\Exception\TelegramException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @Route("/bot")
 */
class BotController extends AbstractController
{

    private string $botApiKey;
    private string $botUserName;

    public function __construct(ParameterBagInterface $params)
    {
        $this->botApiKey = $params->get("bot.api_key");
        $this->botUserName = $params->get("bot.user_name");
    }

    /**
     * @Route("/handler", name="handler")
     */
    public function handler(LoggerInterface $logger, Request $request): Response
    {
        try {
            $telegram = new Telegram($this->botApiKey, $this->botUserName);
            $telegram->addCommandsPath(__DIR__ . "/../BotCommand");
            $telegram->handle();
        } catch (TelegramException $e) {
            $logger->error($e->getMessage());
        }
        return $this->json("ok");
    }
}
