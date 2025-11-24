<?php
require_once __DIR__ . '/../models/Cotacao.php';
require_once __DIR__ . '/LoginController.php';
require_once __DIR__ . '/../../config/database.php';

class CotacaoController {
    private $model;

    public function __construct() {
        $this->model = new Cotacao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Lista todas as cotações
    public function index() {
        $cotacoes = $this->model->all();

        // carrega fornecedores (opcional)
        $db = Database::connect();
        $fornecedores = $db->query("SELECT id, nome FROM fornecedores ORDER BY nome")
                           ->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/cotacoes/index.php';
    }

    // Mostra formulário de criação
    public function create() {
        $db = Database::connect();
        $fornecedores = $db->query("SELECT id, nome FROM fornecedores ORDER BY nome")
                           ->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/cotacoes/create.php';
    }

    // Salva nova cotação
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cotacoes');
            exit;
        }

        $data = [
            'fornecedor_id' => $_POST['fornecedor_id'] ?? null,
            'usuario_id'    => $_SESSION['user_id'] ?? null,
            'data_cotacao'  => $_POST['data_cotacao'] ?? date('Y-m-d'),
            'observacoes'   => $_POST['observacoes'] ?? null
        ];

        $ok = $this->model->store($data);

        if ($ok) {
            $_SESSION['flash_success'] = 'Cotação criada com sucesso.';
        } else {
            $_SESSION['flash_error'] = 'Erro ao criar cotação.';
        }

        header('Location: /cotacoes');
        exit;
    }

    // Exibe detalhes da cotação
    public function show($id) {
        $cotacao = $this->model->find($id);
        if (!$cotacao) {
            header('Location: /cotacoes');
            exit;
        }

        $itens = $this->model->itens($id);

        require_once __DIR__ . '/../views/cotacoes/show.php';
    }

    // Excluir cotação e seus itens
    public function delete($id) {
        $db = Database::connect();

        try {
            $db->beginTransaction();

            // Remove itens vinculados
            $stmt = $db->prepare("DELETE FROM cotacao_itens WHERE cotacao_id = ?");
            $stmt->execute([$id]);

            // Remove cotação
            $stmt2 = $db->prepare("DELETE FROM cotacoes WHERE id = ?");
            $stmt2->execute([$id]);

            $db->commit();
            $_SESSION['flash_success'] = 'Cotação removida.';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Erro ao remover: ' . $e->getMessage();
        }

        header('Location: /cotacoes');
        exit;
    }
}
?>
