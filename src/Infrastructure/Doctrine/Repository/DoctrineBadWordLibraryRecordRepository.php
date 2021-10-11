<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;

/**
 * @extends ServiceEntityRepository<BadWordLibraryRecord>
 */
class DoctrineBadWordLibraryRecordRepository extends ServiceEntityRepository implements BadWordLibraryRecordRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BadWordLibraryRecord::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findManyWithinChat(int $chatId, array $possibleBadWords): array
    {
        return $this->createQueryBuilder('bwl')
            ->where('bwl.telegramChatId = :tgChatId')->setParameter('tgChatId', $chatId)
            ->andWhere('bwl.text in (:words)')->setParameter('words', $possibleBadWords)
            ->getQuery()
            ->getResult();
    }
}
