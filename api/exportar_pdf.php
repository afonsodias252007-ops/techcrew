<?php
include('auth.php');
include('db.php');
$resultado = mysqli_query($conexao, "SELECT * FROM ordem_montagem ORDER BY ordem_montagem ASC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório - Ordem de Montagem de Salas</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; padding: 20px; background: #fff; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f2f2f2; border: 1px solid #999; padding: 8px; font-size: 12px; text-align: left; }
        td { border: 1px solid #ccc; padding: 8px; font-size: 12px; }
        tr:nth-child(even) { background: #fafafa; }
        
        /* Força a janela de impressão a abrir e oculta botões na folha */
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; background: #f1f5f9; padding: 10px; border-radius: 4px; display: flex; justify-content: space-between;">
        <span>Visualização de Impressão de PDF</span>
        <button onclick="window.print()" style="padding: 5px 15px; font-weight: bold; cursor: pointer;">[ GERAR PDF / IMPRIMIR ]</button>
    </div>

    <div class="header">
        <h1>Tech Crew - Relatório de Infraestrutura</h1>
        <p>Documento gerado em: <?php echo date('d/m/Y H:i'); ?> | Operador: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ORDEM MONTAGEM</th>
                <th>SALAS</th>
                <th>N° de PC</th>
                <th>EXTENSÕES</th>
                <th>OBSERVAÇÕES</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            while($row = mysqli_fetch_assoc($resultado)) {
                echo "<tr>";
                echo "<td><strong>" . $row['ordem_montagem'] . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['rooms'] ?? $row['salas']) . "</td>";
                echo "<td>" . $row['num_pc'] . "</td>";
                echo "<td>" . htmlspecialchars($row['extensoes'] ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($row['observacoes'] ?? '-') . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        // Abre automaticamente o gestor de gravação em PDF ao carregar a página
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>