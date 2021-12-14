<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\Update;
use Nikitades\ToxicAvenger\App\BusAwareTelegram;
use Nikitades\ToxicAvenger\App\CommandDependencies;

abstract class BusAwareSystemCommand extends SystemCommand
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
