<?php 
include('auth.php'); // Proteção de login
include('db.php');

$query = "SELECT * FROM relatorios ORDER BY data_envio DESC";
$resultado = mysqli_query($conexao, $query);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            margin: 0;
            font-family: 'Courier New', Courier, monospace; /* Fonte estilo terminal */
            background: #020202;
            color: #00ff9d;
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
            opacity: 0.12; /* Deixa a animação suave para não atrapalhar a leitura */
            pointer-events: none !important; /* BLOQUEIA INTERCEÇÃO DE CLIQUES */
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
            pointer-events: none !important; /* BLOQUEIA INTERCEÇÃO DE CLIQUES */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        /* HUD Superior */
        .user-hud {
            background: rgba(3, 3, 5, 0.9);
            border: 1px solid rgba(0, 255, 157, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 15px rgba(0, 255, 157, 0.05);
        }

        .btn-logout {
            background: #ff3b3b;
            color: #fff;
            padding: 6px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            transition: 0.2s ease;
        }
        .btn-logout:hover {
            box-shadow: 0 0 12px #ff3b3b;
            background: #e03232;
        }

        /* Botão Estilizado do Website da Escola */
        .btn-escola {
            background: rgba(0, 217, 255, 0.1);
            color: #00ffff;
            border: 1px solid rgba(0, 217, 255, 0.4);
            padding: 6px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            transition: 0.2s ease;
        }
        .btn-escola:hover {
            background: #00ffff;
            color: #000;
            box-shadow: 0 0 15px rgba(0, 217, 255, 0.4);
        }

        /* Painel Central Protetor de Legibilidade */
        .card-painel {
            background: rgba(5, 5, 8, 0.88);
            border: 1px solid rgba(0, 255, 157, 0.15);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
        }

        /* --- CORREÇÃO E CENTRALIZAÇÃO DO LOGÓTIPO E TEXTOS --- */
        .terminal-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px dashed rgba(0, 255, 157, 0.2);
            padding-bottom: 25px;
        }

        .terminal-logo {
            max-width: 130px; /* Mantém exatamente a dimensão do teu print original */
            height: auto;
            margin-bottom: 15px;
            border: 1px solid rgba(0, 255, 157, 0.3);
            box-shadow: 0 0 12px rgba(0, 255, 157, 0.2);
        }

        .terminal-header h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            letter-spacing: 1.5px;
            text-shadow: 0 0 8px rgba(0, 255, 157, 0.5);
        }

        .terminal-header p {
            margin: 0;
            font-size: 14px;
            color: #7df7af;
        }

        /* --- ESTILIZAÇÃO GERAL DOS BOTÕES DE GRID --- */
        .btn {
            text-decoration: none !important;
            transition: all 0.25s ease;
            box-sizing: border-box;
            display: flex;
        }

        .btn:hover {
            border-color: #fff !important;
            transform: translateY(-2px);
        }

        /* Correção e Visual do Botão TRIPLO (Informação) */
        .btn-triple-span {
            grid-column: 1 / -1; /* Força o botão a ocupar a linha inteira horizontal de ponta a ponta */
            padding: 18px;
            background: rgba(0, 255, 157, 0.08) !important;
            border: 1px solid rgba(0, 255, 157, 0.4) !important;
            color: #00ff9d !important;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 3px;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .btn-triple-span:hover {
            background: #00ff9d !important;
            color: #000000 !important;
            box-shadow: 0 0 20px rgba(0, 255, 157, 0.4);
        }

        /* Botões Padrão de Serviço */
        .btn-primary:hover {
            background: rgba(0, 255, 102, 0.08) !important;
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.2);
        }

        /* Botões Administrativos Amarelos */
        .btn-warning {
            border: 1px solid rgba(255, 230, 0, 0.3) !important;
            color: #ffff00 !important;
            text-decoration: none;
        }
        .btn-warning:hover {
            background: rgba(255, 230, 0, 0.06) !important;
            box-shadow: 0 0 15px rgba(255, 230, 0, 0.2);
        }
    </style>
</head>
<body>

    <canvas id="matrix-canvas"></canvas>

    <div class="container">
        
        <div class="user-hud">
            <div>
                <span>AGENTE OPERACIONAL: <strong><?php echo strtoupper($_SESSION['username']); ?></strong></span>
                <span style="margin-left: 20px; color: #ffe600;">AUTORIZAÇÃO: <strong><?php echo strtoupper($_SESSION['role']); ?></strong></span>
            </div>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="https://terrasdelarus.edu.pt/" target="_blank" class="btn-escola">[ WEBSITE_ESCOLA ]</a>
                <a href="logout.php" class="btn-logout">[ TERMINAR_SESSÃO ➔ ]</a>
            </div>
        </div>

        <div class="card-painel">
            
            <div class="terminal-header">
                <img src="logo.jpg" alt="Tech Crew Logo" class="terminal-logo">
                <div>
                    <h1>MAIN_CONTROL_PANEL // TECH CREW</h1>
                    <p>Plataforma de Gestão Operacional e Monitorização de Infraestruturas</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">
                
                <a href="info.php" class="btn btn-triple-span">
                    [ I N F O R M A Ç Ã O ]
                </a>

                <a href="ver_montagem.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ Tabela de montagem ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Montagem dos pcs para as provas.</p>
                </a>

                <a href="relatorio.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ RELATÓRIOS ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Logs diários e consolidados semanais.</p>
                </a>

                <a href="calendario.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ Calendario ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Calendario de eventos.</p>
                </a>

                <a href="projetores.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ PROJETORES ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Inventário e estado das salas/blocos.</p>
                </a>

                <a href="emprestimos.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ EMPRÉSTIMOS ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Empréstimos de computadores</p>
                </a>

                <a href="pc.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ COMPUTADORES ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Controlo de hardware e comodatos.</p>
                </a>

                <a href="alunos.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ ALUNOS/PROFESSORES ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Gestão de alunos, turmas e professores.</p>
                </a>

                <a href="tarefas.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ DIRETIVAS ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Fila de tarefas pendentes e concluídas.</p>
                </a>

                <a href="links.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ LAUNCHPAD ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Atalhos externos e hiperligações úteis.</p>
                </a>

                <a href="mapa.php" class="btn btn-primary" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(0,255,102,0.04); border: 1px solid rgba(0,255,102,0.15); color: #00ff66;">
                    <h3 style="margin-bottom: 5px; margin-top: 0;">[ MAPS ]</h3>
                    <p style="font-size: 12px; color: #7df7af; margin: 0;">Acesso ao mapa escolar</p>
                </a>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="backup.php" class="btn btn-warning" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(255,230,0,0.02);">
                        <h3 style="margin-bottom: 5px; margin-top: 0;">[ BACKUPS ]</h3>
                        <p style="font-size: 12px; color: #fff; margin: 0;">Salvaguarda da BD e do sistema.</p>
                    </a>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="registo.php" class="btn btn-warning" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(255,230,0,0.02);">
                        <h3 style="margin-bottom: 5px; margin-top: 0;">[ Criar Conta ]</h3>
                        <p style="font-size: 12px; color: #fff; margin: 0;">Adicione o seu novo estagiário</p>
                    </a>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="logs_admin.php" class="btn btn-warning" style="height: auto; padding: 25px; flex-direction: column; align-items: flex-start; text-align: left; background: rgba(255,230,0,0.02);">
                        <h3 style="margin-bottom: 5px; margin-top: 0;">[ Logs ]</h3>
                        <p style="font-size: 12px; color: #fff; margin: 0;">Ver logs globais de auditoria</p>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById("matrix-canvas");
        const ctx = canvas.getContext("2d");

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        resizeCanvas();
        window.addEventListener("resize", resizeCanvas);

        const matrixChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZｦｧｨｩｪｫｬｭｮｯｰｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒMOYA";
        const charArray = matrixChars.split("");

        const fontSize = 16;
        const columns = canvas.width / fontSize;

        const dropPositions = [];
        for (let x = 0; x < columns; x++) {
            dropPositions[x] = 1;
        }

        function drawMatrix() {
            ctx.fillStyle = "rgba(2, 2, 2, 0.05)";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = "#00ff9d"; 
            ctx.font = fontSize + "px monospace";

            for (let i = 0; i < dropPositions.length; i++) {
                const text = charArray[Math.floor(Math.random() * charArray.length)];
                
                const x = i * fontSize;
                const y = dropPositions[i] * fontSize;

                ctx.fillText(text, x, y);

                if (y > canvas.height && Math.random() > 0.975) {
                    dropPositions[i] = 0;
                }
                dropPositions[i]++;
            }
        }
        setInterval(drawMatrix, 35);
    </script>
</body>
</html>