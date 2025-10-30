<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function register($email, $senha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (email, senha) VALUES (?, ?)");
        return $stmt->execute([$email, $hash]);
    }

    public function login($email, $senha) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            return $user;
        }
        return false;
    }
}
?>