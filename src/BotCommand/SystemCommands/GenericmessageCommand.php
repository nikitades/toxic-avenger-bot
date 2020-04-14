<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Psr\Log\LoggerInterface;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use App\Repository\SaveMessageDTO;
use App\Repository\RedisRepository;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Symfony\Component\HttpFoundation\Response;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class GenericmessageCommand extends SystemCommand
{
    private LoggerInterface $logger;
    private RedisRepository $redisRepo;
    private ToxicityService $toxicityService;
    private int $toxicLimit;

    public function __construct(Telegram $tg, Update $update)
    {
        parent::__construct($tg, $update);
        global $kernel;
        $this->logger = $kernel->getContainer()->get("logger.pub");
        $this->redisRepo = $kernel->getContainer()->get("redis.repo.pub");
        $this->toxicityService = $kernel->getContainer()->get("toxicity.service.pub");
        $this->toxicLimit = $kernel->getContainer()->getParameter("toxic.limit");
    }

    /** @var string */
    protected $name = 'genericmessage';                      // Your command's name
    /** @var string */
    protected $description = 'Reacts to all the messages flood'; // Your command description
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();

        $saveMessageDTO = new SaveMessageDTO();
        $saveMessageDTO->userName = $message->getFrom()->getUsername();
        $saveMessageDTO->userId = $message->getFrom()->getId();
        $saveMessageDTO->messageText = $message->getText();
        $saveMessageDTO->messageTime = $message->getDate();
        $saveMessageDTO->chatId = $message->getChat()->getId();
        $this->redisRepo->saveMessage($saveMessageDTO);

        $userToxicWords = $this->toxicityService->checkIfUserIsToxic(
            $message->getChat()->getId(),
            $message->getFrom()->getId()
        );
        if (!empty($userToxicWords)) {
            $this->logger->debug("Found a toxic user " . $message->getFrom()->getId() . " from chat " . $message->getChat()->getId());
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text'    => ('User ' . $message->getFrom()->getUsername() . ' is **TOXIC** for reaching the limit of **' . $this->toxicLimit . '** toxic words!')
            ];
            Request::sendMessage($data);
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text' => "(" . implode(", ", $userToxicWords) . ")"
            ];
            return Request::sendMessage($data);         // Send message!
        }

        return new ServerResponse(['ok' => true], "MrNagoorBaba");
    }
}
