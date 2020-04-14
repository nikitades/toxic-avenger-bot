<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Psr\Log\LoggerInterface;

class RemoveBadWordCommand extends UserCommand
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
    protected $name = 'removebadword';                      // Your command's name
    /** @var string */
    protected $description = 'Removes a bad word from the chat'; // Your command description
    /** @var string */
    protected $usage = '/removeBadWord';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();
        $word = trim(substr($message->getText(), strlen((string) $message->getFullCommand())));

        $this->redisRepo->addBadWordForChat((string) $word, $message->getChat()->getId());
        $this->redisRepo->removeBadWordForChat((string) $word, $message->getChat()->getId());

        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text'    => "Word *$word* successfully removed!",
            'parse_mode' => 'markdown'
        ];

        $this->logger->debug("Remove bad word command executed at chat " . $message->getChat()->getId());
        return Request::sendMessage($data);
    }
}
