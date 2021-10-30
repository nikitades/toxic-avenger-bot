<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use DateTimeInterface;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareSystemCommand;
use Nikitades\ToxicAvenger\Domain\Command\NewMessage\NewMessageCommand;
use DateTimeImmutable;

class GenericMessageCommand extends BusAwareSystemCommand
{
    public function execute(): ServerResponse
    {
        $this->commandDependencies->messageBusInterface->dispatch(
            new NewMessageCommand(
                text: $this->getMessage()->getText() ?? '',
                userId: $this->getMessage()->getFrom()->getId(),
                userName: $this->getMessage()->getFrom()->getUsername(),
                chatId: $this->getMessage()->getChat()->getId(),
                messageId: $this->getMessage()->getMessageId(),
                sentAt: new DateTimeImmutable(date(DateTimeInterface::ATOM, $this->getMessage()->getDate()))
            )
        );

        return Request::emptyResponse();
    }
}
