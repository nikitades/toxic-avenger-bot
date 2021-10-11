<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nikitades\ToxicAvenger\Domain\Entity\User;
use Nikitades\ToxicAvenger\Domain\Repository\UserRepositoryInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByTelegramId(int $telegramId): ?User
    {
        $sql = $this->createQueryBuilder('u')
        ->where('u.telegramId = :tgId')->setParameter('tgId', $telegramId)
        ->getQuery()->getSQL();

        return $this->createQueryBuilder('u')
            ->where('u.telegramId = :tgId')->setParameter('tgId', $telegramId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
