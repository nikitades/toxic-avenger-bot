<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
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
    public function __construct(
        #[Id, Column(type: 'uuid')]
        public Uuid $id,

        #[Column(type: 'text')]
        public int $telegramId,

        #[Column(type: 'text')]
        public string $name,

        #[Column(type: 'datetime_immutable')]
        public DateTimeImmutable $addedAt,
    ) {
    }
}
