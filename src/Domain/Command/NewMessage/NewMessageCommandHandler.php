<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\NewMessage;

use Doctrine\Common\Collections\ArrayCollection;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;
use Nikitades\ToxicAvenger\Domain\Entity\User;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\UserRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\UuidProvider;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Uid\Uuid;

class NewMessageCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private LemmatizerInterface $lemmatizer,
        private UserRepositoryInterface $userRepository,
        private BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository,
        private BadWordsLibrary $badWordsLibrary,
        private UuidProvider $uuidProvider,
    ) {
    }

    public function __invoke(NewMessageCommand $command): void
    {
        $user = $this->userRepository->findByTelegramId($command->userId);

        if (null === $user) {
            $user = new User(
                id: $this->uuidProvider->provide(),
                telegramId: $command->userId,
                name: $command->userName,
                addedAt: $command->sentAt,
                badWords: new ArrayCollection([])
            );
        }

        if ($command->userName !== $user->name) {
            $user->name = $command->userName;
        }

        $this->userRepository->save($user);

        $lemmas = $this->lemmatizer->lemmatizePhraseWithOnlyMeaningful($command->text);

        $badWordsFromLibrary = $this->badWordsLibrary->getForChat(
            telegramChatId: $command->chatId,
            lemmas: $lemmas,
        );

        $badWordUsages = array_map(
            fn (BadWordLibraryRecord $badWordLibraryRecord): BadWordUsageRecord => new BadWordUsageRecord(
                id: $this->uuidProvider->provide(),
                user: $user,
                telegramMessageId: $command->messageId,
                telegramChatId: $command->chatId,
                libraryWordId: $badWordLibraryRecord->id ?? Uuid::v4(),
                sentAt: $command->sentAt
            ),
            $badWordsFromLibrary
        );

        $this->badWordUsageRecordRepository->addBadWordUsages($badWordUsages);
    }
}
