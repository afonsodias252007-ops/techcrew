<?php
include('auth.php'); // Certifica-se de que a sessão está ativa e segura
include('db.php');   // Ligação à base de dados ($conexao)

$user_logado = $_SESSION['username'];
$mensagem = "";
$erro = "";

// ==========================================
// 1. INTERCEPÇÃO AJAX: BOTÃO ENTREGUE (DEVOLUÇÃO)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'devolver_hardware') {
    $aluno_id = intval($_GET['id']);
    
    $busca = mysqli_query($conexao, "
        SELECT a.nome, c.serial_number, c.marca 
        FROM alunos a 
        INNER JOIN computadores c ON a.computador_id = c.id 
        WHERE a.id = $aluno_id
    ");
    
    if ($dados = mysqli_fetch_assoc($busca)) {
        $nome_utente = $dados['nome'];
        $sn_computador = $dados['serial_number'];
        $marca_pc = $dados['marca'];
        
        $sql_devolver = "UPDATE alunos SET computador_id = NULL WHERE id = $aluno_id";
        
        if (mysqli_query($conexao, $sql_devolver)) {
            if (function_exists('registarAtividade')) {
                registarAtividade('ENTREGUE', 'emprestimos.php', "O utente $nome_utente devolveu o computador $marca_pc (S/N: $sn_computador). Equipamento guardado no armazém.");
            }
            
            echo json_encode([
                'status' => 'success',
                'data_devolucao' => date('d/m/Y H:i')
            ]);
            exit();
        }
    }
    echo json_encode(['status' => 'error']);
    exit();
}

// ==========================================
// 2. PROCESSAR NOVO EMPRÉSTIMO (POST)
// ==========================================
if (isset($_POST['lancar_emprestimo'])) {
    $utente_id = intval($_POST['utente_id']);
    $computador_id = intval($_POST['computador_id']);
    
    if ($utente_id > 0 && $computador_id > 0) {
        $sql_add = "UPDATE alunos SET computador_id = $computador_id WHERE id = $utente_id";
        if (mysqli_query($conexao, $sql_add)) {
            $mensagem = "✅ Hardware alocado e associado ao perfil do utente com sucesso.";
            
            if (function_exists('registarAtividade')) {
                $p = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT serial_number FROM computadores WHERE id=$computador_id"));
                $u = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT nome FROM alunos WHERE id=$utente_id"));
                registarAtividade('EMPRÉSTIMO', 'emprestimos.php', "Alocou o S/N: " . $p['serial_number'] . " ao utente: " . $u['nome']);
            }
        } else {
            $erro = "❌ Falha operacional ao associar hardware: " . mysqli_error($conexao);
        }
    } else {
        $erro = "❌ Parâmetros de identificação em falta ou inválidos.";
    }
}

// ==========================================
// 3. CONSULTAS PARA RELATÓRIOS E SELETORES
// ==========================================
$lista_ativos = mysqli_query($conexao, "
    SELECT a.id, a.nome, a.tipo, a.turma, a.nif, c.marca, c.serial_number 
    FROM alunos a 
    INNER JOIN computadores c ON a.computador_id = c.id 
    WHERE a.estado = 1
");

$lista_arquivados = mysqli_query($conexao, "
    SELECT utilizador, descricao, data_hora 
    FROM logs_atividades 
    WHERE acao = 'ENTREGUE' 
    ORDER BY data_hora DESC
");

$select_utentes = mysqli_query($conexao, "SELECT id, nome, tipo, nif FROM alunos WHERE estado = 1 AND computador_id IS NULL ORDER BY nome ASC");

$select_computadores = mysqli_query($conexao, "
    SELECT c.id, c.marca, c.serial_number 
    FROM computadores c 
    LEFT JOIN alunos a ON c.id = a.computador_id 
    WHERE a.computador_id IS NULL 
    ORDER BY c.serial_number ASC
");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Gestão de Empréstimos</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d; --cyan: #00ffff; --yellow: #ffe600; --red: #ff3b3b;
            --border: rgba(0, 255, 157, 0.2); --font: 'Courier New', monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.10; pointer-events: none; }
        .container { padding: 20px; max-width: 1400px; margin: 0 auto; }
        .user-hud { background: rgba(0, 255, 102, 0.05); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        
        .split-grid { display: flex; gap: 25px; margin-bottom: 30px; }
        .panel-report { flex: 2; background: rgba(5,5,5,0.9); border: 1px solid var(--border); padding: 20px; border-radius: 8px; }
        .panel-form { flex: 1; min-width: 340px; background: rgba(5,5,5,0.9); border: 1px solid var(--border); padding: 20px; border-radius: 8px; height: fit-content; }
        
        .table-header-hub { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 12px; }
        .table-header-hub h3 { margin: 0; color: #fff; }

        .terminal-table { width: 100%; border-collapse: collapse; }
        .terminal-table th { background: rgba(0,255,157,0.15); color: #fff; border: 1px solid var(--border); padding: 10px; font-size: 11px; text-transform: uppercase; text-align: left; }
        .terminal-table td { border: 1px solid rgba(0,255,157,0.15); padding: 10px; font-size: 13px; color: #cbd5e1; }
        .terminal-table tr:hover { background: rgba(0,255,157,0.03); }
        
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 11px; color: #aaa; text-transform: uppercase; }
        .form-control { width: 100%; background: #000; border: 1px solid rgba(0,255,157,0.3); padding: 10px; color: #fff; box-sizing: border-box; font-family: var(--font); font-size: 12px; border-radius: 4px; }
        select.form-control { color: #fff; } select.form-control option { background:#000; color:#fff; }
        
        .search-box-input { border-color: var(--cyan); color: var(--cyan); margin-bottom: 6px; background: rgba(0, 217, 255, 0.03); }
        .search-box-input::placeholder { color: rgba(0, 217, 255, 0.4); }
        .search-box-input:focus { border-color: var(--cyan); box-shadow: 0 0 8px rgba(0,217,255,0.3); outline: none; }

        .table-search-input { width: 280px; padding: 6px 10px; background: #000; border: 1px solid var(--border); color: #fff; font-family: var(--font); font-size: 11px; border-radius: 4px; }
        .table-search-input:focus { border-color: var(--neon); outline: none; }

        /* Estilos para o Estado Atual das Máquinas no Histórico */
        .live-status { padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; margin-left: 10px; display: inline-block; }
        .live-available { background: rgba(0, 255, 157, 0.15); color: var(--neon); border: 1px solid rgba(0,255,157,0.3); }
        .live-loaned { background: rgba(255, 230, 0, 0.15); color: var(--yellow); border: 1px solid rgba(255,230,0,0.3); }

        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px; }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="container">
        
        <div class="user-hud">
            <div>HARDWARE_LOAN_MANAGER // CENTRAL_UNIT</div>
            <div style="display:flex; gap:10px;">
                <a href="pc.php" class="btn" style="border-color:var(--cyan); color:var(--cyan);">➔ [ INVENTÁRIO GLOBAL DE PCs ]</a>
                <a href="index.php" class="btn">➔ [ VOLTAR AO PAINEL ]</a>
            </div>
        </div>

        <?php if(!empty($mensagem)): ?> <div style="padding:10px; background:rgba(0,255,157,0.1); border:1px solid var(--neon); margin-bottom:15px; font-weight:bold;"><?php echo $mensagem; ?></div> <?php endif; ?>
        <?php if(!empty($erro)): ?> <div style="padding:10px; background:rgba(255,59,59,0.1); border:1px solid var(--red); margin-bottom:15px; font-weight:bold; color:var(--red);"><?php echo $erro; ?></div> <?php endif; ?>

        <div class="split-grid">
            <div class="panel-report">
                <div class="table-header-hub">
                    <h3>[ HARDWARE EM POSSE // MAPA ATIVO ]</h3>
                    <input type="text" id="filtro-tabela-ativa" class="table-search-input" placeholder="🔍 Filtrar tabela ativa por nome ou S/N..." onkeyup="pesquisarNaTabelaAtiva()">
                </div>
                
                <table class="terminal-table">
                    <thead>
                        <tr>
                            <th>UTENTE</th>
                            <th>COMPUTADOR ALOCADO</th>
                            <th>S/N (NÚMERO DE SÉRIE)</th>
                            <th style="text-align: center;">AÇÃO COORDENADA</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-ativos">
                        <?php if (mysqli_num_rows($lista_ativos) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($lista_ativos)): ?>
                                <tr id="row-loan-<?php echo $row['id']; ?>" class="linha-ativa-dado" data-texto-busca="<?php echo strtolower($row['nome'] . " " . $row['serial_number']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['nome']); ?></strong> 
                                        <span style="color:var(--yellow); font-size:11px;">(<?php echo strtoupper($row['tipo']); ?><?php echo !empty($row['turma']) ? " - " . $row['turma'] : ""; ?>)</span>
                                        <br><small style="color:#666;">NIF: <?php echo $row['nif']; ?></small>
                                    </td>
                                    <td>🖥️ <?php echo htmlspecialchars($row['marca']); ?></td>
                                    <td style="color:var(--cyan); font-weight:bold;"><?php echo htmlspecialchars($row['serial_number']); ?></td>
                                    <td style="text-align: center;">
                                        <button class="btn" style="border-color:var(--yellow); color:var(--yellow); padding:3px 8px; font-size:11px;" onclick="darEntregueAJAX(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nome'], ENT_QUOTES); ?>', '<?php echo $row['marca']; ?>', '<?php echo $row['serial_number']; ?>')">[ ENTREGUE ]</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr id="no-actives-row"><td colspan="4" style="text-align:center; color:#555;">Nenhum hardware retido no exterior de momento.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel-form">
                <h3 style="margin-top:0; color:var(--yellow); border-bottom:1px solid var(--border); padding-bottom:10px;">[ ALOCAR_HARDWARE ]</h3>
                <form method="POST" action="">
                    
                    <div class="form-group">
                        <label>UTENTE REQUISITANTE</label>
                        <input type="text" id="busca-utente" class="form-control search-box-input" placeholder="🔍 Digite para filtrar utente..." onkeyup="filtrarUtentes()">
                        <select name="utente_id" id="select-utente" class="form-control" required>
                            <option value="">-- Escolher Aluno / Professor --</option>
                            <?php while($ut = mysqli_fetch_assoc($select_utentes)): ?>
                                <option value="<?php echo $ut['id']; ?>" data-nome="<?php echo strtolower($ut['nome']); ?>">
                                    <?php echo htmlspecialchars($ut['nome']); ?> (<?php echo strtoupper($ut['tipo']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>SELECIONAR COMPUTADOR DISPONÍVEL</label>
                        <input type="text" id="busca-sn" class="form-control search-box-input" placeholder="🔍 Digite para filtrar S/N ou marca..." onkeyup="filtrarComputadores()">
                        <select name="computador_id" id="select-computador" class="form-control" required>
                            <option value="">-- Escolher S/N Mapeado --</option>
                            <?php while($pc = mysqli_fetch_assoc($select_computadores)): ?>
                                <option value="<?php echo $pc['id']; ?>" data-sn="<?php echo strtolower($pc['serial_number'] . " " . $pc['marca']); ?>">
                                    📦 S/N: <?php echo htmlspecialchars($pc['serial_number']); ?> (<?php echo htmlspecialchars($pc['marca']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="lancar_emprestimo" class="btn" style="width:100%; margin-top:10px; border-color:var(--yellow); color:var(--yellow);">[ INJECT_LOAN_DIRECTIVE ]</button>
                </form>
            </div>
        </div>

        <div class="panel-report" style="border-color: rgba(0, 217, 255, 0.3);">
            <div class="table-header-hub" style="border-bottom-color: rgba(0, 217, 255, 0.3);">
                <h3 style="color:var(--cyan);">[ REPOSITÓRIO_HISTÓRICO // ARQUIVO DE HARDWARE ENTREGUE ]</h3>
                <input type="text" id="filtro-tabela-historico" class="table-search-input" style="border-color:rgba(0,217,255,0.4);" placeholder="🔍 Filtrar histórico por pessoa ou S/N..." onkeyup="pesquisarNaTabelaHistorico()">
            </div>
            
            <table class="terminal-table">
                <thead>
                    <tr>
                        <th>LOG OPERACIONAL DE DEVOLUÇÃO</th>
                        <th>ESTADO ATUAL DO DISPOSITIVO</th>
                        <th>REGISTADO POR</th>
                        <th>TIMESTAMP DA ENTREGA</th>
                    </tr>
                </thead>
                <tbody id="tabela-arquivados">
                    <?php if (mysqli_num_rows($lista_arquivados) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($lista_arquivados)): 
                            $descricao_log = $row['descricao'];
                            
                            // Algoritmo de Extração: Captura o S/N de dentro do texto do log (Ex: S/N: BRG12345)
                            $sn_detetado = "";
                            if (preg_match('/S\/N:\s*([A-Za-z0-9_-]+)/', $descricao_log, $matches)) {
                                $sn_detetado = mysqli_real_escape_string($conexao, $matches[1]);
                            }

                            // Estado Padrão caso o PC tenha sido excluído do banco futuramente
                            $badge_status = '<span class="live-status" style="background:#222; color:#777;">NÃO MAPEADO</span>';

                            if (!empty($sn_detetado)) {
                                // Verifica se este S/N específico está na posse de alguém neste exato segundo
                                $checar_status = mysqli_query($conexao, "
                                    SELECT a.nome 
                                    FROM computadores c 
                                    INNER JOIN alunos a ON c.id = a.computador_id 
                                    WHERE c.serial_number = '$sn_detetado'
                                ");

                                if (mysqli_num_rows($checar_status) > 0) {
                                    $aluno_atual = mysqli_fetch_assoc($checar_status);
                                    $badge_status = '<span class="live-status live-loaned" title="Em posse de: '.$aluno_atual['nome'].'">REQUISITADO ATUALMENTE</span>';
                                } else {
                                    $badge_status = '<span class="live-status live-available">DISPONÍVEL NO ARMAZÉM</span>';
                                }
                            }
                        ?>
                            <tr class="linha-historico-dado" data-texto-busca="<?php echo strtolower($descricao_log); ?>">
                                <td style="color:#a2a8b0; font-size:12px;">✔️ <?php echo htmlspecialchars($descricao_log); ?></td>
                                <td><?php echo $badge_status; ?></td>
                                <td style="color:var(--cyan); font-weight:bold;"><?php echo strtoupper(htmlspecialchars($row['utilizador'])); ?></td>
                                <td style="color:var(--yellow);"><?php echo date('d/m/Y H:i:s', strtotime($row['data_hora'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="no-archives-row"><td colspan="4" style="text-align:center; color:#444;">O log de devoluções concluídas está limpo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function pesquisarNaTabelaAtiva() {
            const termo = document.getElementById('filtro-tabela-ativa').value.toLowerCase();
            const linhas = document.querySelectorAll('.linha-ativa-dado');
            linhas.forEach(linha => {
                if(linha.getAttribute('data-texto-busca').includes(termo)) linha.style.display = "";
                else linha.style.display = "none";
            });
        }

        function pesquisarNaTabelaHistorico() {
            const termo = document.getElementById('filtro-tabela-historico').value.toLowerCase();
            const linhas = document.querySelectorAll('.linha-historico-dado');
            linhas.forEach(linha => {
                if(linha.getAttribute('data-texto-busca').includes(termo)) linha.style.display = "";
                else linha.style.display = "none";
            });
        }

        function filtrarUtentes() {
            const termoPesquisa = document.getElementById('busca-utente').value.toLowerCase();
            const select = document.getElementById('select-utente');
            const opcoes = select.options;
            for (let i = 1; i < opcoes.length; i++) {
                if (opcoes[i].getAttribute('data-nome').includes(termoPesquisa)) opcoes[i].style.display = "";
                else opcoes[i].style.display = "none";
            }
        }

        function filtrarComputadores() {
            const termoPesquisa = document.getElementById('busca-sn').value.toLowerCase();
            const select = document.getElementById('select-computador');
            const opcoes = select.options;
            for (let i = 1; i < opcoes.length; i++) {
                if (opcoes[i].getAttribute('data-sn').includes(termoPesquisa)) opcoes[i].style.display = "";
                else opcoes[i].style.display = "none";
            }
        }

        function darEntregueAJAX(alunoId, nomeUtente, marcaPC, serialNumber) {
            fetch(`emprestimos.php?action=devolver_hardware&id=${alunoId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        const rowAtiva = document.getElementById(`row-loan-${alunoId}`);
                        if(rowAtiva) rowAtiva.remove();
                        
                        const tabelaAtivos = document.getElementById('tabela-ativos');
                        if(tabelaAtivos.rows.length === 0) {
                            tabelaAtivos.innerHTML = '<tr id="no-actives-row"><td colspan="4" style="text-align:center; color:#555;">Nenhum hardware retido no exterior de momento.</td></tr>';
                        }
                        
                        const noArchiveRow = document.getElementById('no-archives-row');
                        if(noArchiveRow) noArchiveRow.remove();
                        
                        const tabelaArquivados = document.getElementById('tabela-arquivados');
                        const logDescricao = `✔️ O utente ${nomeUtente} devolveu o computador ${marcaPC} (S/N: ${serialNumber}). Equipamento guardado no armazém.`.toLowerCase();
                        
                        // Quando entregue via AJAX, ele entra imediatamente como "DISPONÍVEL NO ARMAZÉM"
                        const novaLinha = `
                            <tr class="linha-historico-dado" data-texto-busca="${logDescricao}">
                                <td style="color:#a2a8b0; font-size:12px;">✔️ O utente ${nomeUtente} devolveu o computador ${marcaPC} (S/N: ${serialNumber}). Equipamento guardado no armazém.</td>
                                <td><span class="live-status live-available">DISPONÍVEL NO ARMAZÉM</span></td>
                                <td style="color:var(--cyan); font-weight:bold;"><?php echo strtoupper($user_logado); ?></td>
                                <td style="color:var(--yellow);">${data.data_devolucao}</td>
                            </tr>
                        `;
                        tabelaArquivados.innerHTML = novaLinha + tabelaArquivados.innerHTML;
                    }
                });
        }

        // Matrix Rain
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