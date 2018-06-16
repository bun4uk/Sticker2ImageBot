<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/16/18
 * Time: 1:55 PM
 */

include 'vendor/autoload.php';
include 'TelegramBot.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$token = trim(file_get_contents('./config/dev'));
$log = new Logger('img_log');
$telegramApi = new TelegramBot($token, $log);

try {
    $log->pushHandler(new StreamHandler('./logs/img_log.log', 200));
} catch (\Exception $exception) {
    error_log('logger exception');
}

$request = file_get_contents('php://input');
$request = json_decode($request);

$update = $request;

if (isset($update->message->text) && false !== strpos($update->message->text, 'start')) {
    $telegramApi->sendMessage($update->message->chat->id, 'Hi there! I\'m Sticker2Image bot. I\'ll help you to convert your stickers to PNG images. Just send me some sticker.');
    return true;
}
if (isset($update->message->sticker)) {
    try {
        $telegramApi->sendMessage($update->message->chat->id, 'I\'ve got your sticker');
        $telegramApi->sendMessage($update->message->chat->id, '...');
        $file = $telegramApi->getFile($update->message->sticker);
        $filePath = "https://api.telegram.org/file/bot$token/" . $file->file_path;

        $log->log(200, $update->message->chat->id);
        if (isset($update->message->chat->first_name)) {
            $log->log(200, $update->message->chat->first_name);
        }
        if (isset($update->message->chat->last_name)) {
            $log->log(200, $update->message->chat->last_name);
        }
        if (isset($update->message->chat->username)) {
            $log->log(200, $update->message->chat->username);
        }
        $log->log(200, $update->message->sticker->set_name);
        $log->log(200, $update->message->sticker->file_id);
        $log->log(200, $filePath);
        $log->log(200, '==============');


        $fileName = './img_' . time();
        $imgPathWebp = $fileName . '.webp';
        copy(
            $filePath,
            $imgPathWebp
        );
        $telegramApi->sendPhoto($update->message->chat->id, $imgPathWebp);
        unlink($imgPathWebp);

        return true;

    } catch (\Exception $exception) {
        $telegramApi->sendMessage($update->message->chat->id, 'Sorry, I am tired. Some server error. Try in a few minutes :\'( ');
        $log->log(404, '===============');
        $log->log(404, $exception->getCode());
        $log->log(404, $exception->getMessage());
        $log->log(404, '===============');
    }
}
$telegramApi->sendMessage($update->message->chat->id, 'I understand only stickers');
