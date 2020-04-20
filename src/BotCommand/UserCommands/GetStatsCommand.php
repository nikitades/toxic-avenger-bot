<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Exception\NotEnoughMessagesInHistoryException;
use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use App\Service\WordService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Psr\Log\LoggerInterface;

class GetStatsCommand extends UserCommand
{
    private LoggerInterface $logger;
    private RedisRepository $redisRepo;
    private ToxicityService $toxicityService;
    private WordService $wordService;
    private int $historySize;
    private int $toxicLimit;

    public function __construct(Telegram $tg, Update $update)
    {
        parent::__construct($tg, $update);
        global $kernel;
        $this->logger = $kernel->getContainer()->get("logger.pub");
        $this->redisRepo = $kernel->getContainer()->get("redis.repo.pub");
        $this->toxicityService = $kernel->getContainer()->get("toxicity.service.pub");
        $this->wordService = $kernel->getContainer()->get("word.service.pub");
        $this->toxicLimit = $kernel->getContainer()->getParameter("toxic.limit");
        $this->historySize = $kernel->getContainer()->getParameter("history.size");
    }

    /** @var string */
    protected $name = 'getstats';                      // Your command's name
    /** @var string */
    protected $description = 'Gets the statistics of the given user'; // Your command description
    /** @var string */
    protected $usage = '/getStats <user>';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();

        $this->logger->debug("Get stat command executed at chat " . $message->getChat()->getId());

        $username = substr($message->getText(), mb_strlen((string) $message->getFullCommand()));
        $username = str_replace("@", "", $username);
        $username = trim($username);

        if (empty($username)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Please, provide the user name (" . $this->usage . ')',
                'parse_mode' => 'markdown'
            ]);
        }

        $userId = (int) $this->redisRepo->getIdByName($username);
        if (empty($userId)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => '🤦 User not found! 🤦',
                'parse_mode' => 'markdown'
            ]);
        }

        $chatId = $message->getChat()->getId();

        $userLastBadMessages = $this->toxicityService->getUserBadMessages($userId, $chatId);
        $userLastBadMessages = array_slice($userLastBadMessages, 0, 10);
        $userAllTimeBadMessages = $this->redisRepo->getMaxResults($chatId, $userId);
        $userAllTimeBadMessages = array_slice($userAllTimeBadMessages, 0, 10);

        $recentWords = [];
        foreach ($userLastBadMessages as $word => $count) $recentWords[] = $this->wordService->escapeSwearWord($word) . " ($count)";

        Request::sendMessage([
            'chat_id' => $chatId,
            'text' => 'User @' . $username . ' used recently: ' . implode(", ", $recentWords),
        ]);

        $allTimeWords = [];
        foreach ($userAllTimeBadMessages as $word => $count) $allTimeWords[] = $this->wordService->escapeSwearWord($word) . " ($count)";

        return Request::sendMessage([
            'chat_id' => $chatId,
            'text' => 'User @' . $username . '\'s best records: ' . implode(", ", $allTimeWords),
        ]);
    }
}
