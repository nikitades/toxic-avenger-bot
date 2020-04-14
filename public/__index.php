<?php
// Load composer
require __DIR__ . '/../vendor/autoload.php';

$bot_api_key  = '1266133149:AAH5oNh64AMsOF_vCinuKsGeT3MfAZxSJ2A';
$bot_username = 'ToxicAvengerBot';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Handle telegram webhook request
    $telegram->addCommandsPath("../src/BotCommand");
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}
