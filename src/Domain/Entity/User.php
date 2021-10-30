<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

#[
    Entity,
    Table(name: 'bot_user'),
    UniqueConstraint(fields: ['telegramId'])
]
class User
{
    /**
     * @param Collection<int,BadWordUsageRecord> $badWords
     */
    public function __construct(
        #[Id, Column(type: 'uuid')]
        public Uuid $id,

        #[Column(type: 'integer')]
        public int $telegramId,

        #[Column(type: 'text')]
        public string $name,

        #[Column(type: 'datetime_immutable')]
        public DateTimeImmutable $addedAt,

        #[OneToMany(targetEntity: BadWordUsageRecord::class, mappedBy: 'user')]
        public Collection $badWords,
    ) {
    }
}
