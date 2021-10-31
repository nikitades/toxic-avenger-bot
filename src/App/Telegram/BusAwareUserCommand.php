<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Nikitades\ToxicAvenger\App\CommandDependencies;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class BusAwareUserCommand extends UserCommand
{
    public function __construct(
        Telegram $telegram,
        ?Update $update = null,
        protected CommandDependencies $commandDependencies,
    ) {
        $this->telegram = $telegram;
        if ($update !== null) {
            $this->setUpdate($update);
        }
        $this->config = $telegram->getCommandConfig($this->name);
    }
}
