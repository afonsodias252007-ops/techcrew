<?php
include('auth.php'); // Proteção de login padrão do Tech Crew
include('db.php');   // Conexão à base de dados ($conexao)

$user_logado = $_SESSION['username'];
$cargo_logado = $_SESSION['role'];

// 1. PROCESSAR MUTAÇÃO DE ESTADO (Desativar ou Reativar Aluno)
if (isset($_GET['alterar_estado'])) {
    $id_aluno = intval($_GET['alterar_estado']);
    $novo_estado = intval($_GET['status']);
    
    $query_update = "UPDATE alunos SET estado = $novo_estado WHERE id = $id_aluno";
    if (mysqli_query($conexao, $query_update)) {
        if (function_exists('registarAtividade')) {
            $msg_log = ($novo_estado == 0) ? "Arquivou o aluno ID #$id_aluno" : "Reativou o aluno ID #$id_aluno";
            registarAtividade('ALTEROU', 'alunos.php', $msg_log);
        }
        header("Location: alunos.php");
        exit();
    }
}

// 2. PUXAR LISTA 1: ASSOCIADOS ATUAIS (estado = 1)
$query_ativos = "SELECT * FROM alunos WHERE estado = 1 ORDER BY nome ASC";
$result_ativos = mysqli_query($conexao, $query_ativos);
$total_ativos = mysqli_num_rows($result_ativos);

// 3. PUXAR LISTA 2: HISTÓRICO / EX-ASSOCIADOS (estado = 0)
$query_inativos = "SELECT * FROM alunos WHERE estado = 0 ORDER BY nome ASC";
$result_inativos = mysqli_query($conexao, $query_inativos);
$total_inativos = mysqli_num_rows($result_inativos);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Relatório de Utentes</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d; --cyan: #00ffff; --yellow: #ffe600; --red: #ff3b3b;
            --border: rgba(0, 255, 157, 0.2); --font: 'Courier New', monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.12; pointer-events: none; }
        
        body::before {
            content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 157, 0.015) 1px, transparent 1px),
                linear-gradient(rgba(0, 255, 157, 0.012) 50%, rgba(0, 0, 0, 0.18) 50%);
            background-size: 30px 30px, 100% 4px; z-index: 10; opacity: 0.6; pointer-events: none !important;
        }

        .container { padding: 20px; max-width: 1400px; margin: 0 auto; }
        .user-hud { background: rgba(0, 255, 102, 0.05); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Estilo Caderno / Relatório Técnico */
        .report-section { background: rgba(5, 5, 5, 0.95); border: 1px solid var(--border); border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 0 15px rgba(0,255,157,0.05); }
        .report-section.archive { border-color: rgba(0, 217, 255, 0.3); }
        
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 20px; }
        .archive .report-header { border-bottom-color: rgba(0, 217, 255, 0.3); }

        .terminal-table { width: 100%; border-collapse: collapse; background: rgba(2,2,2,0.6); }
        .terminal-table th { background: rgba(0, 255, 157, 0.12); color: #fff; border: 1px solid var(--border); padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; }
        .archive .terminal-table th { background: rgba(0, 217, 255, 0.12); border-color: rgba(0, 217, 255, 0.2); }
        .terminal-table td { border: 1px solid rgba(0, 255, 157, 0.15); padding: 12px; font-size: 13px; color: #cbd5e1; }
        .archive .terminal-table td { border-color: rgba(0, 217, 255, 0.15); }
        .terminal-table tr:hover { background: rgba(0, 255, 157, 0.04); }

        .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-active { background: rgba(0,255,157,0.15); color: var(--neon); border: 1px solid var(--neon); }
        .badge-inactive { background: rgba(0,217,255,0.15); color: var(--cyan); border: 1px solid var(--cyan); }

        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 6px 14px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px; font-family: var(--font); }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
        .btn-archive { color: var(--cyan); border-color: rgba(0, 217, 255, 0.4); background: rgba(0, 217, 255, 0.05); }
        .btn-archive:hover { background: var(--cyan); color: #000; box-shadow: 0 0 10px var(--cyan); }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>

    <div class="container">
        <div class="user-hud">
            <div>UTENTES // DIRETORIA: <strong style="color:#fff; text-transform:uppercase;"><?php echo $user_logado; ?></strong></div>
            <div>
                <a href="registo_aluno.php" class="btn">[ + REGISTAR NOVO UTENTE ]</a>
                <a href="index.php" class="btn">➔ [ PAINEL DE CONTROLO ]</a>
            </div>
        </div>

        <div class="report-section">
            <div class="report-header">
                <div>
                    <h2 style="margin:0; font-size:20px; color:#fff;">LIVE_RECORDS // UTENTES_ATIVOS</h2>
                    <p style="margin:5px 0 0 0; color:#aaa; font-size:12px;">Lista de alunos, professores e turmas com autorização operacional corrente.</p>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 11px; color: var(--neon);">CONTINGENTE ATUAL</span>
                    <h3 style="margin:0; font-size:24px; color:#fff;"><?php echo $total_ativos; ?> <span style="font-size:14px; color:#888;">Ativos</span></h3>
                </div>
            </div>

            <table class="terminal-table">
                <thead>
                    <tr>
                        <th>[ ID ]</th>
                        <th>[ NOME DO UTENTE ]</th>
                        <th>[ TURMA / CARGO ]</th>
                        <th>[ PROCESSO ]</th>
                        <th>[ STATUS ]</th>
                        <th style="text-align: center;">[ MUDAR MATRIZ ]</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($total_ativos > 0) {
                        while($row = mysqli_fetch_assoc($result_ativos)) {
                            echo "<tr>";
                            echo "<td style='font-weight:bold; color:var(--neon);'>#" . $row['id'] . "</td>";
                            echo "<td style='font-weight:bold; color:#fff;'>" . htmlspecialchars($row['nome']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['turma'] ?? $row['cargo'] ?? 'N/A') . "</td>";
                            echo "<td style='color:var(--yellow);'>" . htmlspecialchars($row['num_processo'] ?? $row['processo'] ?? '-') . "</td>";
                            echo "<td><span class='badge badge-active'>ATIVO</span></td>";
                            echo "<td style='text-align:center;'>
                                    <a href='alunos.php?alterar_estado=" . $row['id'] . "&status=0' class='btn btn-archive' style='padding:3px 8px; font-size:11px;' onclick='return confirm(\"Deseja arquivar este utilizador e passá-lo para o histórico?\")'>[ ARQUIVAR ]</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color:#666;'>Nenhum utente ativo registado no sistema.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="report-section archive">
            <div class="report-header">
                <div>
                    <h2 style="margin:0; font-size:20px; color:var(--cyan);">ARCHIVE_RECORDS // EX_ASSOCIADOS</h2>
                    <p style="margin:5px 0 0 0; color:#aaa; font-size:12px;">Histórico de utentes antigos, contas desativadas ou turmas de anos letivos anteriores.</p>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 11px; color: var(--cyan);">REPOSITÓRIO ADORMECIDO</span>
                    <h3 style="margin:0; font-size:24px; color:#fff;"><?php echo $total_inativos; ?> <span style="font-size:14px; color:#888;">Arquivados</span></h3>
                </div>
            </div>

            <table class="terminal-table">
                <thead>
                    <tr>
                        <th>[ ID ]</th>
                        <th>[ NOME DO UTENTE ]</th>
                        <th>[ TURMA / CARGO ]</th>
                        <th>[ PROCESSO ]</th>
                        <th>[ STATUS ]</th>
                        <th style="text-align: center;">[ MUDAR MATRIZ ]</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($total_inativos > 0) {
                        while($row = mysqli_fetch_assoc($result_inativos)) {
                            echo "<tr>";
                            echo "<td style='font-weight:bold; color:var(--cyan);'>#" . $row['id'] . "</td>";
                            echo "<td style='color:#aaa; text-decoration: line-through;'>" . htmlspecialchars($row['nome']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['turma'] ?? $row['cargo'] ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($row['num_processo'] ?? $row['processo'] ?? '-') . "</td>";
                            echo "<td><span class='badge badge-inactive'>HISTÓRICO</span></td>";
                            echo "<td style='text-align:center;'>
                                    <a href='alunos.php?alterar_estado=" . $row['id'] . "&status=1' class='btn' style='padding:3px 8px; font-size:11px;' onclick='return confirm(\"Reativar utente e devolvê-lo ao contingente atual?\")'>[ REATIVAR ]</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color:#555;'>O arquivo histórico está limpo de momento.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        const canvas = document.getElementById("matrix-canvas"); const ctx = canvas.getContext("2d");
        function res(){ canvas.width = window.innerWidth; canvas.height = window.innerHeight; } res();
        window.addEventListener("resize", res);
        const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""); const fontSize = 16; const cols = canvas.width / fontSize;
        const drops = Array(Math.floor(cols)).fill(1);
        function draw(){ ctx.fillStyle = "rgba(2,2,2,0.05)"; ctx.fillRect(0,0,canvas.width,canvas.height); ctx.fillStyle = "#00ff9d"; ctx.font = fontSize + "px monospace";
        for(let i=0;i<drops.length;i++){ const txt = chars[Math.floor(Math.random()*chars.length)]; ctx.fillText(txt, i*fontSize, drops[i]*fontSize); if(drops[i]*fontSize > canvas.height && Math.random() > 0.975) drops[i]=0; drops[i]++; } }
        setInterval(draw, 35);
    </script>
</body>
</html>