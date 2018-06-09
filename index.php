<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/16/18
 * Time: 1:55 PM
 */

include('vendor/autoload.php');
include('TelegramBot.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;


$token = trim(file_get_contents('./config'));
$log = new Logger('img_log');
$telegramApi = new TelegramBot($token, $log);
$log->pushHandler(new StreamHandler('./img_log.log', 200));

$request = file_get_contents('php://input');
$request = json_decode($request);

$update = $request;


$dateNow = new DateTime('NOW');
$msgDate = (new DateTime())->setTimestamp($update->message->date);
$diff = $msgDate->diff(new DateTime('NOW'));


if (isset($update->message->text)) {
    if (strstr($update->message->text, 'start')) {
        try {
            $telegramApi->sendMessage($update->message->chat->id, 'Hi there! I\'m Sticker2Image bot. I\'ll help you to convert your stickers to PNG images. Just send me some sticker.');

        } catch (\Exception $exception) {
            print_r($exception->getMessage());

        }
    }

    echo date("Y-m-d h:m:s") . ' - ' . $update->message->text . "\n";
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
        if (isset($update->message->last_name)) {
            $log->log(200, $update->message->chat->last_name);
        }
        if (isset($update->message->username)) {
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
    }
}
$telegramApi->sendMessage($update->message->chat->id, 'I understand only stickers');
