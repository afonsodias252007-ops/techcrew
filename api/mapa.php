<?php
include('db.php');

// Puxar a contagem real de equipamentos por bloco para o HUD da Tech Crew
$contagem_blocos = [];
$res = mysqli_query($conexao, "SELECT bloco, COUNT(*) as total FROM projetores GROUP BY bloco");
if ($res) {
    while($row = mysqli_fetch_assoc($res)) {
        $contagem_blocos[$row['bloco']] = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Planta Digital Oficial</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
            background: #020202;
            color: #00ff9d;
            overflow-x: hidden;
        }

        /* Grelha HUD militar de fundo */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 157, 0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 157, 0.015) 1px, transparent 1px),
                linear-gradient(rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0.25) 50%);
            background-size: 30px 30px, 30px 30px, 100% 4px;
            pointer-events: none;
            z-index: -1;
        }

        .mapa-wrapper {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Contentor Master do Layout Reduzido e Compactado para Eliminar o Espaço Vazio */
        .canvas-escola {
            position: relative;
            width: 100%;
            height: 420px;
            background: rgba(2, 10, 5, 0.9);
            border: 2px solid #00ff9d;
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(0, 255, 157, 0.1), inset 0 0 40px rgba(0,0,0,0.9);
            margin-top: 20px;
            overflow: hidden;
        }

        /* Estilo Vetorial dos Edifícios */
        .bloco-oficial {
            position: absolute;
            background: rgba(5, 15, 8, 0.95);
            border: 2px solid rgba(0, 255, 157, 0.5);
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box;
        }

        .bloco-oficial::before {
            content: "";
            position: absolute;
            top: 4px; left: 4px; right: 4px; bottom: 4px;
            border: 1px dashed rgba(0, 255, 157, 0.15);
            pointer-events: none;
        }

        .bloco-oficial:hover {
            border-color: #00ff9d;
            background: rgba(0, 30, 15, 0.95);
            box-shadow: 0 0 25px rgba(0, 255, 157, 0.4), inset 0 0 15px rgba(0, 255, 157, 0.2);
            transform: scale(1.01);
            z-index: 10;
        }

        .bloco-oficial h3 {
            margin: 0;
            font-size: 15px;
            letter-spacing: 2px;
            color: #00ff9d;
            text-shadow: 0 0 5px rgba(0, 255, 157, 0.5);
        }

        .bloco-oficial .meta-infra {
            font-size: 10px;
            color: #fff;
            opacity: 0.7;
            margin-top: 4px;
            background: rgba(0,0,0,0.6);
            padding: 1px 6px;
            border-radius: 3px;
        }

        /* ========================================================
           ALINHAMENTO COMPACTO E EQUILIBRADO (TAMANHO UNIFORME)
           ======================================================== */
        
        /* PRIMEIRA LINHA ACADÉMICA (Altura: 32%) */
        .b-c { top: 8%;  left: 5%;  width: 24%; height: 32%; } /* C no Topo Esquerdo */
        .b-d { top: 8%;  left: 34%; width: 24%; height: 32%; } /* D no Topo Centro */
        
        /* SEGUNDA LINHA ACADÉMICA (Altura: 32%) */
        .b-b { top: 48%; left: 5%;  width: 24%; height: 32%; } /* B em Baixo à Esquerda */
        .b-a { top: 48%; left: 34%; width: 24%; height: 32%; } /* A em Baixo ao Centro */
        
        /* ALA DIREITA - BLOCO E COM REFEITÓRIO INTEGRADO */
        .b-e { 
            top: 28%; left: 64%; width: 31%; height: 32%; 
            border-color: rgba(255, 0, 102, 0.6); 
        } 
        .b-e h3 { color: #ff0066; text-shadow: 0 0 5px rgba(255, 0, 102, 0.5); }
        .b-e::before { border-color: rgba(255, 0, 102, 0.2); }

        /* PORTARIA (Centralizada na base por baixo do vão entre o Bloco B e A) */
        .b-portaria {
            top: 87%; left: 21%; width: 14%; height: 8%;
            border: 1px solid #ffcc00;
            background: rgba(20, 15, 0, 0.6);
            cursor: default;
        }
        .b-portaria h4 { margin: 0; color: #ffcc00; font-size: 10px; text-align: center; line-height: 32px; letter-spacing: 1px; }
        .b-portaria::before { border-color: rgba(255, 204, 0, 0.2); }

        /* ========================================================
           MODAL INTERATIVO (PISOS E VISUALIZADOR DE PLANTAS)
           ======================================================== */
        .modal-piso {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 99999;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: #040a06;
            border: 2px solid #00ff9d;
            border-radius: 8px;
            width: 95%;
            max-width: 850px;
            padding: 25px;
            box-shadow: 0 0 40px rgba(0, 255, 157, 0.3);
            box-sizing: border-box;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 255, 157, 0.2);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 18px;
            color: #00ff9d;
            letter-spacing: 2px;
        }

        .close-btn {
            background: transparent;
            border: 1px solid rgba(0, 255, 157, 0.4);
            color: #00ff9d;
            padding: 5px 15px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
        }

        .close-btn:hover {
            background: #00ff9d;
            color: #000;
        }

        .piso-selector-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .piso-button {
            background: rgba(0,0,0,0.6);
            border: 1px solid rgba(0, 255, 157, 0.2);
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }

        .piso-button:hover, .piso-button.active {
            border-color: #00ff9d;
            background: rgba(0, 255, 157, 0.08);
            box-shadow: 0 0 10px rgba(0, 255, 157, 0.2);
        }

        .piso-button h4 { margin: 0 0 3px 0; color: #00ff9d; font-size: 15px; }
        .piso-button p { margin: 0; font-size: 11px; opacity: 0.6; }

        .blueprint-viewer {
            width: 100%;
            height: 320px;
            background: #020503;
            border: 1px solid rgba(0, 255, 157, 0.15);
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            overflow: hidden;
            position: relative;
        }

        .blueprint-viewer img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: hue-rotate(45deg) brightness(0.9) contrast(1.1);
        }

        .blueprint-placeholder {
            color: #447a5e;
            font-size: 12px;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }

        .btn-auditoria-geral {
            display: block;
            width: 100%;
            text-align: center;
            background: #00ff9d;
            color: #000;
            padding: 12px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: 1px;
            font-size: 12px;
        }

        .btn-auditoria-geral:hover {
            background: #03c47a;
            box-shadow: 0 0 15px rgba(0, 255, 157, 0.4);
        }
    </style>
</head>
<body>

    <a href="index.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid #00ff9d; color: #00ff9d; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 13px; z-index: 99999; cursor: pointer !important;">⬅ Voltar ao Painel</a>

    <div class="mapa-wrapper">
        
        <div class="card-painel" style="padding: 20px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 20px; text-align: center; margin-top: 50px;">
            <h2 style="color: var(--neon); margin: 0 0 4px 0;">[ PLANTA_GERAL_OFICIAL_CRUZ_DE_PAU ]</h2>
            <p style="color: #aaa; font-size: 12px; margin: 0;">Clica num bloco para inspecionar os planos de cada andar. O inventário listará o bloco por completo.</p>
        </div>

        <div class="canvas-escola">
            
            <div class="bloco-oficial b-c" onclick="abrirModal('Bloco C', 'Salas de Aula / Teóricas')">
                <h3>BLOCO C</h3>
                <div class="meta-infra">Aparelhos: <?php echo $contagem_blocos['Bloco C'] ?? 0; ?></div>
            </div>

            <div class="bloco-oficial b-d" onclick="abrirModal('Bloco D', 'Salas de Aula / Oficinas / Informática')">
                <h3>BLOCO D</h3>
                <div class="meta-infra">Aparelhos: <?php echo $contagem_blocos['Bloco D'] ?? 0; ?></div>
            </div>

            <div class="bloco-oficial b-b" onclick="abrirModal('Bloco B', 'Salas de Aula / Serviços Técnicos')">
                <h3>BLOCO B</h3>
                <div class="meta-infra">Aparelhos: <?php echo $contagem_blocos['Bloco B'] ?? 0; ?></div>
            </div>

            <div class="bloco-oficial b-a" onclick="abrirModal('Bloco A', 'Salas de Aula / Laboratórios')">
                <h3>BLOCO A</h3>
                <div class="meta-infra">Aparelhos: <?php echo $contagem_blocos['Bloco A'] ?? 0; ?></div>
            </div>

            <div class="bloco-oficial b-e" onclick="abrirModal('Bloco E', 'Complexo Social, Reuniões e Refeitório Integrado')">
                <h3>BLOCO E / REFEITÓRIO</h3>
                <div class="meta-infra">Aparelhos: <?php echo ($contagem_blocos['Bloco E'] ?? 0) + ($contagem_blocos['Refeitório'] ?? 0); ?></div>
            </div>

            <div class="bloco-oficial b-portaria">
                <h4>PORTARIA</h4>
            </div>

        </div>

    </div>

    <div id="pisoModal" class="modal-piso">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalTitle">[ BLOCO ]</h2>
                    <div id="modalSub" style="font-size: 11px; color: #aaa; margin-top: 4px;">Setor Operacional Técnico</div>
                </div>
                <button class="close-btn" onclick="fecharModal()">X</button>
            </div>
            
            <div class="piso-selector-grid">
                <div id="btnPiso1" class="piso-button" onclick="mudarAndarVisualizador(1)">
                    <h4>1º ANDAR</h4>
                    <p>Piso Térreo / Rés-do-Chão (Ex: Sala 34)</p>
                </div>
                <div id="btnPiso2" class="piso-button" onclick="mudarAndarVisualizador(2)">
                    <h4>2º ANDAR</h4>
                    <p>Piso Superior / 1º Piso</p>
                </div>
            </div>

            <div class="blueprint-viewer">
                <img id="blueprintImage" src="" alt="Planta do Piso" style="display:none;">
                <div id="blueprintPlaceholder" class="blueprint-placeholder">
                    Nenhuma planta gráfica disponível para este andar.
                </div>
            </div>

            <a id="linkAuditoriaGeral" href="#" class="btn-auditoria-geral">
                📋 VER EQUIPAMENTOS DESTE BLOCO NA BASE DE DADOS
            </a>
        </div>
    </div>

    <script>
        let blocoAtual = "";

        const mapasFicheiros = {
            "Bloco A": { 1: "backups/bloco_a_piso1.jpg", 2: "backups/bloco_a_piso2.jpg" },
            "Bloco B": { 1: "backups/bloco_b_piso1.jpg", 2: "backups/bloco_b_piso2.jpg" },
            "Bloco C": { 1: "backups/bloco_c_piso1.jpg", 2: "backups/bloco_c_piso2.jpg" },
            "Bloco D": { 1: "d1.png", 2: "d2.png" },
            "Bloco E": { 1: "backups/bloco_e_piso1.jpg", 2: "" }
        };

        function abrirModal(nomeBloco, subTexto) {
            blocoAtual = nomeBloco;
            document.getElementById('modalTitle').innerText = "[ INSTALAÇÃO: " + nomeBloco.toUpperCase() + " ]";
            document.getElementById('modalSub').innerText = "Descrição: " + subTexto;
            
            mudarAndarVisualizador(1);
            document.getElementById('pisoModal').style.display = 'block';
        }

        function mudarAndarVisualizador(andar) {
            document.getElementById('btnPiso1').classList.remove('active');
            document.getElementById('btnPiso2').classList.remove('active');
            document.getElementById('btnPiso' + andar).classList.add('active');

            // Aqui está o truque: o botão redireciona apenas filtrando por bloco, mantendo a tua preferência anterior!
            document.getElementById('linkAuditoriaGeral').href = "projetores.php?filtro_bloco=" + encodeURIComponent(blocoAtual);
            
            const imgEl = document.getElementById('blueprintImage');
            const placeholderEl = document.getElementById('blueprintPlaceholder');
            
            if (mapasFicheiros[blocoAtual] && mapasFicheiros[blocoAtual][andar]) {
                imgEl.src = mapasFicheiros[blocoAtual][andar];
                imgEl.style.display = "block";
                placeholderEl.style.display = "none";
                
                imgEl.onerror = function() {
                    imgEl.style.display = "none";
                    placeholderEl.style.display = "block";
                    placeholderEl.innerText = "Planta do " + andar + "º Andar do " + blocoAtual + ". Insere o print correspondente na tua pasta para ativares o visualizador.";
                };
            } else {
                imgEl.style.display = "none";
                placeholderEl.style.display = "block";
                placeholderEl.innerText = "Este complexo não possui registo técnico de " + andar + "º Andar.";
            }
        }

        function fecharModal() {
            document.getElementById('pisoModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('pisoModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>
</html>