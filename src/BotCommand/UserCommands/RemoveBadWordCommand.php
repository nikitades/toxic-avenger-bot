<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use App\Service\WordService;
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
    private WordService $wordService;

    public function __construct(Telegram $tg, Update $update)
    {
        parent::__construct($tg, $update);
        global $kernel;
        $this->logger = $kernel->getContainer()->get("logger.pub");
        $this->redisRepo = $kernel->getContainer()->get("redis.repo.pub");
        $this->toxicityService = $kernel->getContainer()->get("toxicity.service.pub");
        $this->wordService = $kernel->getContainer()->get("word.service.pub");
    }

    /** @var string */
    protected $name = 'removebadword';                      // Your command's name
    /** @var string */
    protected $description = 'Removes a bad word from the chat'; // Your command description
    /** @var string */
    protected $usage = '/removeBadWord <word>';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();
        $word = trim(substr($message->getText(), strlen((string) $message->getFullCommand())));
        $escapedWord = $this->wordService->normalizeWord($word);

        if (empty($escapedWord)) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Please, provide the word (" . $this->usage . ')',
                'parse_mode' => 'markdown'
            ]);
        }

        $this->logger->debug("Remove bad word command executed at chat " . $message->getChat()->getId());

        if (!$this->redisRepo->checkIfBadWordIsInChat($escapedWord, $message->getChat()->getId())) {
            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => "Word *$escapedWord* was not found among bad words!",
                'parse_mode' => 'markdown'
            ]);
        }

        $this->redisRepo->removeBadWordForChat((string) $escapedWord, $message->getChat()->getId());

        return Request::sendMessage([
            'chat_id' => $message->getChat()->getId(),
            'text'    => "Word *$escapedWord* successfully removed!",
            'parse_mode' => 'markdown'
        ]);
    }
}
