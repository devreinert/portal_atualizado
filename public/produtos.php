<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Produto.php';

// Redireciona para login se não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ?route=login");
    exit;
}

// variável $produtos deve ser fornecida pelo controller (index)
if (!isset($produtos)) {
    $produtoModel = new Produto();
    $produtos = $produtoModel->listar();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Produtos</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #121212;
      color: #fff;
    }
    .sidebar {
      background-color: #1e1e1e;
      min-height: 100vh;
      width: 240px;
      padding: 30px 20px;
    }
    .sidebar h4 {
      color: #0074ff;
      font-weight: 600;
      margin-bottom: 40px;
    }
    .sidebar a { color: #ccc; text-decoration: none; display: block; padding: 10px 0; }
    .main-content { padding: 40px; flex-grow: 1; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .table { background-color: #1f1f1f; border-radius: 10px; overflow: hidden; }
    .table thead th { background-color: #2a2a2a; color: #fff; border-bottom: 1px solid #333; }
    .table tbody tr:hover { background-color: #2c2c2c; }
    .btn-warning { background-color: #ffc107; border: none; color: #000; }
    .btn-danger { background-color: #dc3545; border: none; }
    .btn-primary { background-color: #007bff; border: none; }
    .modal-content { background-color: #1e1e1e; color: #fff; }
    .form-control { background-color: #2c2c2c; border-color: #444; color: #fff; }
    .form-control:focus { background-color: #2c2c2c; border-color: #007bff; color: #fff; box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25); }
    .footer { background-color: #1e1e1e; color: #ccc; text-align: center; padding: 15px 0; position: fixed; bottom: 0; width: 100%; border-top: 1px solid #333; }
  </style>
</head>
<body>

  <div class="d-flex">
    <aside class="sidebar">
      <h4>Portal de Compras</h4>
      <a href="?route=produtos" class="active">Produtos</a>
      <a href="?route=fornecedores" class="active">Fornecedores</a>
      <a href="logout.php">Sair</a>
    </aside>

    <main class="main-content">
      <div class="top-bar d-flex align-items-center justify-content-between gap-3">
        <div>
          <h2 class="fw-semibold mb-0">Produtos</h2>
          <?php if (!empty($_GET['q'])): ?>
            <small class="text-muted">Resultados para: "<?= htmlspecialchars($_GET['q']) ?>"</small>
          <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-2">
          <form class="d-flex" method="GET" action="?">
            <input type="hidden" name="route" value="produtos" />
            <input
              class="form-control form-control-sm me-2"
              type="search"
              name="q"
              placeholder="Pesquisar descrição ou código..."
              value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
              aria-label="Pesquisar"
              style="max-width:320px;"
            />
            <button class="btn btn-sm btn-primary" type="submit">Buscar</button>
            <?php if (!empty($_GET['q'])): ?>
              <a href="?route=produtos" class="btn btn-sm btn-secondary ms-2">Limpar</a>
            <?php endif; ?>
          </form>

          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastroProduto">Incluir Cadastro</button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-dark table-hover w-100">
          <thead>
            <tr>
              <th>#</th>
              <th>Descrição</th>
              <th>Código</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($produtos)): ?>
              <tr><td colspan="4" class="text-center">Nenhum produto cadastrado.</td></tr>
            <?php else: ?>
              <?php foreach ($produtos as $produto): ?>
                <tr>
                  <td><?= htmlspecialchars($produto['id']) ?></td>
                  <td><?= htmlspecialchars($produto['descricao']) ?></td>
                  <td><?= htmlspecialchars($produto['codigo']) ?></td>
                  <td>
                    <button 
                      class="btn btn-sm btn-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#modalEditarProduto"
                      data-id="<?= $produto['id'] ?>"
                      data-descricao="<?= htmlspecialchars($produto['descricao']) ?>"
                      data-codigo="<?= htmlspecialchars($produto['codigo']) ?>"
                    >Editar</button>
                    <button 
                      class="btn btn-sm btn-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#modalExcluirProduto"
                      data-id="<?= $produto['id'] ?>"
                    >Excluir</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- MODAL CADASTRO -->
  <div class="modal fade" id="modalCadastroProduto" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" action="?route=produtos">
        <input type="hidden" name="action" value="create">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Incluir Novo Produto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Descrição</label>
              <textarea class="form-control" name="descricao" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Código</label>
              <input type="text" class="form-control" name="codigo" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EDITAR -->
  <div class="modal fade" id="modalEditarProduto" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" action="?route=produtos">
        <input type="hidden" name="action" value="update">
        <input type="hidden" id="editar-id" name="id">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Editar Produto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Descrição</label>
              <textarea class="form-control" id="editar-descricao" name="descricao" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Código</label>
              <input type="text" class="form-control" id="editar-codigo" name="codigo" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EXCLUIR -->
  <div class="modal fade" id="modalExcluirProduto" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" action="?route=produtos">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" id="excluir-id" name="id">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Excluir Produto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Deseja realmente excluir este produto?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Excluir</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <footer class="footer">
    <p>© 2025 Portal de Compras - Todos os direitos reservados</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script>
    // Passa dados para o modal de edição
    $('#modalEditarProduto').on('show.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      $('#editar-id').val(button.data('id'));
      $('#editar-descricao').val(button.data('descricao'));
      $('#editar-codigo').val(button.data('codigo'));
    });

    // Passa o ID para o modal de exclusão
    $('#modalExcluirProduto').on('show.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      $('#excluir-id').val(button.data('id'));
    });

    // Se preferir submeter via action em um único endpoint, seu router/web.php deve ler $_POST['action']
    // e chamar o controller adequado (store/update/delete). Se quiser eu te mostro como fazer o router.
  </script>
</body>
</html>
