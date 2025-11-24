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
        $sql = "
            SELECT c.id,
                   c.fornecedor_id,
                   c.criado_em,
                   f.nome AS fornecedor_nome
            FROM cotacoes c
            LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
            ORDER BY c.criado_em DESC
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna uma cotação específica
     */
    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM cotacoes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria uma cotação com seus itens
     */
    public function create($fornecedor_id, $itens) {
        try {
            $this->conn->beginTransaction();

            // Insere a cotação
            $stmt = $this->conn->prepare("INSERT INTO cotacoes (fornecedor_id) VALUES (?)");
            $stmt->execute([$fornecedor_id]);

            $cotacao_id = $this->conn->lastInsertId();

            // Insere os itens da cotação
            $stmtItem = $this->conn->prepare("
                INSERT INTO cotacao_itens (cotacao_id, produto_id, quantidade)
                VALUES (?,?,?)
            ");

            foreach ($itens as $item) {
                $produto_id  = (int) $item['produto_id'];
                $quantidade  = (int) $item['quantidade'];

                if ($produto_id <= 0 || $quantidade <= 0) {
                    continue; // ignora itens inválidos
                }

                $stmtItem->execute([$cotacao_id, $produto_id, $quantidade]);
            }

            $this->conn->commit();
            return $cotacao_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Retorna itens de uma cotação
     */
    public function itens($cotacao_id) {
        $stmt = $this->conn->prepare("
            SELECT ci.id,
                   ci.produto_id,
                   p.nome AS produto_nome,
                   ci.quantidade
            FROM cotacao_itens ci
            LEFT JOIN produtos p ON p.id = ci.produto_id
            WHERE ci.cotacao_id = ?
        ");

        $stmt->execute([$cotacao_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
