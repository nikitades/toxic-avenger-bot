<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Repository;

use DateTimeImmutable;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Symfony\Component\Uid\Uuid;

interface BadWordLibraryRecordRepositoryInterface
{
    /**
     * @param array<Uuid> $ids
     * @return array<BadWordLibraryRecord>
     */
    public function findManyById(array $ids): array;

    /**
     * @return array<BadWordLibraryRecord>
     */
    public function findActiveFromChat(
        int $chatId,
    ): array;

    /**
     * @param array<string> $possibleBadWordLemmas
     * @return array<BadWordLibraryRecord>
     */
    public function findManyInChatFromList(
        int $chatId,
        array $possibleBadWordLemmas,
    ): array;

    /**
     * @return array<BadWordLibraryRecord>
     */
    public function findAddedByMessageId(
        int $messageId,
    ): array;

    /**
     * @return array<BadWordLibraryRecord>
     */
    public function findDisabledByMessageId(
        int $messageId,
    ): array;

    /**
     * @param array<BadWordLibraryRecord> $badWordLibraryRecords
     */
    public function save(
        array $badWordLibraryRecords,
    ): void;

    /**
     * @param array<string> $lemmasToEnable
     */
    public function enableWords(
        array $lemmasToEnable,
        int $telegramChatId,
        int $telegramMessageId,
        DateTimeImmutable $updatedAt,
    ): void;

    /**
     * @param array<string> $lemmasToDisable
     */
    public function disableWords(
        array $lemmasToDisable,
        int $telegramChatId,
        int $telegramMessageId,
        DateTimeImmutable $updatedAt,
    ): void;

    /**
     * @param array<BadWordLibraryRecord> $badWordLibraryRecords
     */
    public function ensureLiraryItemsExist(
        array $badWordLibraryRecords,
    ): void;
}
