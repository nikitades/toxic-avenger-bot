<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Repository;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;

interface BadWordLibraryRecordRepositoryInterface
{
    /**
     * @param array<string> $possibleBadWords
     * @return array<BadWordLibraryRecord>
     */
    public function findManyWithinChat(
        int $chatId,
        array $possibleBadWords
    ): array;

    /**
     * @return array<BadWordLibraryRecord>
     */
    public function findAddedByMessageId(
        int $messageId,
    ): array;

    /**
     * @param array<BadWordLibraryRecord> $badWordLibraryRecords
     */
    public function save(
        array $badWordLibraryRecords
    ): void;
}
