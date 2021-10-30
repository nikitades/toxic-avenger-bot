<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordFrequencyRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;
use Nikitades\ToxicAvenger\Domain\Entity\User;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<BadWordUsageRecord>
 */
class DoctrineBadWordUsageRecordRepository extends ServiceEntityRepository implements BadWordUsageRecordRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private BadWordsLibrary $badWordsLibrary,
    ) {
        parent::__construct($registry, BadWordUsageRecord::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findBadWordIdsFromUser(int $userId, int $chatId): array
    {
        /** @var array<array{libraryWordId: string}> $rows */
        $rows = $this->getEntityManager()->createQuery('SELECT bwur.libraryWordId libraryWordId
            FROM ' . BadWordUsageRecord::class . ' bwur
            INNER JOIN ' . User::class . ' u WITH bwur.user = u
            WHERE bwur.telegramChatId = :tgChatId
                AND u.telegramId = :tgUserId
            GROUP BY bwur.libraryWordId
        ')
        ->setParameters([
            'tgChatId' => $chatId,
            'tgUserId' => $userId,
        ])
        ->getResult(Query::HYDRATE_ARRAY);

        return array_column($rows, 'libraryWordId');
    }

    /**
     * {@inheritDoc}
     */
    public function getBadWordsUsageFrequencyForList(int $userId, int $chatId, array $bwlr): array
    {
        /** @var array<array{libraryWordId: Uuid, count: int}> $rows */
        $rows = $this->getEntityManager()->createQuery('SELECT bwur.libraryWordId libraryWordId, COUNT(bwur.id) as count
            FROM ' . BadWordUsageRecord::class . ' bwur
            INNER JOIN ' . User::class . ' u WITH bwur.user = u
            WHERE bwur.telegramChatId = :tgChatId
                AND u.telegramId = :tgUserId
                AND bwur.libraryWordId IN (:libraryRecordsIds)
            GROUP BY bwur.libraryWordId
        ')
        ->setParameters([
            'tgChatId' => $chatId,
            'tgUserId' => $userId,
            'libraryRecordsIds' => array_map(
                fn (BadWordLibraryRecord $bwlr): Uuid => $bwlr->id,
                $bwlr,
            ),
        ])
        ->getResult(Query::HYDRATE_ARRAY);

        $libraryWordTextMap = array_combine(
            array_map(fn (BadWordLibraryRecord $bwlr): string => (string) $bwlr->id, $bwlr),
            array_map(fn (BadWordLibraryRecord $bwlr): string => $bwlr->text, $bwlr),
        );

        return array_map(
            fn (array $rawData): BadWordFrequencyRecord => new BadWordFrequencyRecord(
                word: $libraryWordTextMap[(string) $rawData['libraryWordId']] ?? '',
                usagesCount: $rawData['count'],
            ),
            $rows,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function addBadWordUsages(array $badWordUsages): void
    {
        array_walk(
            $badWordUsages,
            fn (BadWordUsageRecord $bwur) => $this->getEntityManager()->persist($bwur),
        );

        $this->getEntityManager()->flush();
    }
}
