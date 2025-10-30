<?php
require_once __DIR__ . '/../models/Produto.php';

class ProdutoController {
    public function index() {
        $produtoModel = new Produto();
        $produtos = $produtoModel->listar();
        include __DIR__ . '/../../public/produtos.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $descricao = $_POST['descricao'];
            $codigo = $_POST['codigo'];

            $produtoModel = new Produto();
            $produtoModel->criar($descricao, $codigo);

            header("Location: ?route=produtos");
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $descricao = $_POST['descricao'];
            $codigo = $_POST['codigo'];

            $produtoModel = new Produto();
            $produtoModel->atualizar($id, $descricao, $codigo);

            header("Location: ?route=produtos");
            exit;
        }
    }

    public function delete() {
        if (isset($_POST['id'])) {
            $produtoModel = new Produto();
            $produtoModel->excluir($_POST['id']);
            header("Location: ?route=produtos");
            exit;
        }
    }
}
