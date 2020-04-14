<?php

namespace App\Exception;

use Exception;
use Throwable;

class NotEnoughMessagesInHistoryException extends Exception
{
    public function __construct(string $msg, int $code = 0, Throwable $prev = null)
    {
        parent::__construct($msg, $code, $prev);
        $this->message = "Not enough messages accumulated for the moment! Messages at history: " . $msg;
    }
}
