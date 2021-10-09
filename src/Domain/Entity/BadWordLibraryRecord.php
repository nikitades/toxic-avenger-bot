<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\Uuid;

#[Entity]
#[UniqueConstraint(fields: ['telegramChatId', 'text'])]
class BadWordLibraryRecord
{
    public function __construct(
        #[Id, Column(type: 'text', length: 36)]
        public Uuid $id,

        #[Column(type: 'integer')]
        public int $telegramChatId,

        #[Column(type: 'text')]
        public string $text,

        #[Column(type: 'boolean')]
        public bool $active,

        #[Column(type: 'datetime')]
        public DateTimeInterface $addedAt,
    ) {
    }
}
