<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'sdfdokln_fleet';
    private $username = 'sdfdokln_admin';
    private $password = ';cX6,?[]dCkL';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public static function generateCompanyId() {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
    }
}
?>
