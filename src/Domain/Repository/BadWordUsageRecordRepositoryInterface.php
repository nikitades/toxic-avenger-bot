<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Repository;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordFrequencyRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;
use Symfony\Component\Uid\Uuid;

interface BadWordUsageRecordRepositoryInterface
{
    /**
     * @param array<BadWordUsageRecord> $badWordUsages
     */
    public function addBadWordUsages(array $badWordUsages): void;

    /**
     * @param array<BadWordLibraryRecord> $bwlr
     * @return array<BadWordFrequencyRecord>
     */
    public function getBadWordsUsageFrequencyForList(
        int $userId,
        int $chatId,
        array $bwlr,
    ): array;

    /**
     * @return array<Uuid>
     */
    public function findBadWordIdsFromUser(
        int $userId,
        int $chatId,
    ): array;

    /**
     * @return array<array{username: string, usages: array<array{wordId: string, usagesCount: int}>}>
     */
    public function findUsersWithBadWordUsageCount(
        int $chatId,
        int $limit,
    ): array;
}
