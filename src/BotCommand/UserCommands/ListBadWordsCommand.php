<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Psr\Log\LoggerInterface;

class ListBadWordsCommand extends UserCommand
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
    protected $name = 'listbadwords';
    /** @var string */
    protected $description = 'Lists all the registered bad words';
    /** @var string */
    protected $usage = '/listBadWords';
    /** @var string */
    protected $version = '1.0.0';

    public function execute()
    {
        $message = $this->getMessage();

        $words = $this->redisRepo->getBadWordsForChat($message->getChat()->getId());

        $this->logger->debug("List bad words command executed at chat " . $message->getChat()->getId());

        if (empty($words)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text'    => "No bad words registered!"
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $message->getChat()->getId(),
            'text'    => "Bad words: \n" . implode(", \n", array_map(
                fn ($word) => "*$word*",
                $words
            )),
            'parse_mode' => 'markdown'
        ]);
    }
}
