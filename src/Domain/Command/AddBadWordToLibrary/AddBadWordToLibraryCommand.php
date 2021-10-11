<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\AddBadWordToLibrary;

use DateTimeInterface;

class AddBadWordToLibraryCommand
{
    public function __construct(
        public string $word,
        public int $telegramChatId,
        public int $telegramUserId,
        public DateTimeInterface $addedAt,
    ) {
    }
}
