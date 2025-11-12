<?php
require_once __DIR__ . '/../models/User.php';
// Inicia uma sessão PHP. Necessário para usar $_SESSION. Armazena o id do usuário


class LoginController
{
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
    
            $userModel = new User();
            $user = $userModel->login($email, $senha);
    
            if ($user) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $user['id'];
    
                header('Location: ?route=produtos');
                exit;
            } else {
                // redireciona para a mesma rota com flag de erro (você pode exibir a mensagem na view)
                header('Location: ?route=login&error=invalid');
                exit;
            }
        } else {
            // GET -> exibe a view de login
            // Ajuste o caminho abaixo conforme a organização do seu projeto.
            // Se sua view está em public/login.php:
            $loginView1 = __DIR__ . '/../../public/login.php';
            // Se sua view está em app/views/login.php:
            $loginView2 = __DIR__ . '/../views/login.php';
    
            if (file_exists($loginView1)) {
                require_once $loginView1;
            } elseif (file_exists($loginView2)) {
                require_once $loginView2;
            } else {
                echo '<p>View de login não encontrada. Ajuste o caminho em LoginController::login()</p>';
            }
        }
    }
    

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $senha = $_POST['senha'];
            $confirmar_senha = $_POST['confirmar_senha'];

            // Validação do e-mail
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('E-mail inválido!'); history.back();</script>";
                exit;
            }

            // Validação da senha forte
            $regexSenha = '/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?\":{}|<>]).{8,}$/';
            if (!preg_match($regexSenha, $senha)) {
                echo "<script>alert('A senha deve ter pelo menos 8 caracteres, uma letra maiúscula e um caractere especial.'); history.back();</script>";
                exit;
            }

            // Confirmar senha
            if ($senha !== $confirmar_senha) {
                echo "<script>alert('As senhas não coincidem!'); history.back();</script>";
                exit;
            }

            // Se passou em todas as validações, chama o model
            $userModel = new User();
            if ($userModel->register($email, $senha)) {
                echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='../public/login.php';</script>";
                exit;
            } else {
                echo "<script>alert('Erro ao cadastrar! Talvez o e-mail já esteja em uso.'); history.back();</script>";
                exit;
            }
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        session_unset();
        session_destroy();
        header('Location: ?route=login');
        exit;
    }
    


}
?>