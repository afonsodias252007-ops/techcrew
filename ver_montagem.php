<?php 
include('auth.php'); // Proteção de login de sessão
include('db.php');

// ==========================================
// SISTEMA DE FILTRAGEM / PESQUISA POR SALA (GET)
// ==========================================
$sala_pesquisa = isset($_GET['pesquisa_sala']) ? mysqli_real_escape_string($conexao, trim($_GET['pesquisa_sala'])) : '';

if (!empty($sala_pesquisa)) {
    // Filtra por correspondência parcial (ex: se digitar "10", apanha "Sala 101", "Sala 102", etc.)
    $query_principal = "SELECT * FROM ordem_montagem WHERE salas LIKE '%$sala_pesquisa%' ORDER BY ordem_montagem ASC";
} else {
    // Se não houver pesquisa, mostra a listagem cronológica total
    $query_principal = "SELECT * FROM ordem_montagem ORDER BY ordem_montagem ASC";
}

$resultado = mysqli_query($conexao, $query_principal);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Ordem de Montagem</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Definição das variáveis de cor do sistema para garantir o funcionamento do CSS */
        :root {
            --neon: #00ff9d;
            --cyan: #00ffff;
            --yellow: #ffe600;
            --border: rgba(0, 255, 157, 0.2);
            --font: 'Courier New', Courier, monospace;
        }

        body {
            margin: 0;
            font-family: var(--font); /* Fonte estilo terminal */
            background: #020202;
            color: var(--neon);
            overflow-x: hidden;
            position: relative;
        }

        /* Canvas para a chuva de código hacker no fundo */
        #matrix-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            opacity: 0.15;
            pointer-events: none !important;
        }

        /* Grelha tática combinada com Scanlines (Linhas de monitor antigo) */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 157, 0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 157, 0.015) 1px, transparent 1px),
                linear-gradient(rgba(0, 255, 157, 0.012) 50%, rgba(0, 0, 0, 0.18) 50%),
                linear-gradient(90deg, rgba(255, 0, 0, 0.008), rgba(0, 255, 0, 0.004), rgba(0, 0, 255, 0.008));
            background-size: 30px 30px, 30px 30px, 100% 4px, 6px 100%;
            z-index: 10;
            opacity: 0.6;
            pointer-events: none !important;
        }

        /* HUD Superior */
        .user-hud {
            background: rgba(0, 255, 102, 0.05);
            border: 1px solid rgba(0, 255, 102, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            background: rgba(0, 255, 157, 0.1);
            color: var(--neon);
            border: 1px solid rgba(0, 255, 157, 0.4);
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            font-family: var(--font);
            cursor: pointer;
        }
        .btn:hover {
            background: var(--neon);
            color: #000;
            box-shadow: 0 0 10px var(--neon);
        }

        .btn-logout {
            background: #ff3b3b;
            color: #fff;
            padding: 5px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }

        .btn-escola {
            background: rgba(0, 217, 255, 0.1);
            color: var(--cyan);
            border: 1px solid rgba(0, 217, 255, 0.4);
            padding: 5px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            transition: 0.2s ease;
        }
        .btn-escola:hover {
            background: var(--cyan);
            color: #000;
            box-shadow: 0 0 15px rgba(0, 217, 255, 0.4);
        }

        .btn-export {
            color: var(--cyan);
            border-color: var(--cyan);
            background: rgba(0, 217, 255, 0.05);
        }
        .btn-export:hover {
            background: var(--cyan);
            color: #000;
            box-shadow: 0 0 10px var(--cyan);
        }

        /* Estilização da Tabela Terminal */
        .terminal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: rgba(2, 2, 2, 0.8);
        }
        .terminal-table th {
            background: rgba(0, 255, 157, 0.15);
            color: #fff;
            border: 1px solid rgba(0, 255, 157, 0.3);
            padding: 12px;
            text-align: left;
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .terminal-table td {
            border: 1px solid rgba(0, 255, 157, 0.15);
            padding: 12px;
            font-size: 13px;
            color: #cbd5e1;
        }
        .terminal-table tr:hover {
            background: rgba(0, 255, 157, 0.05);
        }
        
        .badge-numero {
            background: rgba(0, 217, 255, 0.1);
            color: var(--cyan);
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            border: 1px solid rgba(0, 217, 255, 0.3);
        }

        /* Barra de pesquisa integrada */
        .search-hud {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            background: rgba(0, 255, 102, 0.02);
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid rgba(0, 255, 102, 0.05);
            margin-bottom: 20px;
        }
        .input-text {
            padding: 8px 12px;
            background: #000;
            border: 1px solid var(--border);
            color: #fff;
            border-radius: 6px;
            font-family: var(--font);
            font-size: 12px;
            outline: none;
            width: 200px;
        }
        .input-text:focus {
            border-color: var(--yellow);
        }
    </style>
</head>
<body>

    <canvas id="matrix-canvas"></canvas>

    <div class="container" style="padding: 20px; max-width: 1350px; margin: 0 auto;">
        
        <div class="user-hud">
            <div>
                <span>MONTAGEM // AGENTE: <strong><?php echo strtoupper($_SESSION['username']); ?></strong></span>
                <span style="margin-left: 20px; color: var(--yellow);">AUTORIZAÇÃO: <strong><?php echo strtoupper($_SESSION['role']); ?></strong></span>
            </div>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="https://terrasdelarus.edu.pt/" target="_blank" class="btn-escola">[ WEBSITE_ESCOLA ➔ ]</a>
                <a href="logout.php" class="btn-logout">[ TERMINAR_SESSÃO ]</a>
            </div>
        </div>

        <div class="search-hud">
            <div>
                <a href="index.php" class="btn" style="margin-right: 5px;">➔ [ VOLTAR AO PAINEL ]</a>
                <a href="gerir_montagem.php" class="btn" style="border-color: var(--yellow); color: var(--yellow);">[ + GERIR / INJECTAR ]</a>
            </div>
            
            <form method="GET" action="montagem.php" style="display: flex; gap: 10px; align-items: center;">
                <label style="color: var(--yellow); font-size: 11px; font-weight: bold;">[ LOCALIZAR_SALA ]</label>
                <input type="text" name="pesquisa_sala" class="input-text" placeholder="Ex: Sala 102" value="<?php echo htmlspecialchars($sala_pesquisa); ?>" autocomplete="off">
                <button type="submit" class="btn">[ BUSCA ]</button>
                <?php if (!empty($sala_pesquisa)): ?>
                    <a href="montagem.php" class="btn" style="border-color:#ff3b3b; color:#ff3b3b;">[ CLEAR ]</a>
                <?php endif; ?>
                
                <a href="exportar_csv.php" class="btn btn-export" style="margin-left: 15px;">[ CSV ]</a>
                <a href="exportar_pdf.php" target="_blank" class="btn btn-export">[ PDF ]</a>
            </form>
        </div>

        <div class="card-painel" style="background: rgba(5, 5, 5, 0.9); border: 1px solid rgba(0, 255, 157, 0.2); padding: 25px; border-radius: 8px;">
            <div class="terminal-header" style="border-bottom: 1px solid rgba(0, 255, 157, 0.2); padding-bottom: 15px; margin-bottom: 20px;">
                <h1 style="margin: 0; font-size: 22px;">DEPLOYMENT_MAP // LINEAR_INFRASTRUCTURE</h1>
                <p style="margin: 5px 0 0 0; color: #7df7af; font-size: 12px;">Estado global e ordem de montagem das salas tecnológicas e postos informáticos.</p>
            </div>

            <div style="overflow-x: auto;">
                <table class="terminal-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">[ ORDEM MONTAGEM ]</th>
                            <th style="width: 20%;">[ SALAS ]</th>
                            <th style="width: 15%;">[ N° de PC ]</th>
                            <th style="width: 35%;">[ OBSERVAÇÕES ]</th>
                            <th style="width: 15%;">[ EXTENSÕES ]</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($resultado && mysqli_num_rows($resultado) > 0) {
                            while($row = mysqli_fetch_assoc($resultado)) {
                                echo "<tr>";
                                
                                // 1. Ordem Montagem
                                echo "<td style='color: var(--neon); font-weight: bold;'># " . intval($row['ordem_montagem']) . "</td>";
                                
                                // 2. Salas
                                echo "<td style='color: var(--yellow); font-weight: bold;'>" . htmlspecialchars($row['salas']) . "</td>";
                                
                                // 3. N° de PC
                                echo "<td><span class='badge-numero'>" . intval($row['num_pc']) . " Postos</span></td>";
                                
                                // 4. Observações
                                echo "<td style='color: #cbd5e1;'>" . htmlspecialchars($row['observacoes'] ?? '-') . "</td>";
                                
                                // 5. Extensões
                                echo "<td>" . htmlspecialchars($row['extensoes'] ?? '-') . "</td>";
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center; color: #ff3b3b; padding: 20px;'>[ NENHUM MÓDULO LOCALIZADO COM OS PARÂMETROS ATUAIS ]</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById("matrix-canvas"); const ctx = canvas.getContext("2d");
        function resizeCanvas() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; } resizeCanvas();
        window.addEventListener("resize", resizeCanvas);
        const matrixChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""); const fontSize = 16; const columns = canvas.width / fontSize;
        const dropPositions = Array(Math.floor(columns)).fill(1);
        function drawMatrix() {
            ctx.fillStyle = "rgba(2, 2, 2, 0.05)"; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = "#00ff9d"; ctx.font = fontSize + "px monospace";
            for (let i = 0; i < dropPositions.length; i++) {
                const text = matrixChars[Math.floor(Math.random() * matrixChars.length)];
                ctx.fillText(text, i * fontSize, dropPositions[i] * fontSize);
                if (dropPositions[i] * fontSize > canvas.height && Math.random() > 0.975) dropPositions[i] = 0;
                dropPositions[i]++;
            }
        }
        setInterval(drawMatrix, 35);
    </script>
</body>
</html>