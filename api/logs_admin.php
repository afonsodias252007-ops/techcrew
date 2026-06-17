<?php
include('auth.php');
include('db.php');

// Segurança: Apenas Administradores
verificarAdmin();

$user_logado = $_SESSION['username'];

// Filtro de Pesquisa
$filtro = isset($_GET['pesquisa']) ? mysqli_real_escape_string($conexao, trim($_GET['pesquisa'])) : '';

if (!empty($filtro)) {
    $query = "SELECT * FROM logs_atividades 
              WHERE utilizador LIKE '%$filtro%' 
                 OR acao LIKE '%$filtro%' 
                 OR pagina LIKE '%$filtro%' 
                 OR descricao LIKE '%$filtro%' 
              ORDER BY data_hora DESC";
} else {
    $query = "SELECT * FROM logs_atividades ORDER BY data_hora DESC";
}

$resultado_logs = mysqli_query($conexao, $query);

// Contadores para o HUD Lateral
$total_acoes = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) as qtd FROM logs_atividades"))['qtd'];
$total_insercoes = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) as qtd FROM logs_atividades WHERE acao='ADICIONOU'"))['qtd'];
$total_remocoes  = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) as qtd FROM logs_atividades WHERE acao='REMOVEU'"))['qtd'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Auditoria Global do Sistema</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d;
            --cyan: #00ffff;
            --yellow: #ffe600;
            --red: #ff3b3b;
            --border: rgba(0, 255, 157, 0.2);
            --font: 'Courier New', Courier, monospace;
        }

        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.12; pointer-events: none; }
        .audit-layout { display: flex; gap: 25px; margin-top: 30px; }
        .panel-stats { flex: 1; min-width: 300px; }
        .panel-timeline { flex: 3; }
        .btn-voltar { position: fixed; top: 20px; left: 20px; background: rgba(5, 5, 5, 0.9); border: 1px solid var(--neon); color: var(--neon); padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: bold; z-index: 9999; }
        .hud-indicador { background: rgba(5,5,5,0.9); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        
        .timeline-card { background: rgba(5, 5, 5, 0.85); border: 1px solid rgba(0, 255, 102, 0.1); padding: 18px; border-radius: 8px; margin-bottom: 15px; }
        
        /* Cores das bordas dependendo do tipo de ação */
        .card-adicionou { border-left: 4px solid var(--neon); }
        .card-editou { border-left: 4px solid var(--yellow); }
        .card-removeu { border-left: 4px solid var(--red); }

        .tag-acao { padding: 2px 6px; font-size: 10px; font-weight: bold; border-radius: 3px; text-transform: uppercase; }
        .tag-adicionou { background: rgba(0,255,157,0.2); color: var(--neon); border: 1px solid var(--neon); }
        .tag-editou { background: rgba(255,230,0,0.2); color: var(--yellow); border: 1px solid var(--yellow); }
        .tag-removeu { background: rgba(255,59,59,0.2); color: var(--red); border: 1px solid var(--red); }

        .timeline-meta { display: flex; justify-content: space-between; font-size: 12px; border-bottom: 1px dashed rgba(0, 255, 102, 0.1); padding-bottom: 8px; margin-bottom: 12px; }
        .search-hud { background: rgba(0, 255, 102, 0.02); padding: 12px 18px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>

    <canvas id="matrix-canvas"></canvas>
    <a href="index.php" class="btn-voltar">⬅ DASHBOARD</a>

    <div class="container" style="max-width: 1400px; margin-top: 40px; padding: 20px;">
        
        <div class="terminal-header" style="margin-bottom: 25px;">
            <h1>GLOBAL_SYSTEM_AUDIT // SECURITY_FEED</h1>
            <p>Registo permanente de alterações. Monitorização de injeções, modificações e destruição de nós de dados.</p>
        </div>

        <div class="search-hud">
            <span style="font-size:13px;">Terminal Administrativo de Auditoria: <strong style="color:#fff; text-transform:uppercase;"><?php echo $user_logado; ?></strong></span>
            <form method="GET" action="logs_admin.php" style="display:flex; gap:10px;">
                <input type="text" name="pesquisa" placeholder="Buscar por utilizador, ação, página..." class="btn" style="background:#000; color:#fff; text-align:left; width:300px; cursor:text;" value="<?php echo htmlspecialchars($filtro); ?>" autocomplete="off">
                <button type="submit" class="btn" style="border-color:var(--cyan); color:var(--cyan);">[ PESQUISAR ]</button>
                <?php if(!empty($filtro)): ?>
                    <a href="logs_admin.php" class="btn" style="border-color:var(--red); color:var(--red);">[ LIMPAR ]</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="audit-layout">
            
            <div class="panel-stats">
                <div class="hud-indicador">
                    <h4 style="margin:0 0 5px 0; color:var(--cyan); font-size:12px;">[ TOTAL DE OPERAÇÕES ]</h4>
                    <span style="font-size:24px; font-weight:bold; color:#fff;"><?php echo $total_acoes; ?></span> Eventos
                </div>
                <div class="hud-indicador">
                    <h4 style="margin:0 0 5px 0; color:var(--neon); font-size:12px;">[ NOVOS REGISTOS (INSERT) ]</h4>
                    <span style="font-size:24px; font-weight:bold; color:#fff;"><?php echo $total_insercoes; ?></span> Execuções
                </div>
                <div class="hud-indicador">
                    <h4 style="margin:0 0 5px 0; color:var(--red); font-size:12px;">[ ELIMINAÇÕES (DELETE) ]</h4>
                    <span style="font-size:24px; font-weight:bold; color:#fff;"><?php echo $total_remocoes; ?></span> Remoções
                </div>
            </div>

            <div class="panel-timeline">
                <div class="card-painel" style="background: rgba(5,5,5,0.9); border:1px solid var(--border); padding:20px; border-radius:8px;">
                    <h2 style="font-size:15px; color:#fff; margin-bottom:20px;">[ LOG_STREAM // HISTÓRICO GLOBAL INALTERÁVEL ]</h2>

                    <div style="max-height: 650px; overflow-y: auto; padding-right: 5px;">
                        <?php if ($resultado_logs && mysqli_num_rows($resultado_logs) > 0): ?>
                            <?php while ($log = mysqli_fetch_assoc($resultado_logs)): 
                                $acao_classe = strtolower($log['acao']); // 'adicionou', 'editou' ou 'removeu'
                            ?>
                            <div class="timeline-card card-<?php echo $acao_classe; ?>">
                                <div class="timeline-meta">
                                    <span>
                                        <span class="tag-acao tag-<?php echo $acao_classe; ?>"><?php echo $log['acao']; ?></span>
                                        <span style="margin-left:10px; color:#aaa;">PÁGINA: <strong><?php echo htmlspecialchars($log['pagina']); ?></strong></span>
                                    </span>
                                    <span>
                                        🧑‍💻 UTILIZADOR: <strong style="color:#fff; text-transform:uppercase;"><?php echo htmlspecialchars($log['utilizador']); ?></strong>
                                    </span>
                                    <span style="color:var(--cyan);">
                                        <?php echo date('d/m/Y H:i:s', strtotime($log['data_hora'])); ?>
                                    </span>
                                </div>
                                <div style="color: #e2e8f0; font-size: 13px; font-family: monospace; line-height: 1.5;">
                                    <?php echo htmlspecialchars($log['descricao']); ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding:40px; text-align:center; color:var(--border); font-style:italic;">Nenhum evento capturado nos logs de segurança do servidor.</div>
                        <?php endif; ?>
                    </div>
                </div>
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