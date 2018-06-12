<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 6/12/18
 * Time: 7:30 PM
 */


$dict = explode(':', trim(file_get_contents('./config/kate_dict')));

include 'vendor/autoload.php';
include 'TelegramBot.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$token = trim(file_get_contents('./config/kate'));
$log = new Logger('kate_log');
$telegramApi = new TelegramBot($token, $log);

try {
    $log->pushHandler(new StreamHandler('./logs/kate_log.log', 200));
} catch (\Exception $exception) {
    error_log('logger exception');
}

$request = file_get_contents('php://input');
$request = json_decode($request);

$update = $request;

if (isset($update->message) && ($update->message->chat->username === 'knegrienko')) {
    if (isset($update->message->text) && false !== strpos($update->message->text, 'start')) {
        $telegramApi->sendMessage($update->message->chat->id, 'Приветик');
        return true;
    }
    $telegramApi->sendMessage($update->message->chat->id, 'Катюш, ' . $dict[array_rand($dict, 1)]);
    return true;
}

if (isset($update->message) && ($update->message->chat->username === 'PaulMakaron')) {
    $telegramApi->sendMessage($update->message->chat->id, 'Привет, нащяльникэ');
    return true;
}

if (isset($update->message)) {
    $telegramApi->sendMessage($update->message->chat->id, 'This is a private bot');
    return true;
}