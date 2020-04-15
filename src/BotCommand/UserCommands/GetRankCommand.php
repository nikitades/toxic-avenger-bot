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

class GetRankCommand extends UserCommand
{
    private LoggerInterface $logger;
    private RedisRepository $redisRepo;
    private ToxicityService $toxicityService;
    private int $historySize;

    public function __construct(Telegram $tg, Update $update)
    {
        parent::__construct($tg, $update);
        global $kernel;
        $this->logger = $kernel->getContainer()->get("logger.pub");
        $this->redisRepo = $kernel->getContainer()->get("redis.repo.pub");
        $this->toxicityService = $kernel->getContainer()->get("toxicity.service.pub");
        $this->historySize = $kernel->getContainer()->getParameter("history.size");
    }

    /** @var string */
    protected $name = 'getrank';                      // Your command's name
    /** @var string */
    protected $description = 'Gets the max toxicity rank achieved for the chat'; // Your command description
    /** @var string */
    protected $usage = '/getRank';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();
        $this->logger->debug("Get max rank command executed at chat " . $message->getChat()->getId());
        
        try {
            $chatMaxResults = $this->redisRepo->getMaxResultsForChat($message->getChat()->getId());
        } catch (NotEnoughMessagesInHistoryException $e) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '⚠️ ' . $e->getMessage() . ' ⚠️',
                'parse_mode' => 'markdown'
            ]);
        }
        if (empty($chatMaxResults)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '👽 No rank holders found! 👽',
                'parse_mode' => 'markdown'
            ]);
        }

        $userId = (int) $chatMaxResults[0];
        $usageCount = (int) $chatMaxResults[1];

        $realName = $this->redisRepo->getRealName($userId);
        $rank = $this->toxicityService->getToxicDegree($usageCount);

        return Request::sendMessage([
            'chat_id' => $message->getChat()->getId(),
            'text' => '👑 Rank *' . $rank . '* belongs to @' . $realName . ' with *' . $usageCount . '* usages! 👑',
            'parse_mode' => 'markdown'
        ]);
    }
}
