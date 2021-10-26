<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use Safe\DateTimeImmutable;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;
use Nikitades\ToxicAvenger\Domain\Command\AddBadWordToLibrary\AddBadWordToLibraryCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;

class AddBadWordCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        $this->commandDependencies->messageBusInterface->dispatch(
            new AddBadWordToLibraryCommand(
                text: $this->getMessage()->getText(true) ?? '',
                telegramChatId: $this->getMessage()->getChat()->getId(),
                telegramMessageId: $this->getMessage()->getMessageId(),
                telegramUserId: $this->getMessage()->getFrom()->getId(),
                addedAt: (new DateTimeImmutable('now'))->setTimestamp($this->getMessage()->getDate()),
            )
        );

        $libraryWordsAddedFromThisMessage = $this->commandDependencies->badWordLibraryRecordRepository->findAddedByMessageId(
            messageId: $this->getMessage()->getMessageId(),
        );

        $libraryWordsAddedFromThisMessage = array_filter(
            $libraryWordsAddedFromThisMessage,
            fn (BadWordLibraryRecord $bwlr): bool => $bwlr->active
        );

        $libraryWordsAddedFromThisMessage = array_map(
            fn (BadWordLibraryRecord $libraryBadWordRecord): string => '*' . $libraryBadWordRecord->text . '*',
            $libraryWordsAddedFromThisMessage
        );

        if ([] === $libraryWordsAddedFromThisMessage) {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'No new words registered',
                'parse_mode' => 'markdown',
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => 'Successfully added: ' . implode(', ', $libraryWordsAddedFromThisMessage),
            'parse_mode' => 'markdown',
        ]);
    }
}
