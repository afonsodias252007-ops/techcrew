<?php
include('db.php');

$pesquisa = "";
$where_search = "";
if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    $pesquisa = mysqli_real_escape_string($conexao, trim($_GET['buscar']));
    $where_search = " WHERE nome LIKE '%$pesquisa%' OR nif LIKE '%$pesquisa%' ";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew</title>
    <link rel="icon" type="image/png" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estrutura Base de Alta Legibilidade */
        * { box-sizing: border-box; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            background: #ffffff; 
            color: #000000; 
            padding: 20px; 
            font-size: 13px; 
            line-height: 1.5;
        }
        
        /* Cabeçalho do Relatório */
        .header { 
            border-bottom: 3px double #000000; 
            padding-bottom: 12px; 
            margin-bottom: 25px; 
            text-align: center; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 20px; 
            letter-spacing: 1px; 
            font-weight: bold;
        }
        .header p { 
            margin: 6px 0 0 0; 
            font-size: 12px; 
            color: #333333; 
        }
        
        /* Configuração de Tabelas Claras e Visíveis */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            page-break-inside: auto;
        }
        tr { 
            page-break-inside: avoid; 
            page-break-after: auto; 
        }
        th, td { 
            border: 1px solid #000000; 
            padding: 10px 12px; 
            text-align: left; 
            vertical-align: middle; 
        }
        th { 
            background: #f0f0f0; 
            font-weight: bold; 
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Distintivos (Badges) de Estatuto */
        .badge { 
            display: inline-block;
            font-size: 11px; 
            padding: 3px 6px; 
            border: 1px solid #000000; 
            font-weight: bold; 
            background: #ffffff;
        }
        .badge-prof { background: #e6f7ff; }
        .badge-aluno { background: #fffbe6; }
        
        /* Bloco de Cada Computador */
        .pc-box { 
            background: #fcfcfc; 
            padding: 8px 10px; 
            margin-bottom: 8px; 
            border: 1px dashed #444444; 
            font-size: 12px; 
            border-radius: 4px;
        }
        .pc-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .pc-meta {
            font-size: 11px;
            color: #222222;
        }
        
        /* Destaque para Observações */
        .obs-text {
            display: block;
            margin-top: 5px;
            padding: 4px 6px;
            background: #f5f5f5;
            border-left: 3px solid #000000;
            font-style: italic;
            font-size: 11px;
            color: #111111;
        }

        /* Aviso de Impressão no Ecrã */
        .no-print { 
            background: #fffbe6; 
            border: 1px solid #ffe58f; 
            padding: 15px; 
            margin-bottom: 25px; 
            text-align: center;
            border-radius: 6px;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        .no-print button {
            padding: 6px 16px;
            margin: 0 5px;
            cursor: pointer;
            font-weight: bold;
            border: 1px solid #ccc;
            background: #fff;
            border-radius: 4px;
        }
        .no-print button.btn-primary {
            background: #1890ff;
            color: #fff;
            border-color: #1890ff;
        }

        /* Otimizações Finais para a Folha de Papel */
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            th { background: #e5e5e5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-prof { background: #e6f7ff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-aluno { background: #fffbe6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .pc-box { background: #ffffff !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <b>⚙️ MÓDULO DE EXPORTAÇÃO ATIVO</b><br>
        A janela de impressão do sistema foi aberta automaticamente para gerar o ficheiro.
        <br><br>
        <button class="btn-primary" onclick="window.print()">Reabrir Janela de Gravação</button>
        <button onclick="window.close()">Fechar Janela / Voltar</button>
    </div>

    <div class="header">
        <h1>[ SISTEMA DE INVENTÁRIO - RELATÓRIO DE COMODATOS ]</h1>
        <p>Data de Emissão: <?php echo date('d/m/Y H:i:s'); ?> | Filtro de Pesquisa: <b>"<?php echo htmlspecialchars($pesquisa ? $pesquisa : 'TODOS OS UTENTES'); ?>"</b></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">REG. ID</th>
                <th style="width: 32%;">UTENTE / IDENTIFICAÇÃO</th>
                <th style="width: 15%;">ESTATUTO</th>
                <th style="width: 45%;">EQUIPAMENTOS ATRIBUÍDOS E CONTRATOS</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $utentes = mysqli_query($conexao, "SELECT * FROM alunos $where_search ORDER BY id DESC");
            if ($utentes && mysqli_num_rows($utentes) > 0): 
                while($u = mysqli_fetch_assoc($utentes)): 
            ?>
                <tr>
                    <td style="text-align: center; font-weight: bold; vertical-align: middle;">
                        #<?php echo $u['id']; ?>
                    </td>
                    <td style="vertical-align: middle;">
                        <span style="font-size: 14px; font-weight: bold;"><?php echo htmlspecialchars($u['nome']); ?></span><br>
                        <span style="color: #333333;">NIF / CONTRIBUINTE: <?php echo htmlspecialchars($u['nif']); ?></span>
                    </td>
                    <td style="vertical-align: middle;">
                        <?php if($u['tipo'] == 'professor'): ?>
                            <span class="badge badge-prof">PROFESSOR</span>
                        <?php else: ?>
                            <span class="badge badge-aluno">ALUNO (TURMA: <?php echo htmlspecialchars($u['turma']); ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                        <?php 
                        $emp_query = mysqli_query($conexao, "SELECT e.*, c.marca, c.serial_number FROM emprestimos e JOIN computadores c ON e.computador_id = c.id WHERE e.utente_id = {$u['id']}");
                        if($emp_query && mysqli_num_rows($emp_query) > 0) {
                            while($emp = mysqli_fetch_assoc($emp_query)) {
                                $carregador = $emp['tem_carregador'] ? "[+ Carregador Incluído]" : "[- SEM CARREGADOR]";
                                $dev_data = $emp['data_devolucao'] ? date('d/m/Y', strtotime($emp['data_devolucao'])) : 'Não Definida';
                                
                                echo "<div class='pc-box'>
                                        <div class='pc-title'>💻 PC ID #{$emp['computador_id']} — " . htmlspecialchars($emp['marca']) . "</div>
                                        <div class='pc-meta'>
                                            <b>S/N:</b> " . htmlspecialchars($emp['serial_number']) . "<br>
                                            <b>Alocação:</b> " . date('d/m/Y', strtotime($emp['data_entrega'])) . " até " . $dev_data . "<br>
                                            <b>Acessórios:</b> $carregador
                                        </div>";
                                
                                if(!empty($emp['observacoes'])) {
                                    echo "<span class='obs-text'><b>Observações:</b> " . htmlspecialchars($emp['observacoes']) . "</span>";
                                }
                                echo "</div>";
                            }
                        } else {
                            echo "<span style='color: #666666; font-style: italic;'>Nenhum equipamento associado a este registo.</span>";
                        }
                        ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px; color: #555555; font-style: italic;">
                        Nenhum registo foi encontrado para os critérios de busca inseridos.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Dispara o comando nativo de renderização e PDF do browser
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>