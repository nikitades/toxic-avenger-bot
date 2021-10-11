<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\UuidInterface;

#[
    Entity,
    UniqueConstraint(fields: ['telegramId'])
]
class User
{
    /**
     * @param Collection<int,BadWordUsageRecord> $badWords
     */
    public function __construct(
        #[Id, Column(type: 'text', length: 36)]
        public UuidInterface $id,

        #[Column(type: 'integer')]
        public string $telegramId,

        #[Column(type: 'text')]
        public string $name,

        #[Column(type: 'datetime')]
        public DateTimeInterface $addedAt,

        #[Column(type: 'integer')]
        public int $badWordsUsed,

        #[OneToMany(targetEntity: BadWordUsageRecord::class, mappedBy: 'userId')]
        public Collection $badWords,
    ) {
    }
}
