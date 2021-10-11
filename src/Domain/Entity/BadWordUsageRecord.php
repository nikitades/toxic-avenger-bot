<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\UuidInterface;

#[Entity]
#[UniqueConstraint(columns: ['user_id', 'telegram_chat_id', 'library_word_id'])]
class BadWordUsageRecord
{
    public function __construct(
        #[Id, Column(type: 'uuid', length: 36)]
        public UuidInterface $id,

        #[ManyToOne(targetEntity: User::class)]
        public User $user,

        #[Column(type: 'integer')]
        public int $telegramMessageId,

        #[Column(type: 'integer')]
        public int $telegramChatId,

        #[Column(type: 'uuid', length: 36)]
        public UuidInterface $libraryWordId,

        #[Column(type: 'datetime')]
        public DateTimeInterface $sentAt,
    ) {
    }
}
