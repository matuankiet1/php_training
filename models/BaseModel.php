<?php

class BaseModel {

    // Kết nối PDO dùng chung cho tất cả các model
    protected static $_connection = null;

    public function __construct() {
        if (self::$_connection === null) {
            try {
                // Cấu hình kết nối DB
                $host = 'localhost';
                $db   = 'app_web1';
                $user = 'root';
                $pass = '';
                $charset = 'utf8mb4';

                $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // Bắt buộc dùng prepared statements thật
                ];

                self::$_connection = new PDO($dsn, $user, $pass, $options);

            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Chạy câu lệnh SELECT trả về nhiều dòng
     */
    protected function select($sql, $params = []) {
        $stmt = self::$_connection->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Chạy câu lệnh SELECT trả về 1 dòng
     */
    protected function selectOne($sql, $params = []) {
        $stmt = self::$_connection->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Chạy câu lệnh INSERT
     */
    protected function insert($sql, $params = []) {
        $stmt = self::$_connection->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return self::$_connection->lastInsertId();
    }

    /**
     * Chạy câu lệnh UPDATE
     */
    protected function update($sql, $params = []) {
        $stmt = self::$_connection->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        return $stmt->execute();
    }

    /**
     * Chạy câu lệnh DELETE
     */
    protected function delete($sql, $params = []) {
        $stmt = self::$_connection->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        return $stmt->execute();
    }

    /**
     * Chạy câu lệnh tùy ý (nếu cần)
     */
    protected function query($sql, $params = []) {
        $stmt = self::$_connection->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt;
    }
}
