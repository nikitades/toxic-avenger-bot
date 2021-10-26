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
    public function enableWords(array $lemmasToEnable, int $telegramMessageId): void
    {
        $this->createQueryBuilder('bwl')
            ->update()
            ->set('bwl.active', true)
            ->set('bwl.telegramMessageId', $telegramMessageId)
            ->where('bwl.text IN(:lemmasToEnable)')->set('lemmasToEnable', $lemmasToEnable)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function save(array $badWordLibraryRecords): void
    {
        foreach ($badWordLibraryRecords as $badWordLibraryRecord) {
            $this->getEntityManager()->persist($badWordLibraryRecord);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function findAddedByMessageId(int $messageId): array
    {
        return $this->createQueryBuilder('bwl')
            ->where('bwl.telegramMessageId = :tgMsgId')->setParameter('tgMsgId', $messageId)
            ->getQuery()
            ->getResult();
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
