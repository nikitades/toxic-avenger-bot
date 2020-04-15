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
        if (array_sum($counts) < $this->historySize) {
            throw new NotEnoughMessagesInHistoryException((string) array_sum($counts) . '/' . $this->historySize);
        }
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
        foreach ($normalizedWords as $normalizedWord) {
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
     * @return array<string>
     */
    public function getMaxResultsForChat(int $chatId): array
    {
        $maxResultsKey = "maxResults:$chatId:*";
        $chatMaxResultsKeys = $this->client->keys($maxResultsKey);
        $chatMaxResultsCounts = $this->client->mget($chatMaxResultsKeys);
        $chatMaxResultsUsers = array_map(
            fn ($keyStr) => explode(":", $keyStr)[2],
            $chatMaxResultsKeys
        );
        $maxResultsUser = null;
        $maxResultsCount = 0;
        foreach ($chatMaxResultsCounts as $i => $count) {
            if ($count > $maxResultsCount) {
                $maxResultsCount = $count;
                $maxResultsUser = $chatMaxResultsUsers[$i];
            }
        }
        if ($maxResultsUser === null) return [];
        return [$maxResultsUser, $maxResultsCount];
    }

    public function getMaxResults(int $chatId, int $userId): int
    {
        return (int) $this->client->get("maxResults:$chatId:$userId");
    }

    public function setMaxResults(int $newResult, int $chatId, int $userId): void
    {
        $this->client->set("maxResults:$chatId:$userId", $newResult);
    }

    public function setMaxResultsIfBigger(int $newResult, int $chatId, int $userId): void
    {
        $oldResult = $this->getMaxResults($chatId, $userId);
        if ($newResult > $oldResult) {
            $this->setMaxResults($newResult, $chatId, $userId);
        }
    }
}
