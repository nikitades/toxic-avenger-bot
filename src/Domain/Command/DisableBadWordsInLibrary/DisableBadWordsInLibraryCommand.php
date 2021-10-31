<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\DisableBadWordsInLibrary;

use DateTimeImmutable;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
class DisableBadWordsInLibraryCommand
{
    public function __construct(
        public string $text,
        public int $telegramChatId,
        public int $telegramMessageId,
        public int $telegramUserId,
        public DateTimeImmutable $updatedAt,
    ) {
    }
}
