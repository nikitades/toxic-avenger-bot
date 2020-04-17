<?php

namespace App\Service;

use App\Repository\RedisRepository;
use App\Exception\NotEnoughMessagesInHistoryException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ToxicityService
{
    private RedisRepository $redisRepo;
    private int $toxicLimit;
    private WordService $wordService;
    private PredefinedBadWordsService $predefinedBadWordsService;

    public function __construct(
        RedisRepository $redisRepository, 
        ParameterBagInterface $params, 
        WordService $wordService,
        PredefinedBadWordsService $predefinedBadWordsService
        )
    {
        $this->redisRepo = $redisRepository;
        $this->toxicLimit = $params->get("toxic.limit");
        $this->wordService = $wordService;
        $this->predefinedBadWordsService = $predefinedBadWordsService;
    }

    /**
     * @param string $messageText
     * @param integer $chatId
     * @param integer $userId
     * @return array<string,int>
     */
    public function checkIfUserIsToxic(string $messageText, int $chatId, int $userId): array
    {
        $thisMessageWords = $this->wordService->getProcessedMessage($messageText);
        $thisWholeMessageNormalized = $this->wordService->normalizeWord($messageText);
        $summaryBadWords = array_unique([...$thisMessageWords, $thisWholeMessageNormalized]);

        $userBadMessages = $this->getUserBadMessages($userId, $chatId);

        $intersectedBadWords = array_intersect($summaryBadWords, array_keys($userBadMessages));
        if (empty($intersectedBadWords)) return [];

        foreach ($intersectedBadWords as $intersectedBadWord) {
            $this->redisRepo->setMaxResultsIfBigger($intersectedBadWord, $userBadMessages[$intersectedBadWord], $chatId, $userId);
            if ($userBadMessages[$intersectedBadWord] >= $this->toxicLimit) { //main line. Toxic limit is the key
                return $userBadMessages;
            }
        }

        return [];
    }

    /**
     * @param integer $userId
     * @return array<string,int>
     */
    public function getUserBadMessages(int $userId, int $chatId): array
    {
        $badWords = $this->redisRepo->getBadWordsForChat($chatId);
        $predefinedBadWords = array_merge(
            $this->predefinedBadWordsService->getRu(),
            $this->predefinedBadWordsService->getEn()
        );
        $badWords = array_merge($badWords, $predefinedBadWords);
        /** @var array<string,array<string,int>> */
        $messages = [];
        try {
            $messages = $this->redisRepo->getLastMessages($chatId);
        } catch (NotEnoughMessagesInHistoryException $e) {
            //
        }
        if (empty($messages)) return [];
        if (!array_key_exists((string) $userId, $messages)) return [];
        $userMessages = $messages[(string) $userId];
        $userBadMessages = array_filter(
            $userMessages,
            fn ($msg) => in_array($msg, $badWords),
            ARRAY_FILTER_USE_KEY
        );
        arsort($userBadMessages);
        return $userBadMessages;
    }

    /**
     * @param integer $chatId
     * @return array<mixed>
     */
    public function getMaxBadWordUsagesForChat(int $chatId): array
    {
        $chatMessages = $this->redisRepo->getLastMessages($chatId);
        $badWords = $this->redisRepo->getBadWordsForChat($chatId);
        $predefinedBadWords = array_merge(
            $this->predefinedBadWordsService->getRu(),
            $this->predefinedBadWordsService->getEn()
        );
        $badWords = array_merge($badWords, $predefinedBadWords);
        foreach ($chatMessages as $userId => &$messages) {
            foreach ($messages as $message => $usedTimes) {
                if (!in_array($message, $badWords)) {
                    unset($messages[$message]);
                }
            }
            if (empty($messages)) unset($chatMessages[$userId]);
        }
        $biggestUsage = 0;
        $mostFrequentUser = null;
        $abusedWord = "";
        foreach ($chatMessages as $userId => $userMessages) {
            if (empty($userMessages)) continue;
            foreach ($userMessages as $message => $usagesCount) {
                if ($usagesCount > $biggestUsage) {
                    $biggestUsage = $usagesCount;
                    $mostFrequentUser = $userId;
                    $abusedWord = $message;
                }
            }
        }
        if ($biggestUsage === 0) return [];
        $realName = $this->redisRepo->getRealName((int) $mostFrequentUser);
        return [$realName, $biggestUsage, $abusedWord];
    }

    public function getToxicDegreeForUser(int $userId, int $chatId): string
    {
        $userBadWords = $this->getUserBadMessages($userId, $chatId);
        $justWords = array_values($userBadWords);
        $mostFrequentWordCount = reset($justWords);
        return $this->getToxicDegree((int) $mostFrequentWordCount);
    }

    public function getToxicDegree(int $usages): string
    {
        switch (true) {
            case $usages >= 350:
                return "🔥🔥🔥 TOXIC GOD 🔥🔥🔥";
            case $usages >= 300:
                return "⚔️⚔️ TOXIC AVENGER ⚔️⚔️";
            case $usages >= 250:
                return "💂💂 TOXIC SOLDIER 💂💂";
            case $usages >= 200:
                return "👹👹 TOXIC PREDATOR 👹👹";
            case $usages >= 150:
                return "🦠 TOXIC VIRUS 🦠";
            case $usages >= 100:
                return "🗑️ REAL TRASH 🗑️";
            case $usages >= 75:
                return "🏄‍♂️ MENTAL SICKNESS 🏄‍♂️";
            case $usages >= 50:
                return "👺 TOURETTE SYNDROME 👺";
            case $usages >= 25:
                return "🤯 HARD NEUROSIS 🤯";
            case $usages >= 10:
                return "🤬 DIFFICULT DAY 🤬";
            case $usages >= $this->toxicLimit:
                return "😬 DIRTY BOY 😬";
            default:
                return "? UNKNOWN STATUS ?";
        }
    }
}
