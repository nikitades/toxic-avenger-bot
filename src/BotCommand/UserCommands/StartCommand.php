<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\UserCommand;

class StartCommand extends UserCommand
{
    /** @var string */
    protected $name = 'start';                      // Your command's name
    /** @var string */
    protected $description = 'Run it first'; // Your command description
    /** @var string */
    protected $usage = '/start';                    // Usage of your command
    /** @var string */
    protected $version = '1.0.0';                  // Version of your command

    public function execute()
    {
        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $data = [                                  // Set up the new message data
            'chat_id' => $chat_id,                 // Set Chat ID to send the message to
            'text'    => 'Find a toxic user! Run /findToxic or /help for help', // Set message to send
        ];

        return Request::sendMessage($data);        // Send message!
    }
}
