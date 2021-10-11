<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class BusAwareUserCommand extends UserCommand
{
    public function __construct(
        Telegram $telegram,
        ?Update $update = null,
        protected MessageBusInterface $messageBusInterface,
    ) {
        $this->telegram = $telegram;
        if ($update !== null) {
            $this->setUpdate($update);
        }
        $this->config = $telegram->getCommandConfig($this->name);
    }
}
