<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;

class ListBadWordsCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        $existingEnabledBadWords = $this->commandDependencies->badWordLibraryRecordRepository->findActiveFromChat(
            chatId: $this->getMessage()->getChat()->getId(),
        );

        if ([] === $existingEnabledBadWords) {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'No bad registered in this chat',
                'parse_mode' => 'markdown',
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => "Bad words: \n" . implode(
                "\n",
                array_map(
                    fn (BadWordLibraryRecord $bwlr): string => "*$bwlr->text*",
                    $existingEnabledBadWords,
                ),
            ),
            'parse_mode' => 'markdown',
        ]);
    }
}
