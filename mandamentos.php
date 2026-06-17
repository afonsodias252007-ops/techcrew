<?php
include('auth.php'); // Proteção de login padrão do Tech Crew
$user_logado = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Os 10 Mandamentos</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d;
            --muted-neon: #7df7af;
            --border: rgba(0, 255, 157, 0.25);
            --font: 'Courier New', Courier, monospace;
        }

        body {
            margin: 0;
            font-family: var(--font);
            background: #020202;
            color: var(--neon);
            overflow-x: hidden;
            position: relative;
        }

        /* Canvas Matrix de Fundo */
        #matrix-canvas {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -2; opacity: 0.08; pointer-events: none;
        }

        /* Efeito Scanline CRT */
        body::before {
            content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 157, 0.015) 1px, transparent 1px),
                linear-gradient(rgba(0, 255, 157, 0.012) 50%, rgba(0, 0, 0, 0.18) 50%);
            background-size: 30px 30px, 100% 4px;
            z-index: 10; opacity: 0.6; pointer-events: none;
        }

        .container {
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* HUD Superior */
        .user-hud {
            background: rgba(0, 255, 102, 0.04);
            border: 1px solid var(--border);
            padding: 15px; border-radius: 8px; margin-bottom: 30px;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* Painel dos Mandamentos */
        .mandamentos-panel {
            background: rgba(5, 5, 5, 0.95);
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 0 30px rgba(0, 255, 157, 0.1);
        }

        .main-title {
            text-align: center;
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 28px;
            color: #fff;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .sub-title {
            text-align: center;
            color: var(--neon);
            font-size: 36px;
            margin: 0 0 10px 0;
            font-weight: bold;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(0, 255, 157, 0.4);
        }

        .meta-tags {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 11px;
            color: #666;
            margin-bottom: 30px;
        }

        .system-init {
            color: #888;
            font-size: 13px;
            margin-bottom: 25px;
            border-bottom: 1px dashed var(--border);
            padding-bottom: 10px;
        }

        /* Grelha dos Itens */
        .mandamentos-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .mandamento-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 10px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .mandamento-item:hover {
            background: rgba(0, 255, 157, 0.03);
        }

        /* Enumeradores */
        .item-number {
            font-size: 20px;
            font-weight: bold;
            color: var(--neon);
            min-width: 35px;
        }

        .item-content {
            flex: 1;
            line-height: 1.5;
        }

        .item-title {
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            margin: 0 0 4px 0;
        }

        .item-desc {
            font-size: 14px;
            color: #cbd5e1;
            margin: 0;
        }

        .footer-guide {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .btn {
            background: rgba(0, 255, 157, 0.1);
            color: var(--neon);
            border: 1px solid rgba(0, 255, 157, 0.4);
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn:hover {
            background: var(--neon);
            color: #000;
            box-shadow: 0 0 10px var(--neon);
        }
    </style>
</head>
<body>

    <canvas id="matrix-canvas"></canvas>

    <div class="container">
        <div class="user-hud">
            <div>TERMINAL // PROTOCOLO_DE_CONDUTA</div>
            <a href="info.php" class="btn">➔ [ VOLTAR AO DASHBOARD ]</a>
        </div>

        <div class="mandamentos-panel">
            <h1 class="main-title">Os 10 Mandamentos</h1>
            <h2 class="sub-title">Do Estagiário</h2>
            
            <div class="meta-tags">
                <span>[#ESTAGIARIO_GUIDE]</span>
                <span>[#TECH_CREW_AETL]</span>
            </div>

            <div class="system-init">> [SYSTEM LOG] Init...</div>

            <div class="mandamentos-list">
                <div class="mandamento-item">
                    <div class="item-number">1.</div>
                    <div class="item-content">
                        <h3 class="item-title">Sê Pontual</h3>
                        <p class="item-desc">Chegar a horas é o primeiro sinal de respeito.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">2.</div>
                    <div class="item-content">
                        <h3 class="item-title">Respeita o espaço</h3>
                        <p class="item-desc">Deixa tudo melhor do que encontraste.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">3.</div>
                    <div class="item-content">
                        <h3 class="item-title">Pergunta sem medo</h3>
                        <p class="item-desc">Quem pergunta aprende mais rápido.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">4.</div>
                    <div class="item-content">
                        <h3 class="item-title">Ouve com atenção</h3>
                        <p class="item-desc">Antes de fazer, garante que percebeste.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">5.</div>
                    <div class="item-content">
                        <h3 class="item-title">Trabalha em equipa</h3>
                        <p class="item-desc">Sozinho vais mais rápido, juntos vamos mais longe.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">6.</div>
                    <div class="item-content">
                        <h3 class="item-title">Organiza-te</h3>
                        <p class="item-desc">Uma mente organizada produz melhor.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">7.</div>
                    <div class="item-content">
                        <h3 class="item-title">Erra e assume</h3>
                        <p class="item-desc">O erro é parte do processo - aprende com ele.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">8.</div>
                    <div class="item-content">
                        <h3 class="item-title">Tem iniciativa</h3>
                        <p class="item-desc">Não esperes sempre ordens - observa e age.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">9.</div>
                    <div class="item-content">
                        <h3 class="item-title">Não desistas</h3>
                        <p class="item-desc">A persistência vale mais que o talento.</p>
                    </div>
                </div>

                <div class="mandamento-item">
                    <div class="item-number">10.</div>
                    <div class="item-content">
                        <h3 class="item-title">Dá sempre o teu melhor</h3>
                        <p class="item-desc">Mesmo nas tarefas mais simples.</p>
                    </div>
                </div>
            </div>

            <div class="footer-guide">
                <span style="font-weight: bold; color: #fff;">AETL Tech Crew</span>
                <span style="color: var(--muted-neon);">// [GUIDE] Um guia para o sucesso profissional</span>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById("matrix-canvas"); const ctx = canvas.getContext("2d");
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; } resize();
        window.addEventListener("resize", resize);
        const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""); const fontSize = 16; const cols = canvas.width / fontSize;
        const drops = Array(Math.floor(cols)).fill(1);
        function drawMatrix() {
            ctx.fillStyle = "rgba(2, 2, 2, 0.05)"; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = "#00ff9d"; ctx.font = fontSize + "px monospace";
            for (let i = 0; i < drops.length; i++) {
                const text = chars[Math.floor(Math.random() * chars.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
        }
        setInterval(drawMatrix, 35);
    </script>
</body>
</html>