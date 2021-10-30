<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\NewMessage;

use DateTimeImmutable;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class NewMessageCommand
{
    public function __construct(
        public string $text,
        public int $userId,
        public string $userName,
        public int $chatId,
        public int $messageId,
        public DateTimeImmutable $sentAt,
    ) {
    }
}
