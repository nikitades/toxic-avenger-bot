<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;


/**
 * @extends ServiceEntityRepository<BadWordUsageRecord>
 */
class DoctrineBadWordUsageRecordRepository extends ServiceEntityRepository implements BadWordUsageRecordRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BadWordUsageRecord::class);
    }

    /**
     * {@inheritDoc}
     */
    public function addBadWordUsages(array $badWordUsages): void
    {
        array_walk(
            $badWordUsages,
            fn (BadWordUsageRecord $bwur) => $this->getEntityManager()->persist($bwur)
        );

        $this->getEntityManager()->flush();
    }
}
