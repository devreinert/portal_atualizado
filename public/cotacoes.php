<?php
// public/cotacoes.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Aqui NÃO instanciamos controller nem chamamos store/index,
// isso já foi feito pelo routes/web.php + CotacaoController::index()
// e as variáveis abaixo DEVEM vir do controller:
// $cotacoes, $itensPorCotacao, $fornecedores, $produtos
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
        tr.clicavel { cursor:pointer; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Cotações</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCotacaoCriar">
            Iniciar cotação
        </button>
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
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fornecedor</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Resumo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cotacoes as $c): ?>
                            <?php
                                $idCot = $c['id'];
                                $itens = $itensPorCotacao[$idCot] ?? [];
                            ?>
                            <tr class="clicavel"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCotacaoDetalhe<?= $idCot ?>">
                                <td><?= htmlspecialchars($idCot) ?></td>
                                <td><?= htmlspecialchars($c['fornecedor_nome'] ?? ($c['nome'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars($c['criado_em'] ?? ($c['data_cotacao'] ?? '')) ?></td>
                                <td>
                                    <?php if (($c['status'] ?? '') === 'cancelada'): ?>
                                        <span class="badge bg-danger">Cotação cancelada</span>
                                    <?php elseif (($c['status'] ?? '') === 'encerrada'): ?>
                                        <span class="badge bg-success">Cotação encerrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Cotação aberta</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        if (!empty($itens)) {
                                            $primeiro = $itens[0];
                                            $nomeProd = $primeiro['produto_nome']
                                                ?? ($primeiro['nome'] ?? ($primeiro['descricao'] ?? '-'));
                                            $qtd = (int)($primeiro['quantidade'] ?? 0);
                                            echo htmlspecialchars($nomeProd) . " x " . $qtd;
                                            if (count($itens) > 1) {
                                                echo " + " . (count($itens) - 1) . " item(ns)";
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

<!-- Modal CRIAR cotação -->
<div class="modal fade" id="modalCotacaoCriar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="?route=cotacoes">
        <input type="hidden" name="action" value="create">

        <div class="modal-header">
          <h5 class="modal-title">Iniciar Cotação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <label class="form-label">Fornecedor</label>
            <select name="fornecedor_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= htmlspecialchars($f['id']) ?>">
                        <?= htmlspecialchars($f['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <hr>

            <h6>Produtos</h6>

            <div id="itensWrapper">

                <div class="cotacao-row">
                    <div class="row g-2">
                        <div class="col-7">
                            <select name="produto_id[]" class="form-select" required>
                                <option value="">Selecione o produto...</option>
                                <?php foreach ($produtos as $p): ?>
                                    <option value="<?= htmlspecialchars($p['id']) ?>">
                                        <?= htmlspecialchars($p['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-3">
                            <input type="number" name="quantidade[]" min="1" class="form-control"
                                   placeholder="Qtd" required>
                        </div>
                        <div class="col-2 d-flex align-items-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                        </div>
                    </div>
                </div>

            </div>

            <button type="button" class="btn btn-secondary btn-sm mt-2" id="addProduto">
                Adicionar produto
            </button>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enviar Cotação</button>
        </div>

      </form>
    </div>
  </div>
</div>

<?php if (!empty($cotacoes)): ?>
    <?php foreach ($cotacoes as $c): ?>
        <?php
            $idCot = $c['id'];
            $itens = $itensPorCotacao[$idCot] ?? [];
        ?>
        <!-- Modal DETALHE da cotação -->
        <div class="modal fade" id="modalCotacaoDetalhe<?= $idCot ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">
                    Cotação #<?= htmlspecialchars($idCot) ?> -
                    <?= htmlspecialchars($c['fornecedor_nome'] ?? ($c['nome'] ?? '-')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <p>
                    <strong>Data:</strong>
                    <?= htmlspecialchars($c['criado_em'] ?? ($c['data_cotacao'] ?? '')) ?>
                </p>

                <p>
                    <strong>Status atual:</strong>
                    <?php if (($c['status'] ?? '') === 'cancelada'): ?>
                        <span class="badge bg-danger">Cotação cancelada</span>
                    <?php elseif (($c['status'] ?? '') === 'encerrada'): ?>
                        <span class="badge bg-success">Cotação encerrada</span>
                    <?php else: ?>
                        <span class="badge bg-primary">Cotação aberta</span>
                    <?php endif; ?>
                </p>

                <hr>

                <h6>Produtos desta cotação</h6>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($itens)): ?>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $item['produto_nome']
                                            ?? ($item['nome'] ?? ($item['descricao'] ?? '-'))
                                        ) ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['quantidade'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2">Nenhum item encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
              </div>

              <div class="modal-footer">
                <!-- Cancelar cotação -->
                <form method="POST" action="?route=cotacoes" class="me-auto">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($idCot) ?>">
                    <input type="hidden" name="status" value="cancelada">
                    <button type="submit" class="btn btn-outline-danger">
                        Cancelar cotação
                    </button>
                </form>

                <!-- Confirmar cotação -->
                <form method="POST" action="?route=cotacoes">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($idCot) ?>">
                    <input type="hidden" name="status" value="encerrada">
                    <button type="submit" class="btn btn-success">
                        Confirmar cotação
                    </button>
                </form>
              </div>

            </div>
          </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// adicionar linhas de produto
document.getElementById("addProduto").addEventListener("click", function () {
    const wrapper = document.getElementById("itensWrapper");
    const row = wrapper.firstElementChild.cloneNode(true);

    const sel = row.querySelector("select");
    if (sel) sel.selectedIndex = 0;

    const input = row.querySelector("input[type='number']");
    if (input) input.value = "";

    const btn = row.querySelector(".remove-row");
    if (btn) {
        btn.addEventListener("click", function () {
            const rows = document.querySelectorAll(".cotacao-row");
            if (rows.length > 1) this.closest(".cotacao-row").remove();
        });
    }

    wrapper.appendChild(row);
});

// remover linha (inicial + clonadas)
document.querySelectorAll(".remove-row").forEach(function(btn){
    btn.addEventListener("click", function () {
        const rows = document.querySelectorAll(".cotacao-row");
        if (rows.length > 1) this.closest(".cotacao-row").remove();
    });
});
</script>

</body>
</html>
