<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;

class KickToxicCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        $quote = $this->commandDependencies->coolQuotesProvider->provide();

        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => '`' . $quote->quote . "`\n\n  **" . $quote->author . "** \n  " . implode(' ', array_map(fn (string $tag): string => "#$tag", $quote->tags)),
            'parse_mode' => 'markdown',
        ]);
    }
}
