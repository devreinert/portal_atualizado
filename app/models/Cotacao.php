<?php
require_once __DIR__ . '/../../config/database.php';

class Cotacao {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * Retorna todas as cotações cadastradas
     */
    public function all() {
        $stmt = $this->conn->prepare("
            SELECT c.*,
                   f.nome_empresa AS fornecedor_nome
            FROM cotacoes c
            LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
            ORDER BY c.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna uma cotação por id
     */
    public function find($id) {
        $stmt = $this->conn->prepare("
            SELECT c.*,
                   f.nome_empresa AS fornecedor_nome
            FROM cotacoes c
            LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insere uma nova cotação compatível com o schema atual (fornecedor_id, criado_em)
     * Se você tiver outras colunas (ex: usuario_id, observacoes), ajuste aqui.
     */
    public function store($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO cotacoes (fornecedor_id, criado_em)
            VALUES (?, NOW())
        ");
        return $stmt->execute([
            $data['fornecedor_id'] ?? null
        ]);
    }

    /**
     * Retorna os itens de uma cotação
     */
    public function itens($cotacao_id) {
        $stmt = $this->conn->prepare("
            SELECT ci.id,
                   ci.produto_id,
                   p.descricao AS produto_nome,
                   ci.quantidade
            FROM cotacao_itens ci
            LEFT JOIN produtos p ON p.id = ci.produto_id
            WHERE ci.cotacao_id = ?
        ");

        $stmt->execute([$cotacao_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
