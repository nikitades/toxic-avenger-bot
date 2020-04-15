<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Psr\Log\LoggerInterface;

class KickToxicCommand extends UserCommand
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
    protected $name = 'kicktoxic';                      // Your command's name
    /** @var string */
    protected $description = 'Kicks the toxic one out of the chat'; // Your command description
    /** @var string */
    protected $usage = '/kickToxic';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();

        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text' => "🔫 Не надо там по углам курить, шабить, дрочить, мастурбировать, что конечно многие одно и то же 🔫"
        ];

        $this->logger->debug("Kick toxic command executed at chat " . $message->getChat()->getId());
        return Request::sendMessage($data);
    }
}
