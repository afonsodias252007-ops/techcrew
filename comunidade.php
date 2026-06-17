<?php
include('auth.php'); // Proteção de login padrão do Tech Crew
$user_logado = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Links & Canais Oficiais</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --neon: #00ff9d;
            --cyan: #00ffff;
            --yellow: #ffe600;
            --border: rgba(0, 255, 157, 0.2);
            --font: 'Courier New', monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.10; pointer-events: none; }
        .container { padding: 20px; max-width: 1000px; margin: 0 auto; }
        .user-hud { background: rgba(0, 255, 102, 0.05); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        
        .main-panel { background: rgba(5,5,5,0.95); border: 1px solid var(--border); padding: 30px; border-radius: 8px; box-shadow: 0 0 20px rgba(0, 255, 157, 0.05); }
        .panel-title { margin-top: 0; text-align: center; color: #fff; text-transform: uppercase; letter-spacing: 2px; border-bottom: 1px dashed var(--border); padding-bottom: 15px; margin-bottom: 30px; }
        
        /* Grelha dos Canais (Lado a Lado) */
        .channels-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        
        /* Cartão de Comunidade */
        .channel-card { background: rgba(0, 0, 0, 0.6); border: 1px solid var(--border); border-radius: 6px; padding: 20px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; transition: all 0.3s ease; }
        .channel-card:hover { border-color: var(--neon); background: rgba(0, 255, 157, 0.02); box-shadow: 0 0 15px rgba(0, 255, 157, 0.1); }
        
        /* Variações de cor por plataforma */
        .card-whatsapp { border-color: rgba(0, 255, 157, 0.3); }
        .card-discord { border-color: rgba(0, 217, 255, 0.3); }
        .card-discord:hover { border-color: var(--cyan); box-shadow: 0 0 15px rgba(0, 217, 255, 0.1); }
        
        .platform-title { font-size: 20px; font-weight: bold; margin: 0 0 5px 0; text-transform: uppercase; }
        .whatsapp-text { color: var(--neon); }
        .discord-text { color: var(--cyan); }
        
        .platform-desc { font-size: 13px; color: #aaa; margin: 0 0 20px 0; height: 36px; line-height: 1.4; }
        
        /* Contentor do QR Code */
        .qrcode-wrapper { background: #fff; padding: 12px; border-radius: 6px; box-shadow: 0 0 10px rgba(0,0,0,0.5); display: inline-block; margin-bottom: 20px; }
        
        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px; font-family: var(--font); text-transform: uppercase; transition: all 0.2s; }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
        
        .btn-discord { background: rgba(0, 217, 255, 0.1); color: var(--cyan); border-color: rgba(0, 217, 255, 0.4); }
        .btn-discord:hover { background: var(--cyan); color: #000; box-shadow: 0 0 10px var(--cyan); }

        @media (max-width: 768px) { .channels-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="container">
        
        <div class="user-hud">
            <div>COMMUNICATION_HUB // LINK_STREAM</div>
            <a href="info.php" class="btn">➔ [ VOLTAR AO DASHBOARD ]</a>
        </div>

        <div class="main-panel">
            <h2 class="panel-title">[ CANAIS DE COMUNICAÇÃO // TECH CREW ]</h2>
            
            <div class="channels-grid">
                
                <div class="channel-card card-whatsapp">
                    <div class="platform-title whatsapp-text">🟢 WHATSAPP GROUP</div>
                    <p class="platform-desc">Acesso rápido para comunicações instantâneas, avisos de plantão e coordenação diária.</p>
                    
                    <div class="qrcode-wrapper">
                        <div id="qrcode-whatsapp"></div>
                    </div>
                    
                    <a href="https://chat.whatsapp.com/ByyOs3gxP8mCS9dKmY1PM0?mode=gi_t" target="_blank" class="btn">[ ENTRAR NO GRUPO ]</a>
                </div>

                <div class="channel-card card-discord">
                    <div class="platform-title discord-text">🔵 DISCORD SERVER</div>
                    <p class="platform-desc">Servidor central para reuniões de equipa, canais de voz, logs técnicos e partilha de ferramentas.</p>
                    
                    <div class="qrcode-wrapper">
                        <div id="qrcode-discord"></div>
                    </div>
                    
                    <a href="https://discord.gg/7apJShgwCH" target="_blank" class="btn btn-discord">[ ENTRAR NO SERVIDOR ]</a>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Inicialização automática dos QR Codes usando os links fornecidos
        window.addEventListener('DOMContentLoaded', () => {
            // QR Code do WhatsApp
            new QRCode(document.getElementById("qrcode-whatsapp"), {
                text: "https://chat.whatsapp.com/ByyOs3gxP8mCS9dKmY1PM0?mode=gi_t",
                width: 160,
                height: 160,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            // QR Code do Discord
            new QRCode(document.getElementById("qrcode-discord"), {
                text: "https://discord.gg/7apJShgwCH",
                width: 160,
                height: 160,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });

        // Matrix Rain Background Animation
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