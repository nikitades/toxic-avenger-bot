<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\DisableBadWordsInLibrary;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DisableBadWordsInLibraryCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private LemmatizerInterface $lemmatizer,
        private BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository,
    ) {
    }

    public function __invoke(DisableBadWordsInLibraryCommand $command): void
    {
        $lemmas = $this->lemmatizer->lemmatizePhraseWithOnlyMeaningful($command->text);

        $existingBadWords = $this->badWordLibraryRecordRepository->findManyInChatFromList(
            chatId: $command->telegramChatId,
            possibleBadWordLemmas: $lemmas,
        );

        $activeBadWords = array_filter(
            $existingBadWords,
            fn (BadWordLibraryRecord $bwlr): bool => $bwlr->active
        );

        $lemmasToDisable = array_map(
            fn (BadWordLibraryRecord $bwlr): string => $bwlr->text,
            $activeBadWords
        );

        $this->badWordLibraryRecordRepository->disableWords(
            lemmasToDisable: $lemmasToDisable,
            telegramChatId: $command->telegramChatId,
            telegramMessageId: $command->telegramMessageId,
            updatedAt: $command->updatedAt,
        );
    }
}
