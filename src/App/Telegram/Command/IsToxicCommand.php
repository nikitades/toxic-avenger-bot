<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareUserCommand;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordFrequencyRecord;

class IsToxicCommand extends BusAwareUserCommand
{
    public function execute(): ServerResponse
    {
        $username = explode(' ', $this->getMessage()->getText(true) ?? '')[0];
        $username = str_replace('@', '', $username);

        $user = $this->commandDependencies->userRepositoryInterface->findByUsername($username);

        if (null === $user) {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'User @' . $username . ' is not found',
                'parse_mode' => 'markdown',
            ]);
        }

        $usedBadWordIds = $this->commandDependencies->badWordUsageRecordRepository->findBadWordIdsFromUser(
            userId: $user->telegramId,
            chatId: $this->getMessage()->getChat()->getId(),
        );

        if ([] === $usedBadWordIds) {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => '❤️ No, user @' . $this->getMessage()->getFrom()->getUsername() . ' is not toxic! ❤️',
                'parse_mode' => 'markdown',
            ]);
        }

        $usedBadWords = $this->commandDependencies->badWordsLibrary->findManyById($usedBadWordIds);

        $usedBadWordFrequencies = $this->commandDependencies->badWordUsageRecordRepository->getBadWordsUsageFrequencyForList(
            userId: $user->telegramId,
            chatId: $this->getMessage()->getChat()->getId(),
            bwlr: $usedBadWords,
        );

        /** @var BadWordFrequencyRecord $mostFrequentlyUsedBadWord */
        $mostFrequentlyUsedBadWord = array_reduce(
            $usedBadWordFrequencies,
            fn (?BadWordFrequencyRecord $carry, BadWordFrequencyRecord $bwfr): BadWordFrequencyRecord => null === $carry ? $bwfr : ($bwfr->usagesCount > $carry->usagesCount ?? 0 ? $bwfr : $carry),
            null,
        );

        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => '⚠️ Yes, user @' . $user->name . ' is toxic! Especially for *' . $this->commandDependencies->obsceneWordEscaper->escape($mostFrequentlyUsedBadWord->word) . '* (' . $mostFrequentlyUsedBadWord->usagesCount . ') ⚠️',
            'parse_mode' => 'markdown',
        ]);
    }
}
