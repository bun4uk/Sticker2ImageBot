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

$config = parse_ini_file('/var/www/html/config/config.ini');
$token = $config['telegram_api_token'];
$log = new Logger('img_log');
$telegramApi = new TelegramBot($token, $log);

$date = (new \DateTime('yesterday'))->format('Y-m-d');
exec('cat ' . __DIR__ . "/logs/img_log.log | grep === | grep {$date} | wc -l", $result);
$telegramApi->sendMessage(7699150, 'Вчера бот был использован ' . reset($result) . ' раз(а)');
return true;

