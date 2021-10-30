<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\NewMessage;

use LogicException;
use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;
use Nikitades\ToxicAvenger\Domain\Entity\User;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\UserRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\UuidProvider;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NewMessageCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private LemmatizerInterface $lemmatizer,
        private UserRepositoryInterface $userRepository,
        private BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository,
        private BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository,
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

        $badWordsFromLibrary = array_filter(
            $badWordsFromLibrary,
            fn (BadWordLibraryRecord $bwlr): bool => $bwlr->active,
        );

        $this->badWordLibraryRecordRepository->ensureLiraryItemsExist($badWordsFromLibrary);

        $badWordUsages = array_map(
            fn (BadWordLibraryRecord $badWordLibraryRecord): BadWordUsageRecord => new BadWordUsageRecord(
                id: $this->uuidProvider->provide(),
                user: $user,
                telegramMessageId: $command->messageId,
                telegramChatId: $command->chatId,
                libraryWordId: $badWordLibraryRecord->id ?? throw new LogicException('Uuid is expected to be present here'),
                sentAt: $command->sentAt,
            ),
            $badWordsFromLibrary,
        );

        $this->badWordUsageRecordRepository->addBadWordUsages($badWordUsages);
    }
}
