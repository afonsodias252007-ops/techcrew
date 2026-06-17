<?php
include('db.php');

// 1. Garantir que nenhum erro em segundo plano corrompe o documento
error_reporting(0);
ini_set('display_errors', 0);

// 2. Puxar os dados da base de dados antes de renderizar o HTML
$query = "SELECT * FROM projetores ORDER BY bloco ASC, sala ASC";
$resultado = mysqli_query($conexao, $query);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Relatório de Projetores</title>
    <style>
        /* Isolar completamente o estilo para garantir que sai bem no papel ou no PDF */
        html, body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #444444;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #111111 !important;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            color: #000000 !important;
            background: #ffffff !important;
        }
        th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-transform: uppercase;
        }
        tr {
            background: #ffffff !important;
        }
        
        /* Forçar ocultação de botões ou elementos do browser na impressão */
        @media print {
            body { padding: 0; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>TECH CREW - INVENTÁRIO FIXO DE PROJETORES</h1>
        <p>Escola Básica 2/3 da Cruz de Pau — Extraído em: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 22%;">BLOCO</th>
                <th style="width: 22%;">SALA / ESPAÇO</th>
                <th style="width: 28%;">EQUIPAMENTO INSTALADO</th>
                <th style="width: 20%;">OBSERVAÇÕES</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                while($row = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>#".$row['id']."</td>";
                    echo "<td>".htmlspecialchars($row['bloco'])."</td>";
                    echo "<td>".htmlspecialchars($row['sala'])."</td>";
                    echo "<td>".htmlspecialchars($row['equipamento'])."</td>";
                    echo "<td>".htmlspecialchars($row['observacoes'])."</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center; font-style:italic;'>Nenhum equipamento mapeado na base de dados.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script type="text/javascript">
        window.addEventListener('DOMContentLoaded', function() {
            // Pequeno delay de 500ms para garantir estabilidade visual antes do disparo
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>

</body>
</html>