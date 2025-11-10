<?php
require_once __DIR__ . '/../models/Produto.php';

class ProdutoController {
    // Exibe a lista (aceita ?q=termo)
    public function index() {
        $produtoModel = new Produto();

        // pega termo de busca via GET (ex: ?route=produtos&q=termo)
        $q = null;
        if (isset($_GET['q'])) {
            $q = trim($_GET['q']);
            if ($q === '') $q = null;
        }

        $produtos = $produtoModel->listar($q);
        include __DIR__ . '/../../public/produtos.php';
    }

    // Cria novo produto (espera POST)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // validação simples
            $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
            $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';

            if ($descricao !== '' && $codigo !== '') {
                $produtoModel = new Produto();
                $produtoModel->criar($descricao, $codigo);
            }

            header("Location: ?route=produtos");
            exit;
        }
    }

    // Atualiza produto (espera POST)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
            $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';

            if ($id > 0 && $descricao !== '' && $codigo !== '') {
                $produtoModel = new Produto();
                $produtoModel->atualizar($id, $descricao, $codigo);
            }

            header("Location: ?route=produtos");
            exit;
        }
    }

    // Exclui produto (espera POST com id)
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            if ($id > 0) {
                $produtoModel = new Produto();
                $produtoModel->excluir($id);
            }
            header("Location: ?route=produtos");
            exit;
        }
    }
}
