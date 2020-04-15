<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Exception\NotEnoughMessagesInHistoryException;
use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Psr\Log\LoggerInterface;

class IsToxicCommand extends UserCommand
{
    private LoggerInterface $logger;
    private RedisRepository $redisRepo;
    private ToxicityService $toxicityService;
    private int $historySize;
    private int $toxicLimit;

    public function __construct(Telegram $tg, Update $update)
    {
        parent::__construct($tg, $update);
        global $kernel;
        $this->logger = $kernel->getContainer()->get("logger.pub");
        $this->redisRepo = $kernel->getContainer()->get("redis.repo.pub");
        $this->toxicityService = $kernel->getContainer()->get("toxicity.service.pub");
        $this->historySize = $kernel->getContainer()->getParameter("history.size");
        $this->toxicLimit = $kernel->getContainer()->getParameter("toxic.limit");
    }

    /** @var string */
    protected $name = 'istoxic';                      // Your command's name
    /** @var string */
    protected $description = 'Checks is the user is toxic'; // Your command description
    /** @var string */
    protected $usage = '/isToxic';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();
        $this->logger->debug("Is toxic command executed at chat " . $message->getChat()->getId());

        $username = substr($message->getText(), mb_strlen((string) $message->getFullCommand()));
        $username = str_replace("@", "", $username);
        $username = trim($username);

        $userId = $this->redisRepo->getIdByName($username);
        if (empty($userId)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '🤦 User not found! 🤦',
                'parse_mode' => 'markdown'
            ]);
        }

        $userToxicWords = $this->toxicityService->getUserBadMessages(
            $message->getFrom()->getId(),
            $message->getChat()->getId()
        );

        $userIsToxic = false;
        foreach ($userToxicWords as $word => $usedTimes) if ((int) $usedTimes >= $this->toxicLimit) {
            $userIsToxic = true;
        }

        if (!$userIsToxic) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '😂 No, user @' . $message->getFrom()->getUsername() . ' is not toxic! 😂',
                'parse_mode' => 'markdown'
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $message->getChat()->getId(),
            'text' => '✔️ Yes, user @' . $message->getFrom()->getUsername() . ' is toxic! ✔️',
            'parse_mode' => 'markdown'
        ]);
    }
}
