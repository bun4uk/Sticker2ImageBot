<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/16/18
 * Time: 1:55 PM
 */

include 'vendor/autoload.php';
include 'Dictionary.php';
include 'TelegramBot.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$token = trim(file_get_contents('./config/sticker2img'));
$log = new Logger('img_log');
$telegramApi = new TelegramBot($token, $log);


$date = (new \DateTime())->format('Y-m-d');

exec("cat logs/img_log.log | grep === | grep {$date} | wc -l", $result);
$telegramApi->sendMessage(7699150, reset($result));
return true;

