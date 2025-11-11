<?php
require_once __DIR__ . '/../models/Fornecedor.php';

class FornecedorController {
    public function index() {
        $fornecedorModel = new Fornecedor();
        $q = null;
        if (isset($_GET['q'])) {
            $q = trim($_GET['q']);
            if ($q === '') $q = null;
        }
        $fornecedores = $fornecedorModel->listar($q);
        include __DIR__ . '/../../public/fornecedor.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = isset($_POST['nome_empresa']) ? trim($_POST['nome_empresa']) : '';
            $cnpj = isset($_POST['cnpj']) ? trim($_POST['cnpj']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $contato = isset($_POST['contato']) ? trim($_POST['contato']) : '';
            $segmento = isset($_POST['segmento']) ? trim($_POST['segmento']) : '';

            if ($nome !== '' && $cnpj !== '') {
                $model = new Fornecedor();
                $model->criar($nome, $cnpj, $email, $contato, $segmento);
            }
            header("Location: ?route=fornecedor");
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $nome = isset($_POST['nome_empresa']) ? trim($_POST['nome_empresa']) : '';
            $cnpj = isset($_POST['cnpj']) ? trim($_POST['cnpj']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $contato = isset($_POST['contato']) ? trim($_POST['contato']) : '';
            $segmento = isset($_POST['segmento']) ? trim($_POST['segmento']) : '';

            if ($id > 0 && $nome !== '') {
                $model = new Fornecedor();
                $model->atualizar($id, $nome, $cnpj, $email, $contato, $segmento);
            }
            header("Location: ?route=fornecedor");
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            if ($id > 0) {
                $model = new Fornecedor();
                $model->excluir($id);
            }
            header("Location: ?route=fornecedor");
            exit;
        }
    }
}
?>
