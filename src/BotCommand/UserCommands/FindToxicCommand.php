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

class FindToxicCommand extends UserCommand
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
    protected $name = 'findtoxic';                      // Your command's name
    /** @var string */
    protected $description = 'Finds currently most toxic user'; // Your command description
    /** @var string */
    protected $usage = '/findToxic';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();

        try {
            /** @var array<mixed> */
            $toxicUser = $this->toxicityService->getMaxBadWordUsagesForChat($message->getChat()->getId());
        } catch (NotEnoughMessagesInHistoryException $e) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '⚠️ ' . $e->getMessage() . ' ⚠️',
                'parse_mode' => 'markdown'
            ]);
        }
        if (empty($toxicUser)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '❤️ No toxic users found! ❤️',
                'parse_mode' => 'markdown'
            ]);
        }

        /** @var string */
        $userName = $toxicUser[0];
        /** @var int */
        $usages = $toxicUser[1];

        if ($usages < $this->toxicLimit) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '❤️ No toxic users found! ❤️',
                'parse_mode' => 'markdown'
            ]);
        }

        $rank = $this->toxicityService->getToxicDegree($usages);

        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text' => '🤢 User @' . $userName . ' is *TOXIC* with rank *' . $rank . '* and *' . $usages . '* usages! 🤢',
            'parse_mode' => 'markdown'
        ];


        $this->logger->debug("Get max rank command executed at chat " . $message->getChat()->getId());
        return Request::sendMessage($data);
    }
}
