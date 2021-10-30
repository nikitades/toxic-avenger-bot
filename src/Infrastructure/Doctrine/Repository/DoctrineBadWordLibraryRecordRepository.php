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
    public function findManyById(array $ids): array
    {
        return $this->createQueryBuilder('bwlr')
            ->where('bwlr.id IN (:ids)')->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function ensureLiraryItemsExist(array $badWordLibraryRecords): void
    {
        $this->getEntityManager()->createQuery('INSERT INTO ' . BadWordLibraryRecord::class . ' bwlr
            (id, telegramChatId, text, active)
            VALUES
            ' . implode(
                ', ', array_map(
                    fn (BadWordLibraryRecord $bwlr): string => "(:id_$bwlr->id, :telegramChatId_$bwlr->id, :text_$bwlr->id, :active_$bwlr->id)",
                    $badWordLibraryRecords,
                ),
            ) . '
            ON CONFLICT (id) DO NOTHING
        ')
        ->setParameters(
            array_merge(
                ...array_map(
                    fn (BadWordLibraryRecord $bwlr): array => [
                        'id_' . $bwlr->id => $bwlr->id,
                        'telegramChatId_' . $bwlr->id => $bwlr->telegramChatId,
                        'text_' . $bwlr->id => $bwlr->text,
                        'active_' . $bwlr->id => true,
                    ],
                    $badWordLibraryRecords,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findActiveFromChat(int $chatId): array
    {
        return $this->createQueryBuilder('bwlr')
            ->where('bwlr.telegramChatId = :tgChatId')->setParameter('tgChatId', $chatId)
            ->andWhere('bwlr.active = :true')->setParameter('true', true)
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
        $this->createQueryBuilder('bwlr')
            ->update()
            ->set('bwlr.active', ':true')->setParameter('true', true)
            ->set('bwlr.telegramMessageId', ':tgMsgId')->setParameter('tgMsgId', $telegramMessageId)
            ->set('bwlr.updatedAt', ':updatedAt')->setParameter('updatedAt', $updatedAt)
            ->where('bwlr.telegramChatId = :telegramChatId')->setParameter('telegramChatId', $telegramChatId)
            ->andWhere('bwlr.text IN(:lemmasToEnable)')->setParameter('lemmasToEnable', $lemmasToEnable)
            ->getQuery()
            ->execute();
    }

    public function disableWords(
        array $lemmasToDisable,
        int $telegramChatId,
        int $telegramMessageId,
        DateTimeImmutable $updatedAt,
    ): void {
        $this->createQueryBuilder('bwlr')
            ->update()
            ->set('bwlr.active', ':false')->setParameter('false', false)
            ->set('bwlr.telegramMessageId', ':tgMsgId')->setParameter('tgMsgId', $telegramMessageId)
            ->set('bwlr.updatedAt', ':updatedAt')->setParameter('updatedAt', $updatedAt)
            ->where('bwlr.telegramChatId = :telegramChatId')->setParameter('telegramChatId', $telegramChatId)
            ->andWhere('bwlr.text IN(:lemmasToEnable)')->setParameter('lemmasToEnable', $lemmasToDisable)
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
        return $this->createQueryBuilder('bwlr')
            ->where('bwlr.telegramMessageId = :tgMsgId')->setParameter('tgMsgId', $messageId)
            ->andWhere('bwlr.active = true')
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findDisabledByMessageId(int $messageId): array
    {
        return $this->createQueryBuilder('bwlr')
            ->where('bwlr.telegramMessageId = :tgMsgId')->setParameter('tgMsgId', $messageId)
            ->andWhere('bwlr.active = false')
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findManyInChatFromList(int $chatId, array $possibleBadWordLemmas): array
    {
        return $this->createQueryBuilder('bwlr')
            ->where('bwlr.telegramChatId = :tgChatId')->setParameter('tgChatId', $chatId)
            ->andWhere('bwlr.text in (:words)')->setParameter('words', $possibleBadWordLemmas)
            ->getQuery()
            ->getResult();
    }
}
