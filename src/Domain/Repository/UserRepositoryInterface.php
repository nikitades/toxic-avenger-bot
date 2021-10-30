<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Repository;

use Nikitades\ToxicAvenger\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function findByTelegramId(int $telegramId): ?User;

    public function findByUsername(string $username): ?User;

    public function save(User $user): void;
}
