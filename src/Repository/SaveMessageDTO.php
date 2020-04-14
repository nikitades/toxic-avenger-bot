<?php

namespace App\Repository;

class SaveMessageDTO
{
    public int $chatId;
    public int $userId;
    public int $messageTime;
    public string $messageText;
    public string $userName;
}