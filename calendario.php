<?php
include('auth.php'); // Proteção de login do teu site
include('db.php');   // Ligação à base de dados ($conexao)

$user_logado = $_SESSION['username'];
$mensagem = "";
$erro = "";

// CONFIGURAÇÃO CRONOLÓGICA DO MÊS
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : intval(date('Y'));

while ($mes < 1) { $mes += 12; $ano--; }
while ($mes > 12) { $mes -= 12; $ano++; }

// ==========================================
// 1. RESPOSTA EXCLUSIVA PARA PEDIDOS AJAX (APAGAR)
// ==========================================
// Se o JavaScript pedir para apagar em segundo plano, o PHP responde e para aqui
if (isset($_GET['action']) && $_GET['action'] === 'deletar_ajax') {
    $id_deletar = intval($_GET['id']);
    
    $busca = mysqli_query($conexao, "SELECT titulo FROM eventos_calendario WHERE id=$id_deletar");
    if ($ev_dados = mysqli_fetch_assoc($busca)) {
        $tit_antigo = $ev_dados['titulo'];
        
        $sql_del = "DELETE FROM eventos_calendario WHERE id=$id_deletar";
        if (mysqli_query($conexao, $sql_del)) {
            if(function_exists('registarAtividade')) {
                registarAtividade('REMOVEU', 'calendario.php', "Removeu o agendamento: '$tit_antigo' (ID: #$id_deletar)");
            }
            // Retorna sucesso para o JavaScript saber que correu bem
            echo json_encode(['status' => 'success']);
            exit();
        }
    }
    echo json_encode(['status' => 'error']);
    exit();
}

// ==========================================
// 2. PROCESSAR ADIÇÃO OU EDIÇÃO (POST NORMAL)
// ==========================================
if (isset($_POST['adicionar_evento'])) {
    $id_evento = isset($_POST['id_evento']) ? intval($_POST['id_evento']) : 0;
    $titulo = mysqli_real_escape_string($conexao, trim($_POST['titulo']));
    $descricao = mysqli_real_escape_string($conexao, trim($_POST['descricao']));
    $data_evento = mysqli_real_escape_string($conexao, $_POST['data_evento']);
    
    if (empty($titulo) || empty($data_evento)) {
        $erro = "❌ O título e a data são obrigatórios.";
    } else {
        if ($id_evento > 0) {
            $sql = "UPDATE eventos_calendario SET titulo='$titulo', descricao='$descricao', data_evento='$data_evento' WHERE id=$id_evento";
            if (mysqli_query($conexao, $sql)) {
                $mensagem = "✅ Evento atualizado com sucesso!";
                if(function_exists('registarAtividade')) {
                    registarAtividade('EDITOU', 'calendario.php', "Modificou o evento ID #$id_evento para: '$titulo'");
                }
            } else {
                $erro = "❌ Erro ao atualizar: " . mysqli_error($conexao);
            }
        } else {
            $sql = "INSERT INTO eventos_calendario (titulo, descricao, data_evento, utilizador) VALUES ('$titulo', '$descricao', '$data_evento', '$user_logado')";
            if (mysqli_query($conexao, $sql)) {
                $mensagem = "✅ Evento agendado com sucesso!";
                if(function_exists('registarAtividade')) {
                    registarAtividade('AGENDOU', 'calendario.php', "Agendou: '$titulo' para " . date('d/m/Y', strtotime($data_evento)));
                }
            } else {
                $erro = "❌ Erro ao guardar: " . mysqli_error($conexao);
            }
        }
    }
}

// ==========================================
// 3. LEITURA DOS DADOS DO MÊS
// ==========================================
$primeiro_dia = "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
$ultimo_dia = date('Y-m-d', strtotime('last day of ' . $primeiro_dia));

$query_eventos = "SELECT * FROM eventos_calendario WHERE data_evento BETWEEN '$primeiro_dia' AND '$ultimo_dia' ORDER BY id ASC";
$result_eventos = mysqli_query($conexao, $query_eventos);
$eventos_do_mes = [];

if ($result_eventos) {
    while ($row = mysqli_fetch_assoc($result_eventos)) {
        $dia_chave = date('d', strtotime($row['data_evento']));
        $eventos_do_mes[$dia_chave][] = $row;
    }
}

$dias_do_mes = date('t', strtotime($primeiro_dia));
$primeiro_dia_semana = date('w', strtotime($primeiro_dia));
$nomes_meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$nomes_dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

$mes_anterior = $mes - 1; $ano_anterior = $ano;
if ($mes_anterior < 1) { $mes_anterior = 12; $ano_anterior--; }
$mes_proximo = $mes + 1; $ano_proximo = $ano;
if ($mes_proximo > 12) { $mes_proximo = 1; $ano_proximo++; }

$hoje_completo = date('Y-m-d');
$data_foco_inicial = isset($_POST['data_evento']) ? $_POST['data_evento'] : $hoje_completo;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Calendário Control Panel</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d; --cyan: #00ffff; --yellow: #ffe600; --red: #ff3b3b;
            --border: rgba(0, 255, 157, 0.25); --font: 'Courier New', monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.10; pointer-events: none; }
        .container { padding: 20px; max-width: 1400px; margin: 0 auto; }
        .user-hud { background: rgba(0, 255, 102, 0.04); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        
        .main-panel { background: rgba(5, 5, 5, 0.95); border: 1px solid var(--border); border-radius: 8px; padding: 25px; }
        .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid var(--border); padding-bottom: 15px; }
        .cal-header h2 { margin:0; font-size: 22px; color: #fff; }

        .cal-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .cal-table th { background: rgba(0, 255, 157, 0.18); color: #fff; padding: 14px 10px; border: 1px solid var(--border); font-size: 13px; text-transform: uppercase; }
        .cal-table td { border: 1px solid rgba(0, 255, 157, 0.25); vertical-align: top; padding: 0; background: rgba(0,0,0,0.6); }
        
        .dia-wrapper { min-height: 110px; padding: 10px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease; cursor: pointer; }
        .dia-wrapper:hover { background: rgba(0, 255, 157, 0.08); }
        .dia-num { font-weight: bold; font-size: 16px; color: #555; }
        .dia-wrapper.mes-atual .dia-num { color: #fff; }
        
        .dia-wrapper.hoje { background: rgba(0, 217, 255, 0.04); border: 1px solid var(--cyan); }
        .dia-wrapper.hoje .dia-num { color: var(--cyan); }
        .dia-wrapper.clicado { background: rgba(255, 230, 0, 0.06); border: 2px solid var(--yellow) !important; }
        .dia-wrapper.clicado .dia-num { color: var(--yellow); }

        .mini-eventos-list { margin-top: 8px; display: flex; flex-direction: column; gap: 4px; }
        .event-badge { font-size: 10px; background: rgba(0, 255, 157, 0.12); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 3px 5px; border-radius: 4px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; font-weight: bold; }

        .details-grid { display: flex; gap: 25px; margin-top: 30px; border-top: 1px dashed var(--border); padding-top: 25px; }
        .section-view { flex: 2; background: rgba(2,2,2,0.8); border: 1px solid rgba(0,217,255,0.2); border-radius: 6px; padding: 20px; }
        .section-form { flex: 1; min-width: 340px; background: rgba(2,2,2,0.8); border: 1px solid rgba(255,230,0,0.2); border-radius: 6px; padding: 20px; }

        .event-detail-item { background: rgba(5,5,5,0.9); border: 1px solid rgba(0, 255, 157, 0.15); border-left: 4px solid var(--cyan); padding: 15px; border-radius: 4px; margin-bottom: 12px; transition: opacity 0.3s ease; }
        .event-detail-meta { display: flex; justify-content: space-between; font-size: 11px; color: #888; border-bottom: 1px dashed rgba(255,255,255,0.08); padding-bottom: 5px; margin-bottom: 8px; }

        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 11px; color: #aaa; text-transform: uppercase; }
        .form-control { width: 100%; background: #000; border: 1px solid rgba(0,255,157,0.3); padding: 10px; color: #fff; box-sizing: border-box; font-family: var(--font); font-size: 12px; border-radius: 4px; }
        
        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-family: var(--font); font-size: 12px; }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
        .btn-danger { background: rgba(255, 59, 59, 0.1); color: var(--red); border-color: var(--red); }
        .btn-danger:hover { background: var(--red); color: #fff; box-shadow: 0 0 10px var(--red); }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    
    <div class="container">
        <div class="user-hud">
            <div>AUDITORIA_CRONOLÓGICA // OPERADOR: <strong style="color:#fff; text-transform:uppercase;"><?php echo $user_logado; ?></strong></div>
            <a href="index.php" class="btn">➔ [ PAINEL PRINCIPAL ]</a>
        </div>

        <?php if(!empty($mensagem)): ?> <div style="padding:12px; background:rgba(0,255,157,0.1); border:1px solid var(--neon); margin-bottom:15px; font-weight:bold; border-radius:4px;"><?php echo $mensagem; ?></div> <?php endif; ?>
        <?php if(!empty($erro)): ?> <div style="padding:12px; background:rgba(255,59,59,0.1); border:1px solid #ff3b3b; margin-bottom:15px; font-weight:bold; color:#ff5555; border-radius:4px;"><?php echo $erro; ?></div> <?php endif; ?>

        <div class="main-panel">
            <div class="cal-header">
                <div>
                    <a href="calendario.php?mes=<?php echo $mes_anterior; ?>&ano=<?php echo $ano_anterior; ?>" class="btn" style="padding:5px 12px;">< MEMÓRIA ANTERIOR</a>
                    <a href="calendario.php?mes=<?php echo $mes_proximo; ?>&ano=<?php echo $ano_proximo; ?>" class="btn" style="padding:5px 12px;">PRÓXIMO CICLO ></a>
                </div>
                <h2>[ <?php echo $nomes_meses[$mes] . " // " . $ano; ?> ]</h2>
                <a href="calendario.php" class="btn" style="border-color:var(--cyan); color:var(--cyan);">[ DATA_CORRENTE ]</a>
            </div>

            <table class="cal-table">
                <thead>
                    <tr>
                        <?php foreach($nomes_dias as $d) echo "<th>$d</th>"; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dia_corrente = 1;
                    while ($dia_corrente <= $dias_do_mes) {
                        echo "<tr>";
                        for ($i = 0; $i < 7; $i++) {
                            if (($dia_corrente === 1 && $i < $primeiro_dia_semana) || $dia_corrente > $dias_do_mes) {
                                echo "<td><div class='dia-wrapper'></div></td>";
                            } else {
                                $dia_pad = str_pad($dia_corrente, 2, '0', STR_PAD_LEFT);
                                $data_deste_dia = "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-$dia_pad";
                                $classe_hoje = ($data_deste_dia === $hoje_completo) ? 'hoje' : '';
                                
                                $eventos_json = isset($eventos_do_mes[$dia_pad]) ? json_encode($eventos_do_mes[$dia_pad]) : '[]';
                                
                                echo "<td>";
                                // Cada quadrado tem agora uma id única com o número do dia (ex: id="wrapper-dia-26") para podermos atualizar os badges via JS
                                echo "<div class='dia-wrapper mes-atual $classe_hoje' id='wrapper-dia-$dia_pad' data-dia-cru='$dia_pad' data-data='$data_deste_dia' data-eventos='".htmlspecialchars($eventos_json, ENT_QUOTES, 'UTF-8')."' onclick='selecionarDia(this)'>";
                                echo "<span class='dia-num'>$dia_pad</span>";
                                
                                echo "<div class='mini-eventos-list'>";
                                if (isset($eventos_do_mes[$dia_pad])) {
                                    $cont = 0;
                                    foreach ($eventos_do_mes[$dia_pad] as $ev) {
                                        if($cont++ < 3) echo "<span class='event-badge'>• " . htmlspecialchars($ev['titulo']) . "</span>";
                                    }
                                    if(count($eventos_do_mes[$dia_pad]) > 3) echo "<span class='event-badge' style='background:rgba(255,230,0,0.1); color:var(--yellow);'>+ ".(count($eventos_do_mes[$dia_pad])-3)." EV</span>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "</td>";
                                $dia_corrente++;
                            }
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div class="details-grid">
                
                <div class="section-view">
                    <h3 style="margin-top:0; color:var(--cyan); border-bottom:1px solid rgba(0,217,255,0.2); padding-bottom:10px;">
                        [ DETALHES DOS EVENTOS DE: <span id="label-data-view">--/--/----</span> ]
                    </h3>
                    <div id="container-lista-detalhes" style="max-height: 280px; overflow-y: auto; padding-right: 5px;">
                        </div>
                </div>

                <div class="section-form">
                    <h3 style="margin-top:0; color:var(--yellow); border-bottom:1px solid rgba(255,230,0,0.2); padding-bottom:10px;">
                        <span id="titulo-modo-form">[ REGISTAR NOVO ]</span> EM: <span id="label-data-form">--/--/----</span>
                    </h3>
                    
                    <form method="POST" id="main-event-form" action="calendario.php?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>">
                        <input type="hidden" name="id_evento" id="input-id-evento" value="0">
                        <input type="hidden" name="data_evento" id="input-data-envio" value="">
                        
                        <div class="form-group">
                            <label>TÍTULO DA DIRETIVA</label>
                            <input type="text" name="titulo" id="form-titulo" class="form-control" placeholder="Ex: Montagem da Sala 34" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>LOG_CONTENT / DETALHES</label>
                            <textarea name="descricao" id="form-descricao" class="form-control" rows="3" placeholder="Especificar tarefas, anomalias detetadas..."></textarea>
                        </div>
                        
                        <div style="display:flex; gap:10px;">
                            <button type="submit" name="adicionar_evento" id="btn-submeter-form" class="btn" style="flex:2; border-color:var(--yellow); color:var(--yellow);">[ INJECT_EVENT_LOG ]</button>
                            <button type="button" id="btn-cancelar-edicao" class="btn" style="flex:1; border-color:#aaa; color:#aaa; display:none;" onclick="cancelarEdicao()">[ CANCEL ]</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function formatarDataPT(dataISO) {
            const partes = dataISO.split('-');
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }

        function selecionarDia(elementoDiv) {
            document.querySelectorAll('.dia-wrapper').forEach(el => el.classList.remove('clicado'));
            elementoDiv.classList.add('clicado');
            
            const dataISO = elementoDiv.getAttribute('data-data');
            const eventos = JSON.parse(elementoDiv.getAttribute('data-eventos'));
            
            const dataPT = formatarDataPT(dataISO);
            document.getElementById('label-data-view').innerText = dataPT;
            document.getElementById('label-data-form').innerText = dataPT;
            document.getElementById('input-data-envio').value = dataISO;
            
            cancelarEdicao();

            const container = document.getElementById('container-lista-detalhes');
            container.innerHTML = '';
            
            if (eventos.length > 0) {
                eventos.forEach(ev => {
                    const evEscapado = encodeURIComponent(JSON.stringify(ev));
                    
                    // 🌟 MODIFICADO: O link de apagar agora chama a função JS 'apagarEventoAJAX' em vez de recarregar a página
                    const itemHTML = `
                        <div class="event-detail-item" id="detail-card-${ev.id}">
                            <div class="event-detail-meta">
                                <span>🧑‍💻 OPERADOR: <strong style="color:#fff;">${ev.utilizador.toUpperCase()}</strong></span>
                                <span>ID: #${ev.id}</span>
                            </div>
                            <h4 style="margin: 0 0 8px 0; color: var(--cyan); font-size: 14px;">➔ ${ev.titulo}</h4>
                            <p style="margin: 0 0 12px 0; font-size: 12px; color: #cbd5e1; line-height: 1.5; white-space: pre-wrap;">${ev.descricao ? ev.descricao : 'Nenhuma nota descrita.'}</p>
                            
                            <div style="display:flex; gap:10px;">
                                <button class="btn" style="padding:3px 8px; font-size:11px; border-color:var(--yellow); color:var(--yellow);" onclick="entrarModoEdicao('${evEscapado}')">[ EDITAR ]</button>
                                <button class="btn btn-danger" style="padding:3px 8px; font-size:11px;" onclick="apagarEventoAJAX(${ev.id}, '${dataISO}', '${elementoDiv.getAttribute('data-dia-cru')}')">[ APAGAR ]</button>
                            </div>
                        </div>
                    `;
                    container.innerHTML += itemHTML;
                });
            } else {
                container.innerHTML = `<div style="padding: 30px; text-align: center; color: #555; font-style: italic;">[ Nenhum compromisso agendado ]</div>`;
            }
        }

        // 🌟 NOVA FUNÇÃO: APAGAR VIA AJAX (SEM ATUALIZAR O ECRÃ)
        function apagarEventoAJAX(idEvento, dataISO, diaPad) {
            if (!confirm('Tens a certeza que queres eliminar este agendamento?')) return;
            
            // Faz o pedido assíncrono para o próprio ficheiro passando o gatilho ajax
            fetch(`calendario.php?action=deletar_ajax&id=${idEvento}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 1. Faz desaparecer suavemente o card da lista de detalhes
                        const card = document.getElementById(`detail-card-${idEvento}`);
                        if (card) {
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 300);
                        }
                        
                        // 2. Atualiza a memória interna do quadrado do calendário
                        const divDia = document.getElementById(`wrapper-dia-${diaPad}`);
                        if (divDia) {
                            let eventosAtuais = JSON.parse(divDia.getAttribute('data-eventos'));
                            // Filtra tirando fora o evento que acabámos de apagar
                            eventosAtuais = eventosAtuais.filter(ev => ev.id != idEvento);
                            divDia.setAttribute('data-eventos', JSON.stringify(eventosAtuais));
                            
                            // 3. Atualiza os mini-badges visuais dentro do quadrado imediatamente
                            const miniList = divDia.querySelector('.mini-eventos-list');
                            miniList.innerHTML = '';
                            let cont = 0;
                            eventosAtuais.forEach(ev => {
                                if(cont++ < 3) miniList.innerHTML += `<span class='event-badge'>• ${ev.titulo}</span>`;
                            });
                            if(eventosAtuais.length > 3) {
                                miniList.innerHTML += `<span class='event-badge' style='background:rgba(255,230,0,0.1); color:var(--yellow);'>+ ${eventosAtuais.length - 3} EV</span>`;
                            }
                            
                            // Se já não sobrarem eventos nenhuns nesse dia, avisa no painel
                            if (eventosAtuais.length === 0) {
                                setTimeout(() => {
                                    document.getElementById('container-lista-detalhes').innerHTML = `<div style="padding: 30px; text-align: center; color: #555; font-style: italic;">[ Nenhum compromisso agendado ]</div>`;
                                }, 350);
                            }
                        }
                    } else {
                        alert('❌ Erro de permissão ou falha ao remover o evento.');
                    }
                })
                .catch(err => console.error("Erro na stream AJAX:", err));
        }

        function entrarModoEdicao(objetoEscapado) {
            const ev = JSON.parse(decodeURIComponent(objetoEscapado));
            
            document.getElementById('input-id-evento').value = ev.id;
            document.getElementById('form-titulo').value = ev.titulo;
            document.getElementById('form-descricao').value = ev.descricao;
            
            document.getElementById('titulo-modo-form').innerText = "[ ALTERAR EVENTO ]";
            document.getElementById('btn-submeter-form').innerText = "[ ATUALIZAR_EVENT_LOG ]";
            document.getElementById('btn-cancelar-edicao').style.display = "inline-block";
            
            document.getElementById('form-titulo').focus();
        }

        function cancelarEdicao() {
            document.getElementById('input-id-evento').value = "0";
            document.getElementById('form-titulo').value = "";
            document.getElementById('form-descricao').value = "";
            
            document.getElementById('titulo-modo-form').innerText = "[ REGISTAR NOVO ]";
            document.getElementById('btn-submeter-form').innerText = "[ INJECT_EVENT_LOG ]";
            document.getElementById('btn-cancelar-edicao').style.display = "none";
        }

        window.addEventListener('DOMContentLoaded', () => {
            const hojeISO = '<?php echo $data_foco_inicial; ?>';
            const divFoco = document.querySelector(`[data-data="${hojeISO}"]`);
            if (divFoco) selecionarDia(divFoco);
            else {
                const primeiro = document.querySelector('.dia-wrapper.mes-atual');
                if (primeiro) selecionarDia(primeiro);
            }
        });

        // Matrix
        const canvas = document.getElementById("matrix-canvas"); const ctx = canvas.getContext("2d");
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; } resize();
        window.addEventListener("resize", resize);
        const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""); const fontSize = 16; const cols = canvas.width / fontSize;
        const drops = Array(Math.floor(cols)).fill(1);
        function draw(){ ctx.fillStyle = "rgba(2,2,2,0.05)"; ctx.fillRect(0,0,canvas.width,canvas.height); ctx.fillStyle = "#00ff9d"; ctx.font = fontSize + "px monospace";
        for(let i=0;i<drops.length;i++){ const txt = chars[Math.floor(Math.random()*chars.length)]; ctx.fillText(txt, i*fontSize, drops[i]*fontSize); if(drops[i]*fontSize > canvas.height && Math.random() > 0.975) drops[i]=0; drops[i]++; } }
        setInterval(draw, 35);
    </script>
</body>
</html>