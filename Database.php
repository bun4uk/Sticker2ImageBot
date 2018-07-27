<?php

/**
 * Class Database
 */
class Database
{
    private $db;

    /**
     * Database constructor.
     */
    function __construct(array $dbConfig)
    {
        try {
            $this->db = new PDO(
                "mysql:host={$dbConfig['db_host']};dbname={$dbConfig['db_name']}",
                $dbConfig['db_user'],
                $dbConfig['db_password']
            );
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * @param array $userInfo
     */
    public function saveUser(array $userInfo): void
    {
        $stmt = $this->db->prepare("INSERT INTO users (chat_id, username) VALUES (:chat_id, :username)");
        $stmt->bindParam(':chat_id', $userInfo['chat_id']);
        $stmt->bindParam(':username', $userInfo['username']);
        $stmt->execute();
    }

    /**
     * @param array $actionInfo
     */
    public function saveAction(array $actionInfo): void
    {
        $date = (new \DateTime())->format('Y-m-d H-i-s');
        $stmt = $this->db->prepare(
            "INSERT 
                      INTO 
                        actions (chat_id, set_name, file_id, file_path) 
                      VALUES (:chat_id, :set_name, :file_id, :file_path);
                      UPDATE users SET last_action=:last_action WHERE chat_id=:chat_id"
        );
        $stmt->bindParam(':chat_id', $actionInfo['chat_id']);
        $stmt->bindParam(':set_name', $actionInfo['set_name']);
        $stmt->bindParam(':file_id', $actionInfo['file_id']);
        $stmt->bindParam(':file_path', $actionInfo['file_path']);
        $stmt->bindParam(':last_action', $date);
        $stmt->execute();
    }

    /**
     * @param int $chatId
     * @return bool
     */
    public function userExists(int $chatId): bool
    {
        $stmt = $this->db->prepare('SELECT * FROM users where chat_id=:chat_id');
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            return true;
        }

        return false;

    }
}