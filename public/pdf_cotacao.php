<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cotação #<?= htmlspecialchars($dadosCotacao['id'] ?? '') ?></title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
        }
        .info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }
        th {
            background: #eee;
        }
    </style>
</head>
<body>

<h1>Cotação #<?= htmlspecialchars($dadosCotacao['id'] ?? '') ?></h1>

<div class="info">
    <p><strong>Fornecedor:</strong> <?= htmlspecialchars($dadosFornecedor['nome_empresa'] ?? '') ?></p>
    <p><strong>E-mail:</strong> <?= htmlspecialchars($dadosFornecedor['email'] ?? '') ?></p>
    <p><strong>Data da cotação:</strong> <?= htmlspecialchars($dadosCotacao['criado_em'] ?? '') ?></p>
</div>

<h3>Itens da cotação</h3>

<table>
    <thead>
        <tr>
            <th>Produto</th>
            <th>Quantidade</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($dadosItens)): ?>
            <?php foreach ($dadosItens as $item): ?>
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
                <td colspan="2">Nenhum item cadastrado para esta cotação.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
