<?php

namespace App\Service;

use App\Repository\RedisRepository;
use App\Exception\NotEnoughMessagesInHistoryException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ToxicityService
{
    private RedisRepository $redisRepo;
    private int $toxicLimit;

    public function __construct(RedisRepository $redisRepository, ParameterBagInterface $params)
    {
        $this->redisRepo = $redisRepository;
        $this->toxicLimit = $params->get("toxic.limit");
    }

    /**
     * Returns array like ['@samueljackson', 'bamf']
     * 
     * @param array<string> $messages
     *  [
     *      '3423423' => 'hi',
     *      '2342342' => 'hello'
     *  ]
     * 
     * @return array<string>
     */
    public function getToxicUser(int $chatId, array $messages): array
    {
        $badWords = $this->redisRepo->getBadWordsForChat($chatId);
        $output = [];
        foreach ($messages as $userId => $message) {
            if (in_array($message, $badWords)) {
                if (!isset($output[$userId])) $output[$userId] = [];
                if (!isset($output[$userId][$message])) $output[$userId][$message] = 0;
                $output[$userId][$message]++;
            }
        }
        if (empty($output)) return [];
        rsort($output);
        $userNames = array_keys($output);
        /** @var string */
        $toxicUserName = reset($userNames);
        $toxicUserWords = reset($output);
        return [$toxicUserName, implode(", ", $toxicUserWords)];
    }

    /**
     * @param integer $chatId
     * @param integer $userId
     * @return string[]
     */
    public function checkIfUserIsToxic(int $chatId, int $userId): array
    {
        $badWords = $this->redisRepo->getBadWordsForChat($chatId);
        $messages = [];
        try {
            $messages = $this->redisRepo->getLastMessages($chatId);
        } catch (NotEnoughMessagesInHistoryException $e) {
            //
        }
        dump($messages);
        $badMessages = array_filter(
            $messages,
            fn ($msg) => in_array($msg, $badWords)
        );
        $userMessages = array_filter(
            $badMessages,
            fn ($key) => $key === $userId,
            ARRAY_FILTER_USE_KEY
        );
        if (count($userMessages) >= $this->toxicLimit) {
            return array_values($userMessages);
        }
        return [];
    }
}
