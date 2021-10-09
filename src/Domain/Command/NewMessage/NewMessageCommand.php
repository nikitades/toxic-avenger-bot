<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\NewMessage;

use DateTimeInterface;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class NewMessageCommand
{
    public function __construct(
        public string $text,
        public string $userId,
        public string $chatId,
        public DateTimeInterface $sentAt
    ) {
    }
}
