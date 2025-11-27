<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Fornecedor.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona para login se não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ?route=login");
    exit;
}

// Variável $fornecedores deve vir do Controller
if (!isset($fornecedores)) {
    $fornecedorModel = new Fornecedor();
    $fornecedores = $fornecedorModel->listar();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fornecedores</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
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
    .btn-primary,
    .btn-danger,
    .btn-secondary {
      border-radius: 999px;
    }
    .btn-primary {
      background: linear-gradient(135deg, #2563eb, #4f46e5);
      border: none;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #1d4ed8, #4338ca);
    }
    .btn-danger {
      background: #dc2626;
      border: none;
    }
    .btn-danger:hover {
      background: #b91c1c;
    }
    .btn-outline-light {
      border-radius: 999px;
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
    .form-control {
      background-color: #020617;
      border-color: #1f2937;
      color: #e5e7eb;
      border-radius: 12px;
    }
    .form-control:focus {
      background-color: #020617;
      border-color: #2563eb;
      color: #fff;
      box-shadow: 0 0 0 0.15rem rgba(37,99,235,0.35);
    }
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
      <a href="?route=fornecedor" class="active"><i class="bi bi-building"></i> Fornecedores</a>
      <a href="?route=cotacoes"><i class="bi bi-receipt-cutoff"></i> Cotações</a>
      <a href="?route=logout"><i class="bi bi-box-arrow-right"></i> Sair</a>
    </aside>

    <main class="main-content">
      <div class="top-bar d-flex align-items-center justify-content-between gap-3">
        <div>
          <h2 class="fw-semibold mb-0">
            <i class="bi bi-building"></i> Fornecedores
          </h2>
          <?php if (!empty($_GET['q'])): ?>
            <small class="text-muted">Resultados para: "<?= htmlspecialchars($_GET['q']) ?>"</small>
          <?php else: ?>
            <small class="text-muted">Gerencie os parceiros que recebem suas cotações.</small>
          <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-2">
          <form class="d-flex" method="GET" action="?">
            <input type="hidden" name="route" value="fornecedor" />
            <input
              class="form-control form-control-sm me-2"
              type="search"
              name="q"
              placeholder="Pesquisar nome, CNPJ, e-mail..."
              value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
              aria-label="Pesquisar"
              style="max-width:320px;"
            />
            <button class="btn btn-sm btn-outline-light" type="submit">
              <i class="bi bi-search"></i>
            </button>
            <?php if (!empty($_GET['q'])): ?>
              <a href="?route=fornecedor" class="btn btn-sm btn-secondary ms-2">
                <i class="bi bi-x-circle"></i>
              </a>
            <?php endif; ?>
          </form>

          <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCadastroFornecedor">
            <i class="bi bi-person-plus"></i> Cadastrar Fornecedor
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
          <thead>
            <tr>
              <th><i class="bi bi-hash"></i></th>
              <th><i class="bi bi-building me-1"></i> Empresa</th>
              <th><i class="bi bi-file-earmark-text me-1"></i> CNPJ</th>
              <th><i class="bi bi-envelope me-1"></i> E-mail</th>
              <th><i class="bi bi-telephone me-1"></i> Contato</th>
              <th><i class="bi bi-tags me-1"></i> Segmento</th>
              <th class="text-end"><i class="bi bi-gear"></i> Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($fornecedores)): ?>
              <tr><td colspan="7" class="text-center py-4">Nenhum fornecedor cadastrado.</td></tr>
            <?php else: ?>
              <?php foreach ($fornecedores as $fornecedor): ?>
                <tr>
                  <td><?= htmlspecialchars($fornecedor['id']) ?></td>
                  <td><?= htmlspecialchars($fornecedor['nome_empresa']) ?></td>
                  <td><?= htmlspecialchars($fornecedor['cnpj']) ?></td>
                  <td><?= htmlspecialchars($fornecedor['email']) ?></td>
                  <td><?= htmlspecialchars($fornecedor['contato']) ?></td>
                  <td><?= htmlspecialchars($fornecedor['segmento']) ?></td>
                  <td class="text-end">
                    <button 
                      class="btn btn-sm btn-outline-light me-1"
                      data-bs-toggle="modal"
                      data-bs-target="#modalEditarFornecedor"
                      data-id="<?= $fornecedor['id'] ?>"
                      data-nome="<?= htmlspecialchars($fornecedor['nome_empresa']) ?>"
                      data-cnpj="<?= htmlspecialchars($fornecedor['cnpj']) ?>"
                      data-email="<?= htmlspecialchars($fornecedor['email']) ?>"
                      data-contato="<?= htmlspecialchars($fornecedor['contato']) ?>"
                      data-segmento="<?= htmlspecialchars($fornecedor['segmento']) ?>"
                    >
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button 
                      class="btn btn-sm btn-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#modalExcluirFornecedor"
                      data-id="<?= $fornecedor['id'] ?>"
                    >
                      <i class="bi bi-trash3"></i>
                    </button>
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
  <div class="modal fade" id="modalCadastroFornecedor" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" action="?route=fornecedor">
        <input type="hidden" name="action" value="create">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Cadastrar Novo Fornecedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome da Empresa</label>
              <input type="text" class="form-control" name="nome_empresa" required />
            </div>
            <div class="mb-3">
              <label class="form-label">CNPJ</label>
              <input type="text" class="form-control" name="cnpj" required />
            </div>
            <div class="mb-3">
              <label class="form-label">E-mail</label>
              <input type="email" class="form-control" name="email" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Contato</label>
              <input type="text" class="form-control" name="contato" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Segmento</label>
              <input type="text" class="form-control" name="segmento" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle"></i> Salvar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EDITAR -->
  <div class="modal fade" id="modalEditarFornecedor" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" action="?route=fornecedor">
        <input type="hidden" name="action" value="update">
        <input type="hidden" id="editar-id" name="id">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Editar Fornecedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nome da Empresa</label>
              <input type="text" class="form-control" id="editar-nome" name="nome_empresa" required />
            </div>
            <div class="mb-3">
              <label class="form-label">CNPJ</label>
              <input type="text" class="form-control" id="editar-cnpj" name="cnpj" required />
            </div>
            <div class="mb-3">
              <label class="form-label">E-mail</label>
              <input type="email" class="form-control" id="editar-email" name="email" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Contato</label>
              <input type="text" class="form-control" id="editar-contato" name="contato" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Segmento</label>
              <input type="text" class="form-control" id="editar-segmento" name="segmento" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle"></i> Salvar Alterações
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EXCLUIR -->
  <div class="modal fade" id="modalExcluirFornecedor" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" action="?route=fornecedor">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" id="excluir-id" name="id">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-trash3 me-2"></i> Excluir Fornecedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Deseja realmente excluir este fornecedor?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-trash3"></i> Excluir
            </button>
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
    $('#modalEditarFornecedor').on('show.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      $('#editar-id').val(button.data('id'));
      $('#editar-nome').val(button.data('nome'));
      $('#editar-cnpj').val(button.data('cnpj'));
      $('#editar-email').val(button.data('email'));
      $('#editar-contato').val(button.data('contato'));
      $('#editar-segmento').val(button.data('segmento'));
    });

    $('#modalExcluirFornecedor').on('show.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      $('#excluir-id').val(button.data('id'));
    });
  </script>

</body>
</html>
