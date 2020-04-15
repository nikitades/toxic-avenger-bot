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

    public function __construct(RedisRepository $redisRepository, ParameterBagInterface $params, WordService $wordService)
    {
        $this->redisRepo = $redisRepository;
        $this->toxicLimit = $params->get("toxic.limit");
        $this->wordService = $wordService;
    }

    /**
     * Returns array like ['@samueljackson', 'bamf']
     * 
     * @param integer $chatId
     * @return array<string>
     */
    public function getToxicUser(int $chatId): array
    {
        $messages = $this->redisRepo->getLastMessages($chatId);
        if (empty($messages)) return [];
        $badWords = $this->redisRepo->getBadWordsForChat($chatId);
        $output = [];
        foreach ($messages as $userId => $messages) {
            foreach ($messages as $message => $count) {
                if (in_array($message, $badWords)) {
                    if (!isset($output[$userId])) $output[$userId] = [];
                    if (!isset($output[$userId][$message])) $output[$userId][$message] = 0;
                    $output[$userId][$message] = $count;
                }
            }
        }
        if (empty($output)) return [];
        uasort(
            $output,
            function ($a, $b) {
                $amax = max(array_values($a));
                $bmax = max(array_values($b));
                return $amax === $bmax ? 0
                    : ($amax > $bmax ? 1 : -1);
            }
        );
        $userIds = array_keys($output);
        /** @var string */
        $toxicUserId = reset($userIds);
        $toxicUserWords = $output[$toxicUserId];
        return [$toxicUserId, implode(", ", array_keys($toxicUserWords))];
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

        $userBadMessages = $this->getUserBadMessages($userId, $chatId);

        $intersectedBadWords = array_intersect($thisMessageWords, array_keys($userBadMessages));
        if (empty($intersectedBadWords)) return [];

        foreach ($intersectedBadWords as $intersectedBadWord) {
            $this->redisRepo->setMaxResultsIfBigger($userBadMessages[$intersectedBadWord], $chatId, $userId);
            if ($userBadMessages[$intersectedBadWord] >= $this->toxicLimit) {
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
            case $usages > 100:
                return "🔥🔥🔥 TOXIC GOD 🔥🔥🔥";
            case $usages > 80:
                return "⚔️⚔️ TOXIC AVENGER ⚔️⚔️";
            case $usages > 60:
                return "💂💂 TOXIC SOLDIER 💂💂";
            case $usages > 50:
                return "👹👹 TOXIC PREDATOR 👹👹";
            case $usages > 40:
                return "🦠 TOXIC VIRUS 🦠";
            case $usages > 30:
                return "🗑️ REAL TRASH 🗑️";
            case $usages > 20:
                return "🏄‍♂️ MENTAL SICKNESS 🏄‍♂️";
            case $usages > 15:
                return "👺 TOURETTE SYNDROME 👺";
            case $usages > 10:
                return "🤯 HARD NEUROSIS 🤯";
            case $usages > 7:
                return "🤬 DIFFICULT DAY 🤬";
            case $usages > 5:
                return "😬 DIRTY BOY 😬";
            default:
                return "? UNKNOWN STATUS ?";
        }
    }
}
