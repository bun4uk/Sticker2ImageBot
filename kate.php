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
include 'Dictionary.php';

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

$jsonRequest = file_get_contents('php://input');
$request = json_decode($jsonRequest);

$update = $request;

if (isset($update->message)) {
    $chatId = $update->message->chat->id;

    if (isset($update->message->chat->username)) {
        if (mb_strtolower($update->message->chat->username) === Dictionary::PAULMAKARON) {
            if (false !== strpos($update->message->text, 'debug')) {
                ob_start();
                print_r(json_decode($jsonRequest, 1));
                $ob = ob_get_clean();
                file_put_contents('./logs/request_dump.txt', $ob);
                file_put_contents('./logs/request_dump.json', $jsonRequest);
                exec('jsonlint-py -f ./logs/request_dump.json > ./logs/request_dump.json');
                $telegramApi->sendDocument(
                    $chatId, './logs/request_dump.json', 'json'
                );
                $telegramApi->sendDocument(
                    $chatId, './logs/request_dump.txt', 'txt'
                );
            }
            $telegramApi->sendMessage($chatId, 'Привет, нащяльникэ');
            return true;
        }

        if (mb_strtolower($update->message->chat->username) === Dictionary::KNEGRIENKO) {
            if (isset($update->message->text) && false !== strpos($update->message->text, 'start')) {
                $telegramApi->sendMessage($chatId, 'Приветик');
                return true;
            }
            $telegramApi->sendMessage($chatId, 'Катюш, ' . $dict[array_rand($dict, 1)]);
            return true;
        }

    }
    $telegramApi->sendMessage($chatId, 'This is a private bot');
    return true;
}