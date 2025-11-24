<?php
// controllers/CotacaoController.php

// Garante que a conexão e o model estejam disponíveis.
// Ajuste os caminhos se a sua estrutura for diferente.
require_once __DIR__ . '/../models/Cotacao.php';
require_once __DIR__ . '/../../config/database.php'; // necessário para Database::connect() se precisar de queries adicionais

class CotacaoController {
    private $model;
    private $conn;

    public function __construct() {
        // Model utiliza Database::connect() internamente
        $this->model = new Cotacao();

        // Também usamos a conexão direta para consultas auxiliares (fornecedores / produtos)
        // Database::connect() deve ser definido em config/database.php conforme seu model.
        $this->conn = Database::connect();
    }

    /**
     * Exibe a listagem e prepara dados para a view (fornecedores e produtos)
     */
    public function index() {
        // Busca todas as cotações via model
        $cotacoes = $this->model->all();

        // Busca fornecedores e produtos para popular o modal/form
        $fornecedores = [];
        $produtos = [];

        try {
            $fornecedores = $this->conn->query("SELECT id, nome FROM fornecedores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Em caso de erro, mantemos array vazio (view mostrará mensagem)
            $fornecedores = [];
        }

        try {
            $produtos = $this->conn->query("SELECT id, nome FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $produtos = [];
        }

        // Inclui a view (ajuste o caminho conforme sua organização de views)
        include __DIR__ . '/../views/cotacoes/index.php';
    }

    /**
     * Recebe o POST do formulário e cria a cotação com itens
     */
    public function store() {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método não permitido';
            exit;
        }

        // Sanitização/validação básica
        $fornecedor_id = isset($_POST['fornecedor_id']) ? (int) $_POST['fornecedor_id'] : 0;
        $produto_ids   = isset($_POST['produto_id']) ? $_POST['produto_id'] : [];
        $quantidades   = isset($_POST['quantidade']) ? $_POST['quantidade'] : [];

        $itens = [];
        $countProdutos = max(count($produto_ids), count($quantidades));

        for ($i = 0; $i < $countProdutos; $i++) {
            $pid = isset($produto_ids[$i]) ? (int) $produto_ids[$i] : 0;
            $qtd = isset($quantidades[$i]) ? (int) $quantidades[$i] : 0;

            if ($pid > 0 && $qtd > 0) {
                $itens[] = [
                    'produto_id' => $pid,
                    'quantidade' => $qtd
                ];
            }
        }

        // Validações mínimas
        if ($fornecedor_id <= 0) {
            $_SESSION['flash_error'] = 'Selecione um fornecedor válido.';
            $this->redirectBack();
        }

        if (empty($itens)) {
            $_SESSION['flash_error'] = 'Adicione ao menos um produto com quantidade válida.';
            $this->redirectBack();
        }

        // Tenta criar a cotação via model
        try {
            $cotacao_id = $this->model->create($fornecedor_id, $itens);
            $_SESSION['flash_success'] = 'Cotação criada com sucesso (ID: ' . htmlspecialchars($cotacao_id) . ').';
            // Redireciona para a lista principal de cotações
            $this->redirectToIndex();
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar cotação: ' . $e->getMessage();
            $this->redirectBack();
        }
    }

    /**
     * Redireciona de volta para a página que chamou (referer), com fallback para a index das cotações
     */
    private function redirectBack() {
        // Tenta redirecionar para o referer; se não existir, vai para a index padrão
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if ($referer) {
            header('Location: ' . $referer);
            exit;
        }
        $this->redirectToIndex();
    }

    /**
     * Redireciona para a página pública de cotações.
     * Ajuste se o seu roteamento usar caminho diferente.
     */
    private function redirectToIndex() {
        // Se você tem um arquivo público /public/cotacoes.php use algo parecido abaixo:
        // header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/cotacoes.php');
        // Porém, para maior compatibilidade com várias estruturas, tente um redirecionamento relativo:
        // Caso necessário, modifique este caminho conforme sua estrutura de pastas.
        header('Location: ' . (isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) . '/cotacoes.php' : 'cotacoes.php'));
        exit;
    }
}
