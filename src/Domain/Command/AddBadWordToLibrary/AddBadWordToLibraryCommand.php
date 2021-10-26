<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\AddBadWordToLibrary;

use DateTimeInterface;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
class AddBadWordToLibraryCommand
{
    public function __construct(
        public string $text,
        public int $telegramChatId,
        public int $telegramMessageId,
        public int $telegramUserId,
        public DateTimeInterface $addedAt,
    ) {
    }
}
