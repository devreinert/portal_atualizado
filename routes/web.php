<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/LoginController.php';
require_once __DIR__ . '/../app/controllers/ProdutoController.php';
require_once __DIR__ . '/../app/controllers/FornecedorController.php';


$controller = new LoginController();

// Verifica se contem o route no URL no form, isset retorna true
if (isset($_GET['route'])) {
    switch ($_GET['route']) {

        case 'login':
            $controller->login();
            break;

        case 'register':
            $controller->register();
            break;

            case 'logout':
                // Opcional: delegar ao controller
                $controller = new LoginController();
                $controller->logout();
                // se preferir manter direto aqui:
                // session_unset();
                // session_destroy();
                // header("Location: ?route=login");
                // exit;
                break;

        case 'produtos':
            $controller = new ProdutoController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action'])) {
                    switch ($_POST['action']) {
                        case 'create': $controller->store(); break;
                        case 'update': $controller->update(); break;
                        case 'delete': $controller->delete(); break;
                    }
                }
            } else {
                $controller->index();
            }
            break;

        case 'fornecedor':
            $controller = new FornecedorController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action'])) {
                    switch ($_POST['action']) {
                        case 'create': $controller->store(); break;
                        case 'update': $controller->update(); break;
                        case 'delete': $controller->delete(); break;
                    }
                }
            } else {
                $controller->index();
            }
            break;

            case 'cotacoes':
                require_once __DIR__ . '/../app/controllers/CotacaoController.php';
                $controller = new CotacaoController();
    
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_POST['action'])) {
                        switch ($_POST['action']) {
                            case 'create': 
                                $controller->store(); 
                                break;
                        }
                    }
                } else {
                    $controller->index();
                }
                break;
    

        default:
            echo "Rota inválida!";
            break;


    }
}
?>