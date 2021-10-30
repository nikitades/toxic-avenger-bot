<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Repository;

use DateTimeImmutable;
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
    public function findActiveFromChat(int $chatId): array
    {
        return $this->createQueryBuilder('bwl')
            ->where('bwl.telegramChatId = :tgChatId')->setParameter('tgChatId', $chatId)
            ->andWhere('bwl.active = :true')->setParameter('true', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function enableWords(
        array $lemmasToEnable,
        int $telegramChatId,
        int $telegramMessageId,
        DateTimeImmutable $updatedAt,
    ): void {
        $this->createQueryBuilder('bwl')
            ->update()
            ->set('bwl.active', ':true')->setParameter('true', true)
            ->set('bwl.telegramMessageId', ':tgMsgId')->setParameter('tgMsgId', $telegramMessageId)
            ->set('bwl.updatedAt', ':updatedAt')->setParameter('updatedAt', $updatedAt)
            ->where('bwl.telegramChatId = :telegramChatId')->setParameter('telegramChatId', $telegramChatId)
            ->andWhere('bwl.text IN(:lemmasToEnable)')->setParameter('lemmasToEnable', $lemmasToEnable)
            ->getQuery()
            ->execute();
    }

    public function disableWords(
        array $lemmasToDisable,
        int $telegramChatId,
        int $telegramMessageId,
        DateTimeImmutable $updatedAt,
    ): void {
        $this->createQueryBuilder('bwl')
            ->update()
            ->set('bwl.active', ':false')->setParameter('false', false)
            ->set('bwl.telegramMessageId', ':tgMsgId')->setParameter('tgMsgId', $telegramMessageId)
            ->set('bwl.updatedAt', ':updatedAt')->setParameter('updatedAt', $updatedAt)
            ->where('bwl.telegramChatId = :telegramChatId')->setParameter('telegramChatId', $telegramChatId)
            ->andWhere('bwl.text IN(:lemmasToEnable)')->setParameter('lemmasToEnable', $lemmasToDisable)
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
            ->andWhere('bwl.active = true')
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findDisabledByMessageId(int $messageId): array
    {
        return $this->createQueryBuilder('bwl')
            ->where('bwl.telegramMessageId = :tgMsgId')->setParameter('tgMsgId', $messageId)
            ->andWhere('bwl.active = false')
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findManyInChatFromList(int $chatId, array $possibleBadWordLemmas): array
    {
        return $this->createQueryBuilder('bwl')
            ->where('bwl.telegramChatId = :tgChatId')->setParameter('tgChatId', $chatId)
            ->andWhere('bwl.text in (:words)')->setParameter('words', $possibleBadWordLemmas)
            ->getQuery()
            ->getResult();
    }
}
