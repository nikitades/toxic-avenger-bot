<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;

class HelpCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => 'Add this bot to a chat and then mark every undesired word with /addbadword command (right in the chat!)',
        ]);
    }
}
