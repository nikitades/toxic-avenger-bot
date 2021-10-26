<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\AddBadWordToLibrary;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\UuidProvider;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddBadWordToLibraryCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private LemmatizerInterface $lemmatizer,
        private UuidProvider $uuidProvider,
        private BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository,
    ) {
    }

    public function __invoke(AddBadWordToLibraryCommand $command): void
    {
        $lemmas = $this->lemmatizer->lemmatizePhraseWithOnlyMeaningful($command->text);

        $existingBadWords = $this->badWordLibraryRecordRepository->findManyWithinChat(
            chatId: $command->telegramChatId,
            possibleBadWords: $lemmas
        );

        $newLemmas = array_diff(
            $lemmas,
            array_map(
                fn (BadWordLibraryRecord $badWordLibraryRecord): string => $badWordLibraryRecord->text,
                $existingBadWords
            )
        );

        $newBadWordLibraryRecords = array_map(
            fn (string $text): BadWordLibraryRecord => new BadWordLibraryRecord(
                id: $this->uuidProvider->provide(),
                telegramChatId: $command->telegramChatId,
                telegramMessageId: $command->telegramMessageId,
                text: $text,
                active: true,
                addedAt: $command->addedAt
            ),
            $newLemmas
        );

        $this->badWordLibraryRecordRepository->save($newBadWordLibraryRecords);
    }
}
