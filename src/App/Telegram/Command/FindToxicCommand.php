<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordFrequencyRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Symfony\Component\Uid\Uuid;

class FindToxicCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        $usersWithBiggestBadWordsUsageCount = $this->commandDependencies->badWordUsageRecordRepository->findUsersWithBadWordUsageCount(
            chatId: $this->getMessage()->getChat()->getId(),
            limit: 5,
        );

        if ([] === $usersWithBiggestBadWordsUsageCount) {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => '❤️ No toxic users found! ❤️',
                'parse_mode' => 'markdown',
            ]);
        }

        $badWordsUsedInChat = $this->commandDependencies->badWordsLibrary->findManyById(
            array_values(
                array_map(
                    fn (string $uuidStr): Uuid => Uuid::fromString($uuidStr),
                    array_unique(
                        array_merge(
                            ...array_map(
                                fn (array $row): array => array_column($row['usages'], 'wordId'),
                                $usersWithBiggestBadWordsUsageCount,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $badWordsUsedInChatMap = array_combine(
            array_map(
                fn (BadWordLibraryRecord $bwlr): string => (string) $bwlr->id,
                $badWordsUsedInChat,
            ),
            $badWordsUsedInChat,
        );

        $usersWithBiggestBadWordsUsageCount = array_map(
            fn (array $row): array => [
                'username' => $row['username'],
                'usages' => array_map(
                    fn (array $usage): BadWordFrequencyRecord => new BadWordFrequencyRecord(
                        word: $badWordsUsedInChatMap[$usage['wordId']]->text,
                        usagesCount: $usage['usagesCount'],
                    ),
                    $row['usages'],
                ),
            ],
            $usersWithBiggestBadWordsUsageCount,
        );

        //TODO: cover with tests

        array_walk(
            array: $usersWithBiggestBadWordsUsageCount,
            callback: fn (array &$userWithBadWordUsages) => usort(
                array: $userWithBadWordUsages['usages'],
                callback: fn (BadWordFrequencyRecord $a, BadWordFrequencyRecord $b): int => $b->usagesCount <=> $a->usagesCount,
            )
        );

        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => "Most toxic users:\n" . implode(
                ",\n\n",
                array_map(
                    fn (array $userWithBadWordUsages): string => '@' . $userWithBadWordUsages['username'] . ' with ' . implode(
                        ', ',
                        array_map(
                            fn (BadWordFrequencyRecord $bwfr): string => $this->commandDependencies->obsceneWordEscaper->escape($bwfr->word) . " ($bwfr->usagesCount)",
                            $userWithBadWordUsages['usages'],
                        ),
                    ) . ",\n***total***: " . array_reduce(
                        array: $userWithBadWordUsages['usages'],
                        callback: fn (int $carry, BadWordFrequencyRecord $item): int => $carry + $item->usagesCount,
                        initial: 0
                    ),
                    $usersWithBiggestBadWordsUsageCount,
                ),
            ),
            'parse_mode' => 'markdown',
        ]);
    }
}
