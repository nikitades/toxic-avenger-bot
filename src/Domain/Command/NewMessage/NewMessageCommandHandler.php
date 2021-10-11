<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\NewMessage;

use Nikitades\ToxicAvenger\Domain\UuidProvider;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NewMessageCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private LemmatizerInterface $lemmatizer,
        private UserRepositoryInterface $userRepository,
        private BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository,
        private BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository,
        private UuidProvider $uuidProvider,
    ) {
    }

    public function __invoke(NewMessageCommand $command): void
    {
        $user = $this->userRepository->findByTelegramId($command->userId);

        if (null === $user) {
            return;
        }

        $lemmas = $this->lemmatizer->lemmatizePhraseWithOnlyMeaningful($command->text);

        $badWordsFromLibrary = $this->badWordLibraryRecordRepository->findManyWithinChat(
            chatId: $command->chatId,
            possibleBadWords: $lemmas
        );

        $badWordUsages = array_map(
            fn (BadWordLibraryRecord $badWordLibraryRecord): BadWordUsageRecord => new BadWordUsageRecord(
                id: $this->uuidProvider->provide(),
                user: $user,
                telegramMessageId: $command->messageId,
                telegramChatId: $command->chatId,
                libraryWordId: $badWordLibraryRecord->id,
                sentAt: $command->sentAt
            ),
            $badWordsFromLibrary
        );

        $this->badWordUsageRecordRepository->addBadWordUsages($badWordUsages);
    }
}
