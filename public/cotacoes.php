<?php
// public/cotacoes.php

// Evita chamar session_start() novamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carrega o controller (que por sua vez carrega o model)
require_once __DIR__ . '/../app/controllers/CotacaoController.php';

// Instancia controller
$controller = new CotacaoController();

// Se a página for usada para salvar via query string action=store ou via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'store')) {
    $controller->store();
    exit;
}

// --- BUSCA DADOS PARA A VIEW ---
// Use a propriedade pública model do controller (não $controller->$model)
$cotacoes = $controller->model->all();

// Para fornecedores/produtos usamos Database::connect() e aliasamos as colunas
require_once __DIR__ . '/../app/models/Cotacao.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::connect();

// Fornecedores: seu schema tem 'nome_empresa' -> alias para 'nome' (compatibilidade com views)
$fornecedores = $db->query("SELECT id, nome_empresa AS nome FROM fornecedores ORDER BY nome_empresa")
                   ->fetchAll(PDO::FETCH_ASSOC);

// Produtos: seu schema tem 'descricao' -> alias para 'nome'
$produtos = $db->query("SELECT id, descricao AS nome FROM produtos ORDER BY descricao")
               ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cotações</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .cotacao-row { margin-bottom: 8px; }
        .modal-lg { max-width: 900px; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Cotações</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCotacao">Iniciar cotação</button>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($cotacoes)): ?>
                <p>Nenhuma cotação criada.</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fornecedor</th>
                            <th>Data</th>
                            <th>Itens</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cotacoes as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['id']) ?></td>
                                <td><?= htmlspecialchars($c['fornecedor_nome'] ?? ($c['nome'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars($c['criado_em'] ?? $c['data_cotacao'] ?? '') ?></td>
                                <td>
                                    <?php
                                        $itens = $controller->model->itens($c['id']);
                                        if (!empty($itens)) {
                                            foreach ($itens as $item) {
                                                echo htmlspecialchars(($item['produto_nome'] ?? $item['nome'] ?? $item['descricao'] ?? '-')) . " x " . (int)$item['quantidade'] . "<br>";
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal criar cotação -->
<div class="modal fade" id="modalCotacao" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="cotacoes.php?action=store">

        <div class="modal-header">
          <h5 class="modal-title">Iniciar Cotação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <label class="form-label">Fornecedor</label>
            <select name="fornecedor_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars($f['nome']) ?></option>
                <?php endforeach; ?>
            </select>

            <hr>

            <h6>Produtos</h6>

            <div id="itensWrapper">

                <div class="cotacao-row">
                    <div class="row">
                        <div class="col-7">
                            <select name="produto_id[]" class="form-select" required>
                                <option value="">Selecione o produto...</option>
                                <?php foreach ($produtos as $p): ?>
                                    <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-3">
                            <input type="number" name="quantidade[]" min="1" class="form-control" placeholder="Qtd" required>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                        </div>
                    </div>
                </div>

            </div>

            <button type="button" class="btn btn-secondary btn-sm mt-2" id="addProduto">Adicionar produto</button>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enviar Cotação</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// adicionar linhas
document.getElementById("addProduto").addEventListener("click", function () {
    const wrapper = document.getElementById("itensWrapper");
    const row = wrapper.firstElementChild.cloneNode(true);

    // reset values safely
    const sel = row.querySelector("select");
    if (sel) sel.selectedIndex = 0;
    const input = row.querySelector("input[type='number']");
    if (input) input.value = "";

    // rebind remover
    const btn = row.querySelector(".remove-row");
    if (btn) {
        btn.addEventListener("click", function () {
            const rows = document.querySelectorAll(".cotacao-row");
            if (rows.length > 1) this.closest(".cotacao-row").remove();
        });
    }

    wrapper.appendChild(row);
});

// bind remover para a linha inicial
document.querySelectorAll(".remove-row").forEach(function(btn){
    btn.addEventListener("click", function () {
        const rows = document.querySelectorAll(".cotacao-row");
        if (rows.length > 1) this.closest(".cotacao-row").remove();
    });
});
</script>

</body>
</html>

