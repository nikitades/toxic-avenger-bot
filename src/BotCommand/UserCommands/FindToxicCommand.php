<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Psr\Log\LoggerInterface;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use App\Repository\RedisRepository;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use App\Exception\NotEnoughMessagesInHistoryException;

class FindToxicCommand extends UserCommand
{
    private LoggerInterface $logger;
    private RedisRepository $redisRepo;
    private ToxicityService $toxicityService;

    public function __construct(Telegram $tg, Update $update)
    {
        parent::__construct($tg, $update);
        global $kernel;
        $this->logger = $kernel->getContainer()->get("logger.pub");
        $this->redisRepo = $kernel->getContainer()->get("redis.repo.pub");
        $this->toxicityService = $kernel->getContainer()->get("toxicity.service.pub");
    }

    /** @var string */
    protected $name = 'findtoxic';                      // Your command's name
    /** @var string */
    protected $description = 'Searches for the most toxic user in recent messages'; // Your command description
    /** @var string */
    protected $usage = '/findToxic';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();

        $toxicUser = null;
        try {
            $toxicUser = $this->toxicityService->getToxicUser($message->getChat()->getId());
        } catch (NotEnoughMessagesInHistoryException $e) {
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text'    => $e->getMessage()
            ];

            $this->logger->debug("Find toxic command executed at chat " . $message->getChat()->getId());
            return Request::sendMessage($data);
        }

        if (empty($toxicUser)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '❤️ No toxic users found! ❤️',
                'parse_mode' => 'markdown'
            ]);
        }
        $userId = (int) $toxicUser[0];
        /** @var string */
        $word = $toxicUser[1];
        $userName = $this->redisRepo->getRealName($userId);

        $data = [                                  // Set up the new message data
            'chat_id' => $message->getChat()->getId(),                 // Set Chat ID to send the message to
            'text'    => "☣️ The most toxic user is @$userName for abusing the word *$word* ☣️", // Set message to send
            'parse_mode' => 'markdown'
        ];

        $this->logger->debug("Find toxic command executed at chat " . $message->getChat()->getId());
        return Request::sendMessage($data);        // Send message!
    }
}
