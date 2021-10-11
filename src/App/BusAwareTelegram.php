<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Telegram;
use Symfony\Component\Messenger\MessageBusInterface;

class BusAwareTelegram extends Telegram
{
    public function __construct(
        private MessageBusInterface $commandBus,
        string $api_key,
        string $bot_username = '',
    ) {
        parent::__construct($api_key, $bot_username);
    }

    /**
     * Get an object instance of the passed command.
     *
     * @param string $command
     * @param string $filepath
     *
     * @return Command|null
     */
    public function getCommandObject(string $command, string $filepath = ''): ?Command
    {
        if (isset($this->commands_objects[$command])) {
            return $this->commands_objects[$command];
        }

        $which = [Command::AUTH_SYSTEM];
        if ($this->isAdmin()) {
            $which[] = Command::AUTH_ADMIN;
        }
        $which[] = Command::AUTH_USER;

        foreach ($which as $auth) {
            $command_class = $this->getCommandClassName($auth, $command, $filepath);

            if (null !== $command_class) {
                $command_obj = new $command_class(
                    $this,
                    $this->update,
                    $this->commandBus,
                );

                if (Command::AUTH_SYSTEM === $auth && $command_obj instanceof SystemCommand) {
                    return $command_obj;
                }
                if (Command::AUTH_ADMIN === $auth && $command_obj instanceof AdminCommand) {
                    return $command_obj;
                }
                if (Command::AUTH_USER === $auth && $command_obj instanceof UserCommand) {
                    return $command_obj;
                }
            }
        }

        return null;
    }
}
