<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/16/18
 * Time: 1:57 PM
 */

use GuzzleHttp\Client;
use Monolog\Logger;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class TelegramBot
 */
class TelegramBot
{
    /**
     * TelegramBot constructor.
     * @param string $token
     * @param Logger $log
     */
    public function __construct(string $token, Logger $log)
    {
        $this->token = $token;
        $this->log = $log;
    }

    /**
     * @const TELEGRAM_API_URL
     */
    const TELEGRAM_API_URL = 'https://api.telegram.org/bot';

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @param string $method
     * @param array $params
     * @return stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function query(string $method, array $params = []): stdClass
    {
        $url = self::TELEGRAM_API_URL . $this->token . '/' . $method;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        print_r($url);
        echo "\n";
        $client = new Client(['base_uri' => $url]);
        $response = $client->request('GET');

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @return stdClass
     */
    public function getUpdates(): stdClass
    {
        $response = [];
        try {
            $response = $this->query('getUpdates', [
                'offset' => $this->offset + 1
            ]);
            if (!empty($response->result)) {
                $this->offset = $response->result[count($response->result) - 1]->update_id;
            }
        } catch (GuzzleException $exception) {
            $this->log->log(400, 'Guzzle sendMessage error');
        }

        return $response;
    }

    /**
     * @param int $chat_id
     * @param string $text
     * @return stdClass
     */
    public function sendMessage(int $chat_id = 0, string $text): stdClass
    {
        try {
            $response = $this->query('sendMessage', [
                'text' => $text,
                'chat_id' => $chat_id
            ]);
        } catch (GuzzleException $exception) {
            $this->log->log(400, 'Guzzle sendMessage error');
        }

        return $response;
    }

    public function getFile($file): stdClass
    {
        $response = $this->query('getFile', [
            'file_id' => $file->file_id
        ]);

        return $response->result;
    }

    public function sendPhoto(int $chatId, string $photo)
    {
        $url = self::TELEGRAM_API_URL . $this->token . '/sendPhoto?chat_id=' . $chatId;

        $post_fields = [
            'chat_id' => $chatId,
            'photo' => new CURLFile(realpath($photo))
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:multipart/form-data"
        ]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        return curl_exec($ch);
    }

}