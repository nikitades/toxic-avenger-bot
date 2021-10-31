<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\AddBadWordsToLibrary;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\UuidProvider;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddBadWordsToLibraryCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private LemmatizerInterface $lemmatizer,
        private UuidProvider $uuidProvider,
        private BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository,
    ) {
    }

    public function __invoke(AddBadWordsToLibraryCommand $command): void
    {
        $lemmas = $this->lemmatizer->lemmatizePhraseWithOnlyMeaningful($command->text);

        $existingBadWords = $this->badWordLibraryRecordRepository->findManyInChatFromList(
            chatId: $command->telegramChatId,
            possibleBadWordLemmas: $lemmas,
        );

        $activeBadWords = array_filter(
            $existingBadWords,
            fn (BadWordLibraryRecord $bwlr): bool => $bwlr->active,
        );

        $newLemmas = array_diff(
            $lemmas,
            array_map(
                fn (BadWordLibraryRecord $badWordLibraryRecord): string => $badWordLibraryRecord->text,
                $existingBadWords,
            ),
        );

        $inactiveLemmas = array_diff(
            $lemmas,
            array_map(
                fn (BadWordLibraryRecord $badWordLibraryRecord): string => $badWordLibraryRecord->text,
                $activeBadWords,
            ),
        );

        $this->badWordLibraryRecordRepository->enableWords(
            lemmasToEnable: $inactiveLemmas,
            telegramChatId: $command->telegramChatId,
            telegramMessageId: $command->telegramMessageId,
            updatedAt: $command->updatedAt,
        );

        $newBadWordLibraryRecords = array_map(
            fn (string $text): BadWordLibraryRecord => new BadWordLibraryRecord(
                id: $this->uuidProvider->provide(),
                telegramChatId: $command->telegramChatId,
                telegramMessageId: $command->telegramMessageId,
                text: $text,
                active: true,
                updatedAt: $command->updatedAt,
            ),
            $newLemmas,
        );

        $this->badWordLibraryRecordRepository->save($newBadWordLibraryRecords);
    }
}
