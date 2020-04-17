<?php

namespace App\Repository;

use App\Exception\NotEnoughMessagesInHistoryException;
use App\Service\WordService;
use Predis\Client as RedisClient;
use Longman\TelegramBot\Entities\Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RedisRepository
{
    /**
     * @var RedisClient<string>
     */
    private RedisClient $client;
    private int $historySize;
    private WordService $wordService;

    /**
     * @param RedisClient<string> $redisClient ///WAT
     */
    public function __construct(RedisClient $redisClient, ParameterBagInterface $params, WordService $wordService)
    {
        $this->client = $redisClient;
        $this->historySize = (int) $params->get("history.size");
        $this->wordService = $wordService;
    }

    /**
     * Returns something like
     *  [
     *      '3242342432' => [
     *              'hi' => 3,
     *              'hello' => 4
     *      ],
     *      '5435342342' => [
     *              'how are you' => 5,
     *              'okay' => 6
     *      ]
     *  ]
     * 
     * @return array<string,array<string,int>>
     */
    public function getLastMessages(int $chatId): array
    {
        $chatMessagesKey = "$chatId:*";
        /** @var string[] */
        $chatKeys = $this->client->keys($chatMessagesKey);
        if (empty($chatKeys)) return [];
        $userIds = array_map(fn ($chatKey) => explode(":", $chatKey)[1], $chatKeys);
        $words = array_map(fn ($chatKey) => explode(":", $chatKey)[2], $chatKeys);

        $counts = $this->client->mget($chatKeys);
        $output = [];
        foreach ($counts as $i => $count) {
            $output[$userIds[$i]][$words[$i]] = $count;
        }
        return $output;
    }

    public function saveMessage(SaveMessageDTO $saveMessageDTO): void
    {
        $chatMessagesKey = $saveMessageDTO->chatId . ":*";
        /** @var string[] */
        $chatKeys = $this->client->keys($chatMessagesKey);
        if (count($chatKeys) > $this->historySize) {
            $keysToRemove = array_slice($chatKeys, 0, count($chatKeys) - $this->historySize + 1);
            $this->client->del($keysToRemove);
        }

        $normalizedWords = $this->wordService->getProcessedMessage($saveMessageDTO->messageText);
        $normalizedWholeMessage = $this->wordService->normalizeWord($saveMessageDTO->messageText);
        foreach ([...$normalizedWords, $normalizedWholeMessage] as $normalizedWord) {
            $messageKey = implode(':', [
                $saveMessageDTO->chatId,
                $saveMessageDTO->userId,
                $normalizedWord
            ]);
            $this->client->incr($messageKey);
        }


        $this->client->set("realnames:" . $saveMessageDTO->userId, $saveMessageDTO->userName);
        $this->client->incr("usages:" . $saveMessageDTO->chatId);
    }

    public function getChatMessagesCount(Message $message): int
    {
        return (int) $this->client->get("usages:" . $message->getChat()->getId());
    }

    /**
     * @param integer $chatId
     * @return string[]
     */
    public function getBadWordsForChat(int $chatId): array
    {
        $badWordsKey = "badwords:$chatId";
        return $this->client->smembers($badWordsKey);
    }

    public function checkIfBadWordIsInChat(string $badWord, int $chatId): bool
    {
        $badWordsForChat = $this->getBadWordsForChat($chatId);
        return in_array($badWord, $badWordsForChat);
    }

    /**
     * @param string $word
     * @param integer $chatId
     * @return string[]
     */
    public function addBadWordForChat(string $word, int $chatId): array
    {
        $badWordKey = "badwords:$chatId";
        $this->client->sadd($badWordKey, [$word]);
        return $this->client->smembers($badWordKey);
    }

    /**
     * @param string $word
     * @param integer $chatId
     * @return string[]
     */
    public function removeBadWordForChat(string $word, int $chatId): array
    {
        $badWordKey = "badwords:$chatId";
        $this->client->srem($badWordKey, $word);
        return $this->client->smembers($badWordKey);
    }

    public function getRealName(int $userId): string
    {
        return $this->client->get("realnames:$userId") ?? (string) $userId;
    }

    public function getIdByName(string $username): ?int
    {
        $realnamesKeys = $this->client->keys("realnames:*");
        if (empty($realnamesKeys)) return null;
        $realnames = $this->client->mget($realnamesKeys);
        $realanmesIds = array_map(
            fn ($realnameKey) => explode(":", $realnameKey)[1],
            $realnamesKeys
        );
        foreach ($realnames as $i => $name) {
            if ($name === $username) {
                return (int) $realanmesIds[$i];
            }
        }
        return null;
    }

    /**
     * @param integer $chatId
     * @return array<mixed>
     */
    public function getMaxResultsForChat(int $chatId): array
    {
        $maxResultsKey = "maxResults:$chatId:*";
        $chatMaxResultsKeys = $this->client->keys($maxResultsKey);
        if (empty($chatMaxResultsKeys)) return [];
        $chatMaxResultsCounts = $this->client->mget($chatMaxResultsKeys);

        //TODO: тут инт?
        /** @var array<int> */
        $chatMaxResultsUsers = array_map(
            fn ($keyStr) => explode(":", $keyStr)[2],
            $chatMaxResultsKeys
        );

        /** @var array<string> */
        $chatMaxResultsWords = array_map(
            fn ($keyStr) => explode(":", $keyStr)[3],
            $chatMaxResultsKeys
        );

        $maxResultsUser = null;
        $maxResultsCount = 0;
        $maxResultsWord = "";
        foreach ($chatMaxResultsCounts as $i => $count) {
            if ($count > $maxResultsCount) {
                $maxResultsCount = (int) $count;
                $maxResultsWord = (string) $chatMaxResultsWords[$i];
                $maxResultsUser = (string) $chatMaxResultsUsers[$i];
            }
        }
        if ($maxResultsUser === null) return [];
        return [$maxResultsUser, $maxResultsCount, $maxResultsWord];
    }

    /**
     * @param integer $chatId
     * @param integer $userId
     * @return array<mixed>
     */
    public function getMaxResult(int $chatId, int $userId): array
    {
        $userMaxResults = $this->getMaxResults($chatId, $userId);
        $maxResults = 0;
        $word = "";
        foreach ($userMaxResults as $thisWord => $count) {
            if ($count > $maxResults) {
                $maxResults = $count;
                $word = $thisWord;
            }
        }
        if ($maxResults === 0) return [];
        return [$word, $maxResults];
    }

    /**
     * @param integer $chatId
     * @param integer $userId
     * @return array<string,int>
     */
    public function getMaxResults(int $chatId, int $userId): array
    {
        $userMaxResultsKeys = $this->client->keys("maxResults:$chatId:$userId:*");
        if (empty($userMaxResultsKeys)) return [];
        $userMaxResultsCounts = $this->client->mget($userMaxResultsKeys);
        $userMaxresultsWords = array_map(fn ($strKey) => explode(":", $strKey)[3], $userMaxResultsKeys);
        $output = [];
        foreach ($userMaxresultsWords as $i => $word) {
            $output[$word] = $userMaxResultsCounts[$i];
        }
        arsort($output);
        return $output;
    }

    public function getMaxResultsForWord(string $word, int $chatId, int $userId): int
    {
        return (int) $this->client->get("maxResults:$chatId:$userId:$word");
    }

    public function setMaxResults(string $word, int $newResult, int $chatId, int $userId): void
    {
        $this->client->set("maxResults:$chatId:$userId:$word", $newResult);
    }

    public function setMaxResultsIfBigger(string $word, int $newResult, int $chatId, int $userId): void
    {
        $currentWordResults = $this->getMaxResultsForWord($word, $chatId, $userId);

        if ($newResult > $currentWordResults) {
            $this->setMaxResults($word, $newResult, $chatId, $userId);
        }
    }
}
