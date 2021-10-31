<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use DateTimeInterface;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;
use Nikitades\ToxicAvenger\Domain\Command\DisableBadWordsInLibrary\DisableBadWordsInLibraryCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use DateTimeImmutable;

class RemoveBadWordCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        $this->commandDependencies->messageBusInterface->dispatch(
            new DisableBadWordsInLibraryCommand(
                text: $this->getMessage()->getText(true) ?? '',
                telegramChatId: $this->getMessage()->getChat()->getId(),
                telegramMessageId: $this->getMessage()->getMessageId(),
                telegramUserId: $this->getMessage()->getFrom()->getId(),
                updatedAt: new DateTimeImmutable(date(DateTimeInterface::ATOM, $this->getMessage()->getDate())),
            )
        );

        $libraryWordsDisabledFromThisMessage = $this->commandDependencies->badWordLibraryRecordRepository->findDisabledByMessageId(
            messageId: $this->getMessage()->getMessageId(),
        );

        $libraryWordsDisabledFromThisMessage = array_map(
            fn (BadWordLibraryRecord $bwlr): string => '*' . $bwlr->text . '*',
            $libraryWordsDisabledFromThisMessage
        );

        if ([] === $libraryWordsDisabledFromThisMessage) {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'No words to remove found',
                'parse_mode' => 'markdown',
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => 'Removed: ' . implode(', ', $libraryWordsDisabledFromThisMessage),
            'parse_mode' => 'markdown',
        ]);
    }
}
