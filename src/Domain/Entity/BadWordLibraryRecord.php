<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\UniqueConstraint;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

#[Entity]
#[UniqueConstraint(fields: ['telegramChatId', 'text'])]
class BadWordLibraryRecord
{
    public function __construct(
        #[Id, Column(type: 'uuid')]
        public Uuid $id,

        #[Column(type: 'text', nullable: true)]
        public ?int $telegramChatId,

        #[Column(type: 'text', nullable: true)]
        public ?int $telegramMessageId,

        #[Column(type: 'text')]
        public string $text,

        #[Column(type: 'boolean')]
        public bool $active,

        #[Column(type: 'datetime_immutable', nullable: true)]
        public ?DateTimeImmutable $updatedAt,
    ) {
    }
}
