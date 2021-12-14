<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram;

use Nikitades\ToxicAvenger\App\CommandDependencies;
use Nikitades\ToxicAvenger\App\BusAwareTelegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;

abstract class BusAwareUserCommand extends UserCommand
{
    protected CommandDependencies $commandDependencies;

    public function __construct(
        BusAwareTelegram $telegram,
        ?Update $update = null,
    ) {
        $this->telegram = $telegram;
        $this->commandDependencies = $telegram->getCommandDependencies();
        if ($update !== null) {
            $this->setUpdate($update);
        }
        $this->config = $telegram->getCommandConfig($this->name);
    }
}
