<?php
include('auth.php'); // Proteção de segurança padrão do Tech Crew
include('db.php');   // Conexão à base de dados ($conexao)

$user_logado = $_SESSION['username'];
$mensagem = "";
$erro = "";

// ==========================================
// 1. PROCESSAR INSERÇÃO DE NOVO PC (POST)
// ==========================================
if (isset($_POST['registar_pc'])) {
    $marca = mysqli_real_escape_string($conexao, trim($_POST['marca']));
    $serial_number = mysqli_real_escape_string($conexao, trim($_POST['serial_number']));
    
    if (empty($marca) || empty($serial_number)) {
        $erro = "❌ Todos os campos técnicos do hardware são obrigatórios.";
    } else {
        // Verifica se o Número de Série já existe para evitar duplicados na matriz
        $verificar = mysqli_query($conexao, "SELECT id FROM computadores WHERE serial_number = '$serial_number'");
        if (mysqli_num_rows($verificar) > 0) {
            $erro = "❌ Código de erro: S/N já registado no inventário do sistema.";
        } else {
            $sql_add = "INSERT INTO computadores (marca, serial_number) VALUES ('$marca', '$serial_number')";
            if (mysqli_query($conexao, $sql_add)) {
                $mensagem = "✅ Computador [ $marca ] S/N: $serial_number injetado no armazém!";
                if (function_exists('registarAtividade')) {
                    registarAtividade('ADICIONOU', 'pc.php', "Cadastrou novo hardware: $marca (S/N: $serial_number)");
                }
            } else {
                $erro = "❌ Falha crítica no banco de dados: " . mysqli_error($conexao);
            }
        }
    }
}

// ==========================================
// 2. CONSULTA GLOBAL COM VERIFICAÇÃO DE ESTADO
// ==========================================
// Fazemos um LEFT JOIN para saber se o ID do PC está associado a algum aluno na coluna computador_id
$query_pcs = "
    SELECT c.id, c.marca, c.serial_number, a.nome as nome_utente, a.tipo as tipo_utente
    FROM computadores c
    LEFT JOIN alunos a ON c.id = a.computador_id
    ORDER BY c.id DESC
";
$resultado_pcs = mysqli_query($conexao, $query_pcs);
$total_maquinas = mysqli_num_rows($resultado_pcs);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Inventário de Máquinas</title>
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
        
        .table-header-hub { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 15px; }
        
        .terminal-table { width: 100%; border-collapse: collapse; }
        .terminal-table th { background: rgba(0,255,157,0.15); color: #fff; border: 1px solid var(--border); padding: 12px; font-size: 11px; text-transform: uppercase; text-align: left; }
        .terminal-table td { border: 1px solid rgba(0,255,157,0.15); padding: 12px; font-size: 13px; color: #cbd5e1; }
        .terminal-table tr:hover { background: rgba(0,255,157,0.03); }
        
        /* Badges de Estado Operacional */
        .status-badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-disponivel { background: rgba(0, 217, 255, 0.15); color: var(--cyan); border: 1px solid var(--cyan); }
        .status-emprestado { background: rgba(255, 230, 0, 0.15); color: var(--yellow); border: 1px solid var(--yellow); }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 11px; color: #aaa; text-transform: uppercase; }
        .form-control { width: 100%; background: #000; border: 1px solid rgba(0,255,157,0.3); padding: 12px; color: #fff; box-sizing: border-box; font-family: var(--font); font-size: 13px; border-radius: 4px; }
        .form-control:focus { border-color: var(--neon); outline: none; box-shadow: 0 0 8px rgba(0,255,157,0.25); }
        
        .table-search-input { width: 300px; padding: 8px 12px; background: #000; border: 1px solid var(--border); color: #fff; font-family: var(--font); font-size: 12px; border-radius: 4px; }
        .table-search-input:focus { border-color: var(--cyan); outline: none; box-shadow: 0 0 8px rgba(0,217,255,0.2); }

        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px; font-family: var(--font); }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="container">
        
        <div class="user-hud">
            <div>MAINTENANCE_LOG // HARDWARE_INVENTORY</div>
            <div style="display:flex; gap:10px;">
                <a href="emprestimos.php" class="btn" style="border-color:var(--cyan); color:var(--cyan);">➔ [ CONTROLAR EMPRÉSTIMOS ]</a>
                <a href="index.php" class="btn">➔ [ PAINEL DA MATRIZ ]</a>
            </div>
        </div>

        <?php if(!empty($mensagem)): ?> <div style="padding:12px; background:rgba(0,255,157,0.1); border:1px solid var(--neon); margin-bottom:15px; font-weight:bold; border-radius:4px;"><?php echo $mensagem; ?></div> <?php endif; ?>
        <?php if(!empty($erro)): ?> <div style="padding:12px; background:rgba(255,59,59,0.1); border:1px solid var(--red); margin-bottom:15px; font-weight:bold; color:var(--red); border-radius:4px;"><?php echo $erro; ?></div> <?php endif; ?>

        <div class="split-grid">
            <div class="panel-report">
                <div class="table-header-hub">
                    <h3 style="margin:0; color:#fff;">[ CENTRAL_HARDWARE_STREAM // MÁQUINAS ]</h3>
                    <input type="text" id="filtro-pesquisa" class="table-search-input" placeholder="🔍 Filtrar por Marca ou Número de Série (S/N)..." onkeyup="filtrarInventario()">
                </div>
                
                <table class="terminal-table">
                    <thead>
                        <tr>
                            <th style="width:10%;">[ ID ]</th>
                            <th style="width:25%;">[ MARCA / MODELO ]</th>
                            <th style="width:30%;">[ NÚMERO DE SÉRIE (S/N) ]</th>
                            <th style="width:35%;">[ ESTADO OPERACIONAL ]</th>
                        </tr>
                    </thead>
                    <tbody id="corpo-tabela">
                        <?php if ($total_maquinas > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($resultado_pcs)): 
                                // Se nome_utente não for nulo, significa que está associado a alguém
                                $esta_emprestado = !empty($row['nome_utente']);
                                $texto_busca = strtolower($row['marca'] . " " . $row['serial_number']);
                            ?>
                                <tr class="linha-pc-dado" data-busca="<?php echo $texto_busca; ?>">
                                    <td style="color:var(--cyan); font-weight:bold;">#<?php echo $row['id']; ?></td>
                                    <td style="color:#fff; font-weight:bold;">🖥️ <?php echo htmlspecialchars($row['marca']); ?></td>
                                    <td style="color:var(--neon); font-weight:bold;"><?php echo htmlspecialchars($row['serial_number']); ?></td>
                                    <td>
                                        <?php if($esta_emprestado): ?>
                                            <span class="status-badge status-emprestado">EMPRESTADO</span>
                                            <span style="font-size:11px; color:#aaa; margin-left:8px;">
                                                À guarda de: <strong style="color:#fff;"><?php echo strtoupper(htmlspecialchars($row['nome_utente'])); ?></strong> 
                                                (<small style="color:var(--yellow);"><?php echo strtoupper($row['tipo_utente']); ?></small>)
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-disponivel">DISPONÍVEL</span>
                                            <span style="font-size:11px; color:#666; margin-left:8px;">No Armazém Central</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr id="linha-vazia"><td colspan="4" style="text-align:center; color:#555; font-style:italic;">Nenhum terminal de computador mapeado no inventário.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel-form">
                <h3 style="margin-top:0; color:var(--yellow); border-bottom:1px solid var(--border); padding-bottom:10px;">[ INJETAR_HARDWARE ]</h3>
                <form method="POST" action="pc.php">
                    <div class="form-group">
                        <label>Marca / Fabricante</label>
                        <input type="text" name="marca" class="form-control" placeholder="Ex: HP, Lenovo, Insys" required autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label>Número de Série Físico (S/N)</label>
                        <input type="text" name="serial_number" class="form-control" placeholder="Ex: BRG549210X" required autocomplete="off">
                    </div>
                    
                    <button type="submit" name="registar_pc" class="btn" style="width:100%; margin-top:10px; border-color:var(--yellow); color:var(--yellow);">[ CADASTRAR HARDWARE NA MATRIZ ]</button>
                </form>
                <div style="margin-top: 20px; background: rgba(0, 255, 157, 0.02); padding: 12px; border: 1px dashed rgba(0, 255, 157, 0.1); border-radius: 4px; font-size:11px; color:#aaa; line-height:1.4;">
                    ⚙️ <strong>DIRETRIZ DE INVENTÁRIO:</strong><br>
                    Após o registo, o computador entra automaticamente com a flag <span style="color:var(--cyan);">DISPONÍVEL</span>. Aloca as máquinas aos utilizadores no ecrã de empréstimos.
                </div>
            </div>
        </div>
    </div>

    <script>
        function filtrarInventario() {
            const termo = document.getElementById('filtro-pesquisa').value.toLowerCase();
            const linhas = document.querySelectorAll('.linha-pc-dado');
            
            linhas.forEach(linha => {
                const metadados = linha.getAttribute('data-busca');
                if(metadados.includes(termo)) {
                    linha.style.display = ""; // Exibe se bater com o termo
                } else {
                    linha.style.display = "none"; // Oculta se não bater
                }
            });
        }

        // Matrix Rain Setup
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