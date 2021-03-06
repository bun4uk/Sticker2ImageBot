<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/16/18
 * Time: 1:55 PM
 */

date_default_timezone_set('Europe/Kiev');

include 'vendor/autoload.php';
include 'Dictionary.php';
include 'TelegramBot.php';
include 'Database.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$token = trim(file_get_contents('./config/dev'));
$log = new Logger('dev_img_log');
$telegramApi = new TelegramBot($token, $log);
$db = new Database();

try {
    $log->pushHandler(new StreamHandler('./logs/dev_img_log.log', 200));
} catch (\Exception $exception) {
    error_log('logger exception');
}

$request = file_get_contents('php://input');
$request = json_decode($request);

$update = $request;

$db->setUser([
    'chat_id' => $update->message->chat->id,
    'username' => $update->message->chat->username ?? null,
    'type' => $update->message->chat->type ?? null
]);

if (isset($update->message->text) && false !== strpos($update->message->text, 'start')) {
    $telegramApi->sendMessage($update->message->chat->id, 'Hi there! I\'m Sticker2Image bot. I\'ll help you to convert your stickers to PNG images. Just send me some sticker.');
    return true;
}

if (
    $update->message->chat->id === 7699150
    && false !== strpos($update->message->text, '/call_count')
) {
    ob_start();
    print_r(json_decode($jsonRequest, 1));
    $ob = ob_get_clean();
    file_put_contents('./logs/request_dump_raw.json', $jsonRequest);
    exec('jsonlint-py -f ./logs/request_dump_raw.json > ./logs/request_dump.json');
    unlink('./logs/request_dump_raw.json');
    $telegramApi->sendDocument(
        $update->message->chat->id, './logs/request_dump.json', 'json'
    );
    unlink('./logs/request_dump.json');

    $command = explode(' ', $update->message->text);
    $date = (isset($command[1]) && !empty($command[1])) ? $command[1] : (new \DateTime())->format('Y-m-d');
    exec("cat logs/img_log.log | grep === | grep {$date} | wc -l", $result);
    $telegramApi->sendMessage($update->message->chat->id, json_encode($result));
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

        $fileName = './img_' . time() . mt_rand();
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

if (isset($update->message, $update->message->chat->id)) {
    $telegramApi->sendMessage($update->message->chat->id, 'I understand only stickers');
}
