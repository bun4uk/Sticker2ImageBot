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


$token = file_get_contents('./config');
$log = new Logger('img_log');
$telegramApi = new TelegramBot($token, $log);
$log->pushHandler(new StreamHandler('./img_log.log', 200));

$users = [];


if ('/bot' === $_SERVER['REQUEST_URI']) {
    file_put_contents('request_dump.html', $_REQUEST);
    file_put_contents('server_dump.html', $_SERVER);
    return true;
}

die('exit');

while (1) {
//    $telegramApi->sendMessage(2666474, 'Привет, Макс!');
    sleep(5);
    $updates = $telegramApi->getUpdates();

    foreach ($updates->result as $update) {

        $dateNow = new DateTime('NOW');
        $msgDate = (new DateTime())->setTimestamp($update->message->date);
        $diff = $msgDate->diff(new DateTime('NOW'));


        if ($diff->h > 1) {
            continue;
        }


        print_r($users);
        if (!array_key_exists($update->message->chat->id, $users)) {
            $users[$update->message->chat->id] = 1;
        } else {
            $users[$update->message->chat->id] += 1;
        }


        if ($users[$update->message->chat->id] > 10) {
            try {
                $telegramApi->sendMessage($update->message->chat->id, 'It is enough for today');
                continue;
            } catch (\Exception $exception) {
                print_r($exception->getMessage());
                continue;
            }
        }


        if (isset($update->message->text)) {
            if (strstr($update->message->text, 'start')) {
                try {
                    $telegramApi->sendMessage($update->message->chat->id, 'Hi there! I\'m Sticker2Image bot. I\'ll help you to convert your stickers to PNG images. Just send me some sticker.');
                    continue;
                } catch (\Exception $exception) {
                    print_r($exception->getMessage());
                    continue;
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
                $im = imagecreatefromwebp($filePath);
                $imgPath = './img_' . time() . '.png';
                imagepng($im, $imgPath);
                imagedestroy($im);
                $telegramApi->sendPhoto($update->message->chat->id, $imgPath);

                unlink($imgPath);
                continue;
            } catch (\Exception $exception) {
                $telegramApi->sendMessage($update->message->chat->id, 'Sorry, I am tired. Some server error. Try in a few minutes :\'( ');
                continue;
            }
        }
        $telegramApi->sendMessage($update->message->chat->id, 'I understand only stickers');
    }
}