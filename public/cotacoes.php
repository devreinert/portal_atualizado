<?php
// public/cotacoes.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona para login se não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ?route=login");
    exit;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
          font-family: 'Poppins', sans-serif;
          background: radial-gradient(circle at top left, #1f2933, #050608 55%);
          color: #fff;
          min-height: 100vh;
          margin: 0;
        }
        .sidebar {
          background: linear-gradient(180deg, #10141c, #050608);
          min-height: 100vh;
          width: 260px;
          padding: 30px 20px;
          border-right: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar h4 {
          color: #0d6efd;
          font-weight: 600;
          margin-bottom: 40px;
          display: flex;
          align-items: center;
          gap: 10px;
        }
        .sidebar h4 i {
          font-size: 1.4rem;
        }
        .sidebar a {
          color: #aaa;
          text-decoration: none;
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 10px 0;
          border-radius: 8px;
          padding-left: 4px;
          transition: all 0.2s;
          font-size: 0.95rem;
        }
        .sidebar a i {
          font-size: 1.1rem;
        }
        .sidebar a.active,
        .sidebar a:hover {
          color: #fff;
          background-color: rgba(13,110,253,0.15);
          padding-left: 8px;
        }
        .main-content {
          padding: 40px;
          flex-grow: 1;
        }
        .top-bar {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 30px;
        }
        .top-bar h2 i {
          color: #0d6efd;
          margin-right: 8px;
        }
        .table {
          background-color: #111827;
          border-radius: 14px;
          overflow: hidden;
          border: 1px solid rgba(148,163,184,0.25);
        }
        .table thead th {
          background-color: #020617;
          color: #e5e7eb;
          border-bottom: 1px solid #1f2937;
          font-weight: 500;
          font-size: 0.9rem;
          text-transform: uppercase;
          letter-spacing: 0.04em;
        }
        .table tbody tr {
          transition: background-color 0.15s, transform 0.05s;
        }
        .table tbody tr:hover {
          background-color: #020617;
          transform: translateY(-1px);
        }
        tr.clicavel { cursor:pointer; }
        .badge-status {
          border-radius: 999px;
          padding: 4px 10px;
          font-size: 0.75rem;
        }
        .btn-primary,
        .btn-danger,
        .btn-secondary,
        .btn-success,
        .btn-outline-danger {
          border-radius: 999px;
        }
        .btn-primary {
          background: linear-gradient(135deg, #2563eb, #4f46e5);
          border: none;
        }
        .btn-primary:hover {
          background: linear-gradient(135deg, #1d4ed8, #4338ca);
        }
        .btn-success {
          background: #16a34a;
          border: none;
        }
        .btn-success:hover {
          background: #15803d;
        }
        .btn-outline-danger {
          border-color: #dc2626;
          color: #fecaca;
        }
        .btn-outline-danger:hover {
          background-color: #dc2626;
          color: #fff;
        }
        .modal-content {
          background: #020617;
          color: #e5e7eb;
          border-radius: 18px;
          border: 1px solid rgba(148,163,184,0.25);
        }
        .modal-header {
          border-bottom-color: rgba(31,41,55,0.8);
        }
        .modal-footer {
          border-top-color: rgba(31,41,55,0.8);
        }
        .form-control,
        .form-select {
          background-color: #020617;
          border-color: #1f2937;
          color: #e5e7eb;
          border-radius: 12px;
        }
        .form-control:focus,
        .form-select:focus {
          background-color: #020617;
          border-color: #2563eb;
          color: #fff;
          box-shadow: 0 0 0 0.15rem rgba(37,99,235,0.35);
        }
        .cotacao-row { margin-bottom: 8px; }
        .modal-lg { max-width: 900px; }
        .footer {
          background-color: #020617;
          color: #9ca3af;
          text-align: center;
          padding: 10px 0;
          position: fixed;
          bottom: 0;
          width: 100%;
          border-top: 1px solid rgba(31,41,55,0.9);
          font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="d-flex">
  <aside class="sidebar">
    <h4><i class="bi bi-cart3"></i> Portal de Compras</h4>
    <a href="?route=produtos"><i class="bi bi-box-seam"></i> Produtos</a>
    <a href="?route=fornecedor"><i class="bi bi-building"></i> Fornecedores</a>
    <a href="?route=cotacoes" class="active"><i class="bi bi-receipt-cutoff"></i> Cotações</a>
    <a href="?route=logout"><i class="bi bi-box-arrow-right"></i> Sair</a>
  </aside>

  <main class="main-content">

    <div class="top-bar d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="fw-semibold mb-0">
            <i class="bi bi-receipt-cutoff"></i> Cotações
          </h2>
          <small class="text-muted">Acompanhe o status das cotações e visualize itens rapidamente.</small>
        </div>

        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCotacaoCriar">
            <i class="bi bi-plus-circle"></i> Iniciar cotação
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

    <div class="card border-0" style="background-color:#111827; border-radius:14px;">
        <div class="card-body">
            <?php if (empty($cotacoes)): ?>
                <p class="mb-0">Nenhuma cotação criada.</p>
            <?php else: ?>
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th><i class="bi bi-hash"></i></th>
                            <th><i class="bi bi-building me-1"></i> Fornecedor</th>
                            <th><i class="bi bi-calendar3 me-1"></i> Data</th>
                            <th><i class="bi bi-flag me-1"></i> Status</th>
                            <th><i class="bi bi-box2-heart me-1"></i> Resumo</th>
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
                                        <span class="badge-status bg-danger-subtle text-danger">
                                            <i class="bi bi-x-circle me-1"></i> Cotação cancelada
                                        </span>
                                    <?php elseif (($c['status'] ?? '') === 'encerrada'): ?>
                                        <span class="badge-status bg-success-subtle text-success">
                                            <i class="bi bi-check-circle me-1"></i> Cotação encerrada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-status bg-primary-subtle text-primary">
                                            <i class="bi bi-hourglass-split me-1"></i> Cotação aberta
                                        </span>
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

  </main>
</div>

<!-- Modal CRIAR cotação -->
<div class="modal fade" id="modalCotacaoCriar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="?route=cotacoes">
        <input type="hidden" name="action" value="create">

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-plus-circle me-2"></i> Iniciar Cotação
          </h5>
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

            <h6 class="mb-2"><i class="bi bi-boxes me-1"></i> Produtos</h6>

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
                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <button type="button" class="btn btn-secondary btn-sm mt-2 d-flex align-items-center gap-1" id="addProduto">
                <i class="bi bi-plus-circle"></i> Adicionar produto
            </button>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send-check"></i> Enviar Cotação
          </button>
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
                    <i class="bi bi-receipt me-2"></i>
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
                        <span class="badge-status bg-danger-subtle text-danger">
                            <i class="bi bi-x-circle me-1"></i> Cotação cancelada
                        </span>
                    <?php elseif (($c['status'] ?? '') === 'encerrada'): ?>
                        <span class="badge-status bg-success-subtle text-success">
                            <i class="bi bi-check-circle me-1"></i> Cotação encerrada
                        </span>
                    <?php else: ?>
                        <span class="badge-status bg-primary-subtle text-primary">
                            <i class="bi bi-hourglass-split me-1"></i> Cotação aberta
                        </span>
                    <?php endif; ?>
                </p>

                <hr>

                <h6 class="mb-2"><i class="bi bi-box-seam me-1"></i> Produtos desta cotação</h6>

                <table class="table table-sm table-dark align-middle mb-0">
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
                    <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-1">
                        <i class="bi bi-x-octagon"></i> Cancelar cotação
                    </button>
                </form>

                <!-- Confirmar cotação -->
                <form method="POST" action="?route=cotacoes">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($idCot) ?>">
                    <input type="hidden" name="status" value="encerrada">
                    <button type="submit" class="btn btn-success d-flex align-items-center gap-1">
                        <i class="bi bi-check2-circle"></i> Confirmar cotação
                    </button>
                </form>
              </div>

            </div>
          </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<footer class="footer">
  <p>© 2025 Portal de Compras - Todos os direitos reservados</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
