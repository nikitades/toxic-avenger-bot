<?php

namespace App\Repository;

use App\Exception\NotEnoughMessagesInHistoryException;
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

    /**
     * @param RedisClient<string> $redisClient ///WAT
     */
    public function __construct(RedisClient $redisClient, ParameterBagInterface $params)
    {
        $this->client = $redisClient;
        $this->historySize = (int) $params->get("history.size");
    }

    /**
     * Returns something like
     *  [
     *      '3242342432' => 'hi',
     *      '5435342342' => 'how are you'
     *  ]
     * 
     * @return array<string,string>
     */
    public function getLastMessages(int $chatId): array
    {
        $chatMessagesKey = "$chatId:*";
        /** @var string[] */
        $chatKeys = $this->client->keys($chatMessagesKey);
        $userIds = array_map(fn ($chatKey) => explode(":", $chatKey)[1], $chatKeys);
        $messages = $this->client->mget($chatKeys);
        if (count($messages) < $this->historySize) {
            throw new NotEnoughMessagesInHistoryException((string) count($messages));
        }
        $output = [];
        foreach ($messages as $i => $message) $output[$userIds[$i]] = $message;
        return $output;
    }

    //TODO: переделать редис-структуру на хранение количества использований (сделать слова не строками а хешмапами)

    public function saveMessage(SaveMessageDTO $saveMessageDTO): void
    {
        $chatMessagesKey = $saveMessageDTO->chatId . ":*";
        /** @var string[] */
        $chatKeys = $this->client->keys($chatMessagesKey);
        if (count($chatKeys) > $this->historySize) {
            $keysToRemove = array_slice($chatKeys, 0, count($chatKeys) - $this->historySize + 1);
            $this->client->del($keysToRemove);
        }

        $messageKey = implode(':', [
            $saveMessageDTO->chatId,
            $saveMessageDTO->userId,
            $saveMessageDTO->messageTime
        ]);
        $this->client->set($messageKey, $saveMessageDTO->messageText);
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
}
