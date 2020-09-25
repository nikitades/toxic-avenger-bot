<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Psr\Log\LoggerInterface;
use App\Service\ToxicityService;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use App\Repository\SaveMessageDTO;
use App\Repository\RedisRepository;
use App\Service\WordService;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Commands\UserCommand;
use Symfony\Component\HttpFoundation\Response;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;

class GenericmessageCommand extends SystemCommand
{
    private LoggerInterface $logger;
    private RedisRepository $redisRepo;
    private ToxicityService $toxicityService;
    private WordService $wordService;
    private int $toxicLimit;

    public function __construct(Telegram $tg, Update $update)
    {
        parent::__construct($tg, $update);
        global $kernel;
        $this->logger = $kernel->getContainer()->get("logger.pub");
        $this->redisRepo = $kernel->getContainer()->get("redis.repo.pub");
        $this->toxicityService = $kernel->getContainer()->get("toxicity.service.pub");
        $this->toxicLimit = $kernel->getContainer()->getParameter("toxic.limit");
        $this->wordService = $kernel->getContainer()->get("word.service.pub");
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

        if ($message->getChat()->getType() === "private") {
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text'    => "😞 Sorry, this bot is for *chat* usage only 😞",
                'parse_mode' => 'markdown'
            ];
            return Request::sendMessage($data);
        }

        $Allah = rand(0, 6) > 5;

        if (empty(trim($message->getText()))) {
            return new ServerResponse(['ok' => true], "MrNagoorBaba");
        };

        if (!$Allah) {
            $this->saveMessage($message);
        }

        $userToxicWords = $this->getUserToxicWords($message);

        if (!empty($userToxicWords)) {
            $this->logger->debug("Found a toxic user " . $message->getFrom()->getId() . " from chat " . $message->getChat()->getId());
            $userStatus = $this->toxicityService->getToxicDegreeForUser($message->getFrom()->getId(), $message->getChat()->getId());
            if ($Allah) {
                Request::sendMessage([
                    'chat_id' => $message->getChat()->getId(),
                    'text'    => ('☣️ User @' . $message->getFrom()->getUsername() . ' is *' . $userStatus . '* for reaching the limit of *' . $this->toxicLimit . '* toxic words! ☣️'),
                    'parse_mode' => 'markdown'
                ]);
                Request::sendMessage([
                    'chat_id' => $message->getChat()->getId(),
                    'text'    => ('☝️ Bul @' . $message->getFrom()->getUsername() . ' was saved by Allah! Be careful next time! ☝️'),
                    'parse_mode' => 'markdown'
                ]);
                return;
            }
            Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text'    => ('☣️ User @' . $message->getFrom()->getUsername() . ' is *' . $userStatus . '* for reaching the limit of *' . $this->toxicLimit . '* toxic words! ☣️'),
                'parse_mode' => 'markdown'
            ]);
            $escapedUserToxicWords = array_map(
                fn ($word) => $this->wordService->escapeSwearWord($word) . ": " . $userToxicWords[$word],
                array_keys($userToxicWords)
            );
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text' => "☣️ (" . implode(", ", $escapedUserToxicWords) . ") ☣️"
            ];
            return Request::sendMessage($data);         // Send message!
        }

        return new ServerResponse(['ok' => true], "MrNagoorBaba");
    }

    /**
     * @param Message $message
     * @return array<string,int>
     */
    private function getUserToxicWords(Message $message): array
    {
        return $this->toxicityService->checkIfUserIsToxic(
            $message->getText(),
            $message->getChat()->getId(),
            $message->getFrom()->getId()
        );
    }

    private function saveMessage(Message $message): void
    {
        $saveMessageDTO = new SaveMessageDTO();
        $saveMessageDTO->userName = $message->getFrom()->getUsername();
        $saveMessageDTO->userId = $message->getFrom()->getId();
        $saveMessageDTO->messageText = $message->getText();
        $saveMessageDTO->messageTime = $message->getDate();
        $saveMessageDTO->chatId = $message->getChat()->getId();
        $this->redisRepo->saveMessage($saveMessageDTO);
    }
}
