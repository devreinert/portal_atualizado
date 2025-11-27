<?php
require_once __DIR__ . '/../models/Cotacao.php';
require_once __DIR__ . '/../../config/database.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;

class CotacaoController {
    public $model;

    public function __construct() {
        $this->model = new Cotacao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Lista todas as cotações e carrega dados auxiliares para a view
     */
    public function index() {
        // pega todas as cotações via model
        $cotacoes = $this->model->all();

        // monta array de itens de cada cotação (para os modais)
        $itensPorCotacao = [];
        foreach ($cotacoes as $c) {
            $itensPorCotacao[$c['id']] = $this->model->itens($c['id']);
        }

        // conecta para buscar fornecedores e produtos (aliasando colunas para 'nome')
        $db = Database::connect();

        // fornecedores: nome_empresa -> nome (para compatibilidade com views)
        $fornecedores = $db->query("SELECT id, nome_empresa AS nome FROM fornecedores ORDER BY nome_empresa")
                           ->fetchAll(PDO::FETCH_ASSOC);

        // produtos: descricao -> nome
        $produtos = $db->query("SELECT id, descricao AS nome FROM produtos ORDER BY descricao")
                       ->fetchAll(PDO::FETCH_ASSOC);

        // disponibiliza variáveis para a view
        require_once __DIR__ . '/../../public/cotacoes.php';
    }

    /**
     * Mostra formulário de criação (se usado como rota separada)
     */
    public function create() {
        $db = Database::connect();
        $fornecedores = $db->query("SELECT id, nome_empresa AS nome FROM fornecedores ORDER BY nome_empresa")
                           ->fetchAll(PDO::FETCH_ASSOC);
        $produtos = $db->query("SELECT id, descricao AS nome FROM produtos ORDER BY descricao")
                       ->fetchAll(PDO::FETCH_ASSOC);

        
        require_once __DIR__ . '/../../public/cotacoes.php';
    }

    /**
     * Salva uma nova cotação e seus itens
     * e em seguida gera o PDF e envia por e-mail ao fornecedor
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?route=cotacoes');
            exit;
        }

        $fornecedorId = $_POST['fornecedor_id'] ?? null;
        $produtos     = $_POST['produto_id'] ?? [];
        $qtds         = $_POST['quantidade'] ?? [];

        if (empty($fornecedorId)) {
            $_SESSION['flash_error'] = 'Fornecedor obrigatório.';
            header('Location: ?route=cotacoes');
            exit;
        }

        $db = Database::connect();

        try {
            $db->beginTransaction();

            // status inicial = 'aberta'
            $stmt = $db->prepare(
                "INSERT INTO cotacoes (fornecedor_id, status, criado_em) VALUES (?, 'aberta', NOW())"
            );
            $stmt->execute([$fornecedorId]);

            $cotacaoId = $db->lastInsertId();

            if (!empty($produtos) && is_array($produtos)) {
                $stmtItem = $db->prepare(
                    "INSERT INTO cotacao_itens (cotacao_id, produto_id, quantidade) VALUES (?, ?, ?)"
                );
                $count = max(count($produtos), count($qtds));
                for ($i = 0; $i < $count; $i++) {
                    $pid = $produtos[$i] ?? null;
                    $qt  = $qtds[$i] ?? 0;

                    if (!empty($pid) && (int)$qt > 0) {
                        $stmtItem->execute([$cotacaoId, $pid, (int)$qt]);
                    }
                }
            }

            $db->commit();
            $_SESSION['flash_success'] = 'Cotação criada com sucesso.';

            // 🔹 Após salvar no banco, tenta gerar PDF e enviar por e-mail
            $this->enviarEmailCotacao($cotacaoId);

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Erro ao criar cotação: ' . $e->getMessage();
        }

        header('Location: ?route=cotacoes');
        exit;
    }

    /**
     * Exibe detalhes de uma cotação (se quiser usar em página separada)
     */
    public function show($id) {
        $cotacao = $this->model->find($id);
        if (!$cotacao) {
            $_SESSION['flash_error'] = 'Cotação não encontrada.';
            header('Location: ?route=cotacoes');
            exit;
        }

        $itens = $this->model->itens($id);

        // se algum dia for usar, ajuste o caminho da view
        require_once __DIR__ . '/../views/cotacoes/show.php';
    }

    /**
     * Atualiza o status da cotação:
     *  - cancelada  -> "Cotação cancelada"
     *  - encerrada  -> "Cotação encerrada"
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?route=cotacoes');
            exit;
        }

        $id     = $_POST['id']     ?? null;
        $status = $_POST['status'] ?? null;

        if (empty($id) || !in_array($status, ['cancelada', 'encerrada'])) {
            $_SESSION['flash_error'] = 'Dados inválidos para atualizar cotação.';
            header('Location: ?route=cotacoes');
            exit;
        }

        $db = Database::connect();

        try {
            $stmt = $db->prepare("UPDATE cotacoes SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            if ($status === 'cancelada') {
                $_SESSION['flash_success'] = 'Cotação cancelada.';
            } elseif ($status === 'encerrada') {
                $_SESSION['flash_success'] = 'Cotação encerrada.';
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar cotação: ' . $e->getMessage();
        }

        header('Location: ?route=cotacoes');
        exit;
    }

    /**
     * Remove cotação e seus itens (se existir)
     */
    public function delete($id) {
        $db = Database::connect();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("DELETE FROM cotacao_itens WHERE cotacao_id = ?");
            $stmt->execute([$id]);

            $stmt2 = $db->prepare("DELETE FROM cotacoes WHERE id = ?");
            $stmt2->execute([$id]);

            $db->commit();
            $_SESSION['flash_success'] = 'Cotação removida.';
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Erro ao remover cotação: ' . $e->getMessage();
        }

        header('Location: ?route=cotacoes');
        exit;
    }

    /**
     * Gera um PDF da cotação e envia por e-mail para o fornecedor
     */
    private function enviarEmailCotacao($cotacaoId)
    {
        try {
            $db = Database::connect();

            // Buscar dados da cotação
            $cotacao = $this->model->find($cotacaoId);
            if (!$cotacao) {
                return;
            }

            // Itens da cotação
            $itens = $this->model->itens($cotacaoId);

            // Buscar fornecedor (usa coluna "email" da sua tabela)
            $stmtForn = $db->prepare("SELECT nome_empresa, email FROM fornecedores WHERE id = ?");
            $stmtForn->execute([$cotacao['fornecedor_id']]);
            $fornecedor = $stmtForn->fetch(PDO::FETCH_ASSOC);

            if (!$fornecedor || empty($fornecedor['email'])) {
                $_SESSION['flash_error'] = 'Cotação criada, mas o fornecedor não possui e-mail cadastrado.';
                return;
            }

            // Montar HTML do PDF usando o arquivo em /public/pdf_cotacao.php
            $dadosCotacao    = $cotacao;
            $dadosItens      = $itens;
            $dadosFornecedor = $fornecedor;

            ob_start();
            include __DIR__ . '/../../public/pdf_cotacao.php';
            $html = ob_get_clean();

            // Gerar PDF
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            // Enviar e-mail
            $mail = new PHPMailer(true);

            // 🔧 CONFIGURE AQUI SEU SMTP
            // 🔧 CONFIGURE AQUI SEU SMTP (MAILTRAP)
          

            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'e5581940f77672';   // o seu Username do Mailtrap
            $mail->Password   = '4c056948f79243';  // a sua Password do Mailtrap
            $mail->Port       = 2525;               // <-- apenas UM número, use 2525
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // pode deixar assim
            $mail->CharSet    = 'UTF-8';

            
            

            $mail->setFrom('seu-email@seuprovedor.com', 'Portal de Compras');
            $mail->addAddress($fornecedor['email'], $fornecedor['nome_empresa']);

            $mail->Subject = 'Cotação #' . $cotacaoId;
            $mail->Body    = "Olá {$fornecedor['nome_empresa']},\n\nSegue em anexo a cotação de número {$cotacaoId}.\n\nAtenciosamente,\nPortal de Compras";

            // Anexar o PDF
            $mail->addStringAttachment($pdfOutput, "cotacao_{$cotacaoId}.pdf");

            $mail->send();

            $_SESSION['flash_success'] =
                ($_SESSION['flash_success'] ?? 'Cotação criada com sucesso.') .
                ' E-mail enviado ao fornecedor.';

        } catch (\Throwable $e) {
            // Não quebra o fluxo; apenas informa o erro de envio
            $_SESSION['flash_error'] =
                'Cotação criada, mas ocorreu um erro ao gerar/enviar o e-mail: ' .
                $e->getMessage();
        }
    }
}
