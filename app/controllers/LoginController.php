<?php
require_once __DIR__ . '/../models/User.php';
// Inicia uma sessão PHP. Necessário para usar $_SESSION. Armazena o id do usuário
session_start();

class LoginController {
    public function login() {

        // Verifica se a requisição HTTP foi feita usando o método POST.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Pega o valor jogado no input type = email e armazena na variavel $email
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            // Cria uma instância da classe User
            $userModel = new User();

            // Acessa o metodo login na classe User para fazer as validações
            $user = $userModel->login($email, $senha);

            // Se for true
            if ($user) {

                // Cria uma variavel $_SESSION que recebe o id do usuario
                $_SESSION['usuario_id'] = $user['id'];

                // Envia um cabeçalho HTTP que redireciona o navegador para tela-inicio.php
                header('Location: ?route=produtos');
                exit;
            } else {
                echo "<script>alert('E-mail ou senha inválidos!'); history.back();</script>";
            }
        }
    }

    public function register() {
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
    
}
?>