<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Repository\RedisRepository;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Psr\Log\LoggerInterface;

class HelpCommand extends UserCommand
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
    protected $name = 'help';                      // Your command's name
    /** @var string */
    protected $description = 'Shows the help'; // Your command description
    /** @var string */
    protected $usage = '/help';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();

        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text'    => "How to use the bot:
/start - begin using this bot
/help - show this message
/findToxic - find a toxic user from last {$this->historySize} messages
/addBadWord <word> - adds a bad word to this chat's list
/removeBadWord <word> - removes this bad word from this chat's list
/listBadWords - shows all the bad words defined for this chat
/getRank - shows user with the most toxic words usages for this chat for all times
/kickToxic - kicks the most toxic one from the chat

This bot stores last {$this->historySize} messages from this chat (new ones erase old ones).
When someone writes something toxic, bot makes you know.

You can add your own toxic words with /addBadWord <word> command.

***

Этот бот хранит последние {$this->historySize} сообщений из этого чата (новые стирают старые).
Когда кто-то пишет что-то токсичное, бот дает вам знать.

Вы можете добавить свои токсичные слова командой /addBadWord <слово>.
            "
        ];

        $this->logger->debug("Help command executed at chat " . $message->getChat()->getId());
        return Request::sendMessage($data);
    }
}
