<?php
require_once __DIR__ . '/../../config/database.php';

class Fornecedor {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function listar($q = null) {
        if ($q === null || trim($q) === '') {
            $stmt = $this->conn->query("SELECT * FROM fornecedores ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $term = '%' . $q . '%';
            $stmt = $this->conn->prepare("
                SELECT * FROM fornecedores
                WHERE nome_empresa LIKE ? OR cnpj LIKE ? OR email LIKE ? OR contato LIKE ?
                ORDER BY id DESC
            ");
            $stmt->execute([$term, $term, $term, $term]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function criar($nome_empresa, $cnpj, $email, $contato, $segmento) {
        $stmt = $this->conn->prepare("INSERT INTO fornecedores (nome_empresa, cnpj, email, contato, segmento) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nome_empresa, $cnpj, $email, $contato, $segmento]);
    }

    public function atualizar($id, $nome_empresa, $cnpj, $email, $contato, $segmento) {
        $stmt = $this->conn->prepare("UPDATE fornecedores SET nome_empresa = ?, cnpj = ?, email = ?, contato = ?, segmento = ? WHERE id = ?");
        return $stmt->execute([$nome_empresa, $cnpj, $email, $contato, $segmento, $id]);
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM fornecedores WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM fornecedores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
