<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal de Compras</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* ======== BASE ======== */
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0057ff, #00b3ff);
      color: white;
      height: 100vh;
      overflow-x: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ======== CONTAINER PRINCIPAL ======== */
    .login-box {
      background-color: white;
      border-radius: 25px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      max-width: 950px;
      width: 100%;
      display: flex;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      animation: fadeIn 0.8s ease forwards;
    }

    .login-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    /* ======== LADO ESQUERDO (LOGIN) ======== */
    .left-box {
      background-color: #ffffff;
      color: #000;
      padding: 50px 40px;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .left-box h1 {
      color: #0057ff;
      font-weight: 600;
    }

    .btn-primary {
      background-color: #0057ff;
      border: none;
      border-radius: 12px;
      transition: 0.3s;
      font-weight: 500;
    }

    .btn-primary:hover {
      background-color: #0041cc;
      transform: scale(1.03);
    }

    /* ======== LADO DIREITO (REGISTRO) ======== */
    .right-box {
      background: linear-gradient(135deg, #0057ff, #003bb3);
      color: white;
      padding: 50px 40px;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .form-control {
      border-radius: 10px;
      border: 1px solid #ccc;
      transition: box-shadow 0.3s ease;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(0, 87, 255, 0.25);
      border-color: #0057ff;
    }

    .btn-outline-primary {
      border-color: white;
      color: #0057ff;
      background-color: white;
      border-radius: 12px;
      transition: 0.3s;
      font-weight: 500;
    }

    .btn-outline-primary:hover {
      background-color: #e6f0ff;
      transform: scale(1.03);
    }

    /* ======== ANIMAÇÕES ======== */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ======== FOOTER ======== */
    footer {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
      color: white;
      opacity: 0.8;
    }

    footer a {
      color: white;
      text-decoration: none;
      font-weight: 500;
    }

    footer a:hover {
      text-decoration: underline;
    }

    .footer-fixado {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  text-align: center;
  padding: 15px 0;
  background: transparent;
  color: white;
  font-size: 14px;
  opacity: 0.85;
  z-index: 10;
}

.footer-fixado a {
  color: white;
  text-decoration: none;
  font-weight: 500;
}

.footer-fixado a:hover {
  text-decoration: underline;
}


    /* ======== RESPONSIVO ======== */
    @media (max-width: 768px) {
      .login-box {
        flex-direction: column;
      }
      .left-box, .right-box {
        padding: 30px 25px;
      }
    }
  </style>
</head>
<body>
  <div class="login-box">
    <!-- LADO ESQUERDO (LOGIN) -->
    <div class="left-box">
      <h1>Portal de Compras</h1>
      <p class="mb-4">Acesse sua conta</p>
      <form method="POST" action="../routes/web.php?route=login">
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" class="form-control" name="email" placeholder="exemplo@email.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input type="password" class="form-control" name="senha" placeholder="********" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
      </form>
    </div>

    <!-- LADO DIREITO (REGISTRO) -->
    <div class="right-box">
      <h3 class="text-white mb-4">Criar nova conta</h3>
      <form method="POST" action="../routes/web.php?route=register" onsubmit="return validarFormulario()">
        <div class="mb-3">
          <label class="form-label text-white">E-mail</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
          <label class="form-label text-white">Senha</label>
          <input type="password" class="form-control" name="senha" id="senha" required>
          <div class="form-text text-light">
            A senha deve ter pelo menos <strong>8 caracteres</strong>, uma <strong>letra maiúscula</strong> e um <strong>caractere especial</strong>.
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label text-white">Confirmar Senha</label>
          <input type="password" class="form-control" name="confirmar_senha" id="confirmar_senha" required>
        </div>
        <button type="submit" class="btn btn-outline-primary w-100">Registrar</button>
      </form>
    </div>
  </div>

  <footer class="footer-fixado">
    <a href="#">portaldecompras.com</a> © Todos os direitos reservados
  </footer>

  <script>
  function validarFormulario() {
    const senha = document.getElementById("senha").value;
    const confirmar = document.getElementById("confirmar_senha").value;
    const regexSenha = /^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;

    if (!regexSenha.test(senha)) {
      alert("A senha deve ter pelo menos 8 caracteres, uma letra maiúscula e um caractere especial.");
      return false;
    }

    if (senha !== confirmar) {
      alert("As senhas não coincidem.");
      return false;
    }

    return true;
  }
  </script>
</body>
</html>
