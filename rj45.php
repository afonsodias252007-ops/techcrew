<?php
include('auth.php'); // Proteção de login padrão do Tech Crew
$user_logado = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Sequência de Cabos RJ45</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d;
            --cyan: #00ffff;
            --border: rgba(0, 255, 157, 0.2);
            --font: 'Courier New', monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.08; pointer-events: none; }
        .container { padding: 20px; max-width: 1100px; margin: 0 auto; }
        
        /* HUD Superior */
        .user-hud { background: rgba(0, 255, 102, 0.05); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        
        .main-panel { background: rgba(5,5,5,0.95); border: 1px solid var(--border); padding: 30px; border-radius: 8px; box-shadow: 0 0 20px rgba(0, 255, 157, 0.05); }
        .panel-title { margin-top: 0; text-align: center; color: #fff; text-transform: uppercase; letter-spacing: 2px; border-bottom: 1px dashed var(--border); padding-bottom: 15px; margin-bottom: 30px; }
        
        .split-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 30px; }
        .standard-box { background: rgba(0, 0, 0, 0.6); border: 1px solid var(--border); border-radius: 6px; padding: 20px; }
        .standard-title { font-size: 18px; font-weight: bold; color: #fff; margin-top: 0; margin-bottom: 15px; text-transform: uppercase; border-bottom: 1px solid rgba(0, 255, 157, 0.1); padding-bottom: 8px; text-align: center; }
        
        /* Lista de Cores do Cabo */
        .color-list { display: flex; flex-direction: column; gap: 8px; padding: 0; margin: 0; list-style: none; }
        .color-item { display: flex; align-items: center; gap: 15px; padding: 6px 12px; background: rgba(5,5,5,0.8); border: 1px solid rgba(255,255,255,0.05); border-radius: 4px; font-size: 13px; color: #cbd5e1; }
        
        /* Amostras de Cor (Sem Emojis) */
        .color-sample { width: 14px; height: 14px; border-radius: 3px; border: 1px solid rgba(255,255,255,0.2); display: inline-block; }
        .pin-num { font-weight: bold; color: var(--neon); min-width: 45px; }

        /* Classes de Cor Sólida e Listrada */
        .c-br-vd { background: linear-gradient(45deg, #fff 50%, #00ff00 50%); }
        .c-vd { background: #00ff00; }
        .c-br-lr { background: linear-gradient(45deg, #fff 50%, #ff7700 50%); }
        .c-lr { background: #ff7700; }
        .c-br-az { background: linear-gradient(45deg, #fff 50%, #0000ff 50%); }
        .c-az { background: #0000ff; }
        .c-br-ca { background: linear-gradient(45deg, #fff 50%, #8b5a2b 50%); }
        .c-ca { background: #8b5a2b; }

        /* Painel Informativo Técnico */
        .tech-info { background: rgba(0, 217, 255, 0.02); border: 1px dashed rgba(0, 217, 255, 0.3); border-radius: 6px; padding: 20px; color: #cbd5e1; font-size: 13px; line-height: 1.6; }
        .tech-info h4 { margin-top: 0; color: var(--cyan); text-transform: uppercase; font-size: 14px; margin-bottom: 10px; }
        
        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px; font-family: var(--font); text-transform: uppercase; }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
        
        @media (max-width: 768px) { .split-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="container">
        
        <div class="user-hud">
            <div>CRIMPING_REFERENCE // RJ45_MATRIX</div>
            <a href="info.php" class="btn">➔ [ VOLTAR AO REPOSITÓRIO ]</a>
        </div>

        <div class="main-panel">
            <h2 class="panel-title">[ PROTOCOLO DE CRIMPAÇÃO // CABO DE REDE RJ45 ]</h2>
            
            <div class="split-grid">
                
                <div class="standard-box">
                    <div class="standard-title">Padrão T568A</div>
                    <ul class="color-list">
                        <li class="color-item"><span class="pin-num">PIN 1</span> <span class="color-sample c-br-vd"></span> Branco e Verde</li>
                        <li class="color-item"><span class="pin-num">PIN 2</span> <span class="color-sample c-vd"></span> Verde</li>
                        <li class="color-item"><span class="pin-num">PIN 3</span> <span class="color-sample c-br-lr"></span> Branco e Laranja</li>
                        <li class="color-item"><span class="pin-num">PIN 4</span> <span class="color-sample c-az"></span> Azul</li>
                        <li class="color-item"><span class="pin-num">PIN 5</span> <span class="color-sample c-br-az"></span> Branco e Azul</li>
                        <li class="color-item"><span class="pin-num">PIN 6</span> <span class="color-sample c-lr"></span> Laranja</li>
                        <li class="color-item"><span class="pin-num">PIN 7</span> <span class="color-sample c-br-ca"></span> Branco e Castanho</li>
                        <li class="color-item"><span class="pin-num">PIN 8</span> <span class="color-sample c-ca"></span> Castanho</li>
                    </ul>
                </div>

                <div class="standard-box">
                    <div class="standard-title">Padrão T568B</div>
                    <ul class="color-list">
                        <li class="color-item"><span class="pin-num">PIN 1</span> <span class="color-sample c-br-lr"></span> Branco e Laranja</li>
                        <li class="color-item"><span class="pin-num">PIN 2</span> <span class="color-sample c-lr"></span> Laranja</li>
                        <li class="color-item"><span class="pin-num">PIN 3</span> <span class="color-sample c-br-vd"></span> Branco e Verde</li>
                        <li class="color-item"><span class="pin-num">PIN 4</span> <span class="color-sample c-az"></span> Azul</li>
                        <li class="color-item"><span class="pin-num">PIN 5</span> <span class="color-sample c-br-az"></span> Branco e Azul</li>
                        <li class="color-item"><span class="pin-num">PIN 6</span> <span class="color-sample c-vd"></span> Verde</li>
                        <li class="color-item"><span class="pin-num">PIN 7</span> <span class="color-sample c-br-ca"></span> Branco e Castanho</li>
                        <li class="color-item"><span class="pin-num">PIN 8</span> <span class="color-sample c-ca"></span> Castanho</li>
                    </ul>
                </div>

            </div>

            <div class="tech-info">
                <h4>Diretrizes de Aplicação de Hardware</h4>
                <strong>Cabo Direto (Straight-Through):</strong> Utiliza o mesmo padrão (geralmente T568B) em ambas as extremidades. Serve para ligar dispositivos diferentes, como por exemplo um Computador a um Switch ou a um Router.<br><br>
                <strong>Cabo Cruzado (Crossover):</strong> Utiliza o padrão T568A numa extremidade e o padrão T568B na outra extremidade. Serve para ligar dispositivos iguais entre si, como por exemplo ligar dois Computadores diretamente sem Switch.<br><br>
                <small style="color:var(--cyan);">* Orientação física de crimpagem: Segura na ficha RJ45 com a patilha de plástico virada para baixo e os contactos de cobre virados para cima. O PIN 1 começa sempre da esquerda para a direita.</small>
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