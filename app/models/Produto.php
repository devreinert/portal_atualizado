<?php
require_once __DIR__ . '/../../config/database.php';

class Produto {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function listar() {
        $stmt = $this->conn->query("SELECT * FROM produtos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar($descricao, $codigo) {
        $stmt = $this->conn->prepare("INSERT INTO produtos (descricao, codigo) VALUES (?, ?)");
        return $stmt->execute([$descricao, $codigo]);
    }

    public function atualizar($id, $descricao, $codigo) {
        $stmt = $this->conn->prepare("UPDATE produtos SET descricao = ?, codigo = ? WHERE id = ?");
        return $stmt->execute([$descricao, $codigo, $id]);
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
