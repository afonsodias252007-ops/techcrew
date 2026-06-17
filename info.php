<?php
include('auth.php'); // Proteção de login padrão do Tech Crew
$user_logado = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Hub Informativo</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d;
            --cyan: #00ffff;
            --yellow: #ffe600;
            --border: rgba(0, 255, 157, 0.2);
            --font: 'Courier New', monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.08; pointer-events: none; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        
        /* HUD Superior */
        .user-hud { background: rgba(0, 255, 102, 0.05); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Cabeçalho do Index Secundário */
        .hub-header { text-align: center; margin-bottom: 35px; background: rgba(5,5,5,0.9); border: 1px solid var(--border); padding: 20px; border-radius: 8px; }
        .hub-header h1 { margin: 0 0 10px 0; font-size: 26px; color: #fff; letter-spacing: 2px; }
        .hub-header p { margin: 0; font-size: 13px; color: #888; }

        /* Grelha de Módulos Informativos */
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
        
        /* Cartão de Módulo */
        .info-card { background: rgba(5,5,5,0.95); border: 1px solid var(--border); border-radius: 8px; padding: 25px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; }
        .info-card:hover { border-color: var(--neon); box-shadow: 0 0 15px rgba(0, 255, 157, 0.1); background: rgba(0, 255, 157, 0.01); }
        
        /* Variações de cor para cartões dinâmicos */
        .card-cyan { border-color: rgba(0, 217, 255, 0.3); }
        .card-cyan:hover { border-color: var(--cyan); box-shadow: 0 0 15px rgba(0, 217, 255, 0.1); }
        .card-yellow { border-color: rgba(255, 230, 0, 0.3); }
        .card-yellow:hover { border-color: var(--yellow); box-shadow: 0 0 15px rgba(255, 230, 0, 0.1); }

        .card-title { font-size: 18px; font-weight: bold; color: #fff; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 10px; }
        .card-desc { font-size: 13px; color: #cbd5e1; line-height: 1.5; margin: 0 0 20px 0; }
        
        /* Rodapé interno dos cartões */
        .card-footer { display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #555; border-top: 1px dashed rgba(0, 255, 157, 0.1); padding-top: 12px; }
        
        /* Botões */
        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 11px; font-family: var(--font); text-transform: uppercase; }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
        
        .btn-cyan { background: rgba(0, 217, 255, 0.1); color: var(--cyan); border-color: rgba(0, 217, 255, 0.4); }
        .btn-cyan:hover { background: var(--cyan); color: #000; box-shadow: 0 0 10px var(--cyan); }
        .btn-yellow { background: rgba(255, 230, 0, 0.1); color: var(--yellow); border-color: rgba(255, 230, 0, 0.4); }
        .btn-yellow:hover { background: var(--yellow); color: #000; box-shadow: 0 0 10px var(--yellow); }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="container">
        
        <div class="user-hud">
            <div>SECONDARY_INDEX // INFORMATION_DESK</div>
            <a href="index.php" class="btn">➔ [ VOLTAR AO MENU PRINCIPAL ]</a>
        </div>

        <div class="hub-header">
            <h1>[ REPOSITÓRIO INFORMATIVO CENTRAL ]</h1>
            <p>Diretrizes táticas, documentação operacional e canais oficiais da Tech Crew.</p>
        </div>

        <div class="info-grid">
    
    <div class="info-card">
        <div>
            <div class="card-title">Código de Conduta</div>
            <p class="card-desc">Consulta as diretrizes de comportamento essenciais para garantir o sucesso técnico e a postura adequada dentro do laboratório e da equipa.</p>
        </div>
        <div>
            <div class="card-footer">
                <span>TAG: #MANDAMENTOS</span>
                <a href="mandamentos.php" class="btn">[ ACEDER ]</a>
            </div>
        </div>
    </div>

    <div class="info-card">
        <div>
            <div class="card-title">Comunidade e Redes</div>
            <p class="card-desc">Acesso aos QR Codes e hiperligações diretas do servidor de Discord e do grupo oficial de WhatsApp para articulação em tempo real.</p>
        </div>
        <div>
            <div class="card-footer">
                <span>TAG: #COMUNICACAO</span>
                <a href="comunidade.php" class="btn">[ CONECTAR ]</a>
            </div>
        </div>
    </div>

    <div class="info-card">
        <div>
            <div class="card-title">Esquema de Cabos RJ45</div>
            <p class="card-desc">Esquema técnico detalhado com o mapeamento de pinos e sequências de cores dos padrões internacionais T568A e T568B para montagem de cabos de rede.</p>
        </div>
        <div>
            <div class="card-footer">
                <span>TAG: #HARDWARE_NET</span>
                <a href="rj45.php" class="btn">[ CONSULTAR ]</a>
            </div>
        </div>
    </div>

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