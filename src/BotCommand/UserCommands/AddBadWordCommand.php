<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Psr\Log\LoggerInterface;

class AddBadWordCommand extends UserCommand
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
    protected $name = 'addbadword';                      // Your command's name
    /** @var string */
    protected $description = 'Adds a bad word to the chat'; // Your command description
    /** @var string */
    protected $usage = '/addBadWord';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();
        $word = trim(substr($message->getText(), strlen((string) $message->getFullCommand())));

        if (empty($word)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "No words given",
                'parse_mode' => 'markdown'
            ]);
        }

        if (strlen($word) < 3) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Word *$word* is too short!",
                'parse_mode' => 'markdown'
            ]);
        }

        $this->redisRepo->addBadWordForChat((string) $word, $message->getChat()->getId());

        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text'    => "Word *$word* successfully added!",
            'parse_mode' => 'markdown'
        ];

        $this->logger->debug("Add bad word command executed at chat " . $message->getChat()->getId());
        return Request::sendMessage($data);
    }
}
