<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
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
    public function findUsersWithBadWordUsageCount(int $chatId, int $limit): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('usages_sum', 'usages_sum');
        $rsm->addScalarResult('agg', 'agg');

        $hardcodedBadWordsIds = $this->badWordsLibrary->getHardcodedItemsIds($chatId);

        $rows = $this->getEntityManager()->createNativeQuery(
            'select u.name, SUM(bwur.usages_sum) usages_sum, json_agg(bwur) agg from bot_user u
            inner join (
                SELECT 
                    bwur.user_id,
                    COUNT(bwur.id) usages_sum,
                    bwur.library_word_id word_id
                FROM bad_word_usage_record bwur
                INNER JOIN bad_word_library_record bwlr ON bwlr.id = bwur.library_word_id AND bwlr.active = true
                WHERE bwur.telegram_chat_id = :tgChatId
                GROUP BY bwur.library_word_id, bwur.user_id
                UNION ALL
                SELECT 
                    bwur.user_id,
                    COUNT(bwur.id) usages_sum,
                    bwur.library_word_id word_id
                FROM bad_word_usage_record bwur
                WHERE bwur.telegram_chat_id = :tgChatId AND bwur.library_word_id IN (:hardcodedWordsIds)
                GROUP BY bwur.library_word_id, bwur.user_id
            ) bwur on bwur.user_id = u.id
            group by u.name
            order by usages_sum DESC
            limit :limit',
            $rsm,
        )
        ->setParameter('tgChatId', $chatId)
        ->setParameter('limit', $limit)
        ->setParameter('hardcodedWordsIds', $hardcodedBadWordsIds)
        ->getResult(Query::HYDRATE_ARRAY);

        return array_map(
            fn (array $row): array => [
                'username' => $row['name'],
                'usages' => array_map(
                    fn (array $aggregate): array => [
                        'wordId' => $aggregate['word_id'],
                        'usagesCount' => $aggregate['usages_sum'],
                    ],
                    json_decode($row['agg'], true),
                ),
            ],
            $rows,
        );
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
