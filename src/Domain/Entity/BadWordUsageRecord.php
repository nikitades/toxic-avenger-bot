<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\ManyToOne;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

#[Entity]
#[Index(fields: ['user', 'libraryWordId', 'telegramChatId'], name: 'idx_user_library_word_chat')]
class BadWordUsageRecord
{
    public function __construct(
        #[Id, Column(type: 'uuid')]
        public Uuid $id,

        #[ManyToOne(targetEntity: User::class, inversedBy: 'badWords')]
        public User $user,

        #[Column(type: 'text')]
        public int $telegramMessageId,

        #[Column(type: 'text')]
        public int $telegramChatId,

        #[Column(type: 'uuid')]
        public Uuid $libraryWordId,

        #[Column(type: 'datetime_immutable')]
        public DateTimeImmutable $sentAt,
    ) {
    }
}
