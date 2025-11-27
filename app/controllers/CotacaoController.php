<?php
require_once __DIR__ . '/../models/Cotacao.php';
require_once __DIR__ . '/../../config/database.php';

class CotacaoController {
    public $model;

    public function __construct() {
        $this->model = new Cotacao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Lista todas as cotações e carrega dados auxiliares para a view
     */
    public function index() {
        // pega todas as cotações via model
        $cotacoes = $this->model->all();

        // monta array de itens de cada cotação (para os modais)
        $itensPorCotacao = [];
        foreach ($cotacoes as $c) {
            $itensPorCotacao[$c['id']] = $this->model->itens($c['id']);
        }

        // conecta para buscar fornecedores e produtos (aliasando colunas para 'nome')
        $db = Database::connect();

        // fornecedores: nome_empresa -> nome (para compatibilidade com views)
        $fornecedores = $db->query("SELECT id, nome_empresa AS nome FROM fornecedores ORDER BY nome_empresa")
                           ->fetchAll(PDO::FETCH_ASSOC);

        // produtos: descricao -> nome
        $produtos = $db->query("SELECT id, descricao AS nome FROM produtos ORDER BY descricao")
                       ->fetchAll(PDO::FETCH_ASSOC);

        // disponibiliza variáveis para a view (index.php)
        // $cotacoes, $itensPorCotacao, $fornecedores, $produtos
        require_once __DIR__ . '/../../public/cotacoes.php';
    }

    /**
     * Mostra formulário de criação (se usado como rota separada)
     */
    public function create() {
        $db = Database::connect();
        $fornecedores = $db->query("SELECT id, nome_empresa AS nome FROM fornecedores ORDER BY nome_empresa")
                           ->fetchAll(PDO::FETCH_ASSOC);
        $produtos = $db->query("SELECT id, descricao AS nome FROM produtos ORDER BY descricao")
                       ->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/cotacoes/create.php';
    }

    /**
     * Salva uma nova cotação e seus itens (se informados)
     * Espera POST com:
     *  - fornecedor_id
     *  - produto_id[] (array)
     *  - quantidade[] (array)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?route=cotacoes');
            exit;
        }

        $fornecedorId = $_POST['fornecedor_id'] ?? null;
        $produtos = $_POST['produto_id'] ?? [];
        $qtds = $_POST['quantidade'] ?? [];

        if (empty($fornecedorId)) {
            $_SESSION['flash_error'] = 'Fornecedor obrigatório.';
            header('Location: ?route=cotacoes');
            exit;
        }

        $db = Database::connect();

        try {
            $db->beginTransaction();

            // status inicial = 'aberta'
            $stmt = $db->prepare(
                "INSERT INTO cotacoes (fornecedor_id, status, criado_em) VALUES (?, 'aberta', NOW())"
            );
            $stmt->execute([$fornecedorId]);

            $cotacaoId = $db->lastInsertId();

            if (!empty($produtos) && is_array($produtos)) {
                $stmtItem = $db->prepare(
                    "INSERT INTO cotacao_itens (cotacao_id, produto_id, quantidade) VALUES (?, ?, ?)"
                );
                $count = max(count($produtos), count($qtds));
                for ($i = 0; $i < $count; $i++) {
                    $pid = $produtos[$i] ?? null;
                    $qt  = $qtds[$i] ?? 0;

                    if (!empty($pid) && (int)$qt > 0) {
                        $stmtItem->execute([$cotacaoId, $pid, (int)$qt]);
                    }
                }
            }

            $db->commit();
            $_SESSION['flash_success'] = 'Cotação criada com sucesso.';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Erro ao criar cotação: ' . $e->getMessage();
        }

        header('Location: ?route=cotacoes');
        exit;
    }

    /**
     * Exibe detalhes de uma cotação (se quiser usar em página separada)
     */
    public function show($id) {
        $cotacao = $this->model->find($id);
        if (!$cotacao) {
            $_SESSION['flash_error'] = 'Cotação não encontrada.';
            header('Location: ?route=cotacoes');
            exit;
        }

        $itens = $this->model->itens($id);

        require_once __DIR__ . '/../views/cotacoes/show.php';
    }

    /**
     * Atualiza o status da cotação:
     *  - cancelada  -> "Cotação cancelada"
     *  - encerrada  -> "Cotação encerrada"
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?route=cotacoes');
            exit;
        }

        $id     = $_POST['id']     ?? null;
        $status = $_POST['status'] ?? null;

        if (empty($id) || !in_array($status, ['cancelada', 'encerrada'])) {
            $_SESSION['flash_error'] = 'Dados inválidos para atualizar cotação.';
            header('Location: ?route=cotacoes');
            exit;
        }

        $db = Database::connect();

        try {
            $stmt = $db->prepare("UPDATE cotacoes SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            if ($status === 'cancelada') {
                $_SESSION['flash_success'] = 'Cotação cancelada.';
            } elseif ($status === 'encerrada') {
                $_SESSION['flash_success'] = 'Cotação encerrada.';
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar cotação: ' . $e->getMessage();
        }

        header('Location: ?route=cotacoes');
        exit;
    }

    /**
     * Remove cotação e seus itens (se existir)
     * (se ainda quiser manter a exclusão física)
     */
    public function delete($id) {
        $db = Database::connect();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("DELETE FROM cotacao_itens WHERE cotacao_id = ?");
            $stmt->execute([$id]);

            $stmt2 = $db->prepare("DELETE FROM cotacoes WHERE id = ?");
            $stmt2->execute([$id]);

            $db->commit();
            $_SESSION['flash_success'] = 'Cotação removida.';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Erro ao remover cotação: ' . $e->getMessage();
        }

        header('Location: ?route=cotacoes');
        exit;
    }
}
?>
