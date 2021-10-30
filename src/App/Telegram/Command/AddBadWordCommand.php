<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use DateTimeInterface;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;
use Nikitades\ToxicAvenger\Domain\Command\AddBadWordsToLibrary\AddBadWordsToLibraryCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use DateTimeImmutable;

class AddBadWordCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        $this->commandDependencies->messageBusInterface->dispatch(
            new AddBadWordsToLibraryCommand(
                text: $this->getMessage()->getText(true) ?? '',
                telegramChatId: $this->getMessage()->getChat()->getId(),
                telegramMessageId: $this->getMessage()->getMessageId(),
                telegramUserId: $this->getMessage()->getFrom()->getId(),
                updatedAt: new DateTimeImmutable(date(DateTimeInterface::ATOM, $this->getMessage()->getDate())),
            )
        );

        $libraryWordsAddedFromThisMessage = $this->commandDependencies->badWordLibraryRecordRepository->findAddedByMessageId(
            messageId: $this->getMessage()->getMessageId(),
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
            'text' => 'Added: ' . implode(', ', $libraryWordsAddedFromThisMessage),
            'parse_mode' => 'markdown',
        ]);
    }
}
