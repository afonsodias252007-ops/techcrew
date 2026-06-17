<?php
include('auth.php');
include('db.php');

$mensagem = "";
$erro = "";

// 1. GATILHO: ADICIONAR REGISTO (INSERT)
if (isset($_POST['adicionar'])) {
    $ordem = intval($_POST['ordem_montagem']);
    $sala = mysqli_real_escape_string($conexao, trim($_POST['salas']));
    $num_pc = intval($_POST['num_pc']);
    $obs = mysqli_real_escape_string($conexao, trim($_POST['observacoes']));
    $ext = mysqli_real_escape_string($conexao, trim($_POST['extensoes']));

    if (empty($sala)) {
        $erro = "❌ O campo SALAS é obrigatório.";
    } else {
        $sql_add = "INSERT INTO ordem_montagem (ordem_montagem, salas, num_pc, observacoes, extensoes) 
                    VALUES ($ordem, '$sala', $num_pc, '$obs', '$ext')";
        
        if (mysqli_query($conexao, $sql_add)) {
            // 🌟 REGISTO DE AUDITORIA
            registarAtividade('ADICIONOU', 'gerir_montagem.php', "Injetou a sala '$sala' na ordem de montagem #$ordem com $num_pc PCs.");
            $mensagem = "✅ Nova diretiva de montagem registada com sucesso.";
        } else {
            $erro = "❌ Erro ao salvar dados: " . mysqli_error($conexao);
        }
    }
}

// 2. GATILHO: APAGAR REGISTO (DELETE)
if (isset($_GET['apagar'])) {
    $id_apagar = intval($_GET['apagar']);
    
    // Procura os dados da sala antes de a apagar para guardar no histórico detalhado
    $busca_dados = mysqli_query($conexao, "SELECT salas, ordem_montagem FROM ordem_montagem WHERE id = $id_apagar");
    if ($dados_sala = mysqli_fetch_assoc($busca_dados)) {
        $sala_nome = $dados_sala['salas'];
        $ordem_num = $dados_sala['ordem_montagem'];
        
        $sql_del = "DELETE FROM ordem_montagem WHERE id = $id_apagar";
        if (mysqli_query($conexao, $sql_del)) {
            // 🌟 REGISTO DE AUDITORIA (Mesmo que a sala suma do inventário, o log fica guardado!)
            registarAtividade('REMOVEU', 'gerir_montagem.php', "Eliminou permanentemente a sala '$sala_nome' que estava na ordem #$ordem_num (Registo ID: #$id_apagar).");
            header("Location: gerir_montagem.php");
            exit();
        }
    }
}

// Puxar listagem total atualizada
$resultado = mysqli_query($conexao, "SELECT * FROM ordem_montagem ORDER BY ordem_montagem ASC");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Gestão de Montagem</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d;
            --cyan: #00ffff;
            --yellow: #ffe600;
            --border: rgba(0, 255, 157, 0.2);
            --font: 'Courier New', Courier, monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.12; pointer-events: none; }
        .container { padding: 20px; max-width: 1400px; margin: 0 auto; }
        .user-hud { background: rgba(0, 255, 102, 0.05); border: 1px solid rgba(0, 255, 102, 0.2); padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.4); padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-family: var(--font); font-size: 12px; }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 10px var(--neon); }
        .btn-danger { background: rgba(255, 59, 59, 0.1); color: #ff3b3b; border: 1px solid #ff3b3b; }
        .btn-danger:hover { background: #ff3b3b; color: #fff; box-shadow: 0 0 10px #ff3b3b; }
        .btn-export { color: var(--cyan); border-color: var(--cyan); background: rgba(0,217,255,0.1); }
        .btn-export:hover { background: var(--cyan); color: #000; box-shadow: 0 0 10px var(--cyan); }
        
        .split-layout { display: flex; gap: 25px; margin-top: 20px; }
        .col-form { flex: 1; min-width: 320px; }
        .col-table { flex: 3; }
        
        .form-box { background: rgba(5,5,5,0.9); border: 1px solid var(--border); padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 11px; color: #aaa; }
        .form-control { width: 100%; background: #000; border: 1px solid rgba(0,255,157,0.3); padding: 10px; color: #fff; box-sizing: border-box; font-family: var(--font); font-size: 12px; border-radius: 4px; }
        .form-control:focus { border-color: var(--neon); outline: none; }
        
        .terminal-table { width: 100%; border-collapse: collapse; background: rgba(2,2,2,0.9); }
        .terminal-table th { background: rgba(0,255,157,0.15); color: #fff; border: 1px solid rgba(0,255,157,0.3); padding: 12px; text-align: left; font-size: 12px; }
        .terminal-table td { border: 1px solid rgba(0,255,157,0.15); padding: 12px; font-size: 13px; color: #cbd5e1; }
        .terminal-table tr:hover { background: rgba(0,255,157,0.05); }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="container">
        
        <div class="user-hud">
            <div>CENTRAL_LOGS // OPERADOR: <strong style="color:#fff; text-transform:uppercase;"><?php echo $_SESSION['username']; ?></strong></div>
            <div style="display: flex; gap: 10px;">
                <a href="exportar_csv.php" class="btn btn-export">[ EXPORTAR_CSV ]</a>
                <a href="exportar_pdf.php" target="_blank" class="btn btn-export">[ IMPRIMIR_PDF ]</a>
                <a href="ver_montagem.php" class="btn">[ VOLTAR ]</a>
            </div>
        </div>

        <?php if(!empty($mensagem)): ?> <div style="padding:10px; background:rgba(0,255,157,0.1); border:1px solid var(--neon); margin-bottom:15px; font-weight:bold;"><?php echo $mensagem; ?></div> <?php endif; ?>
        <?php if(!empty($erro)): ?> <div style="padding:10px; background:rgba(255,59,59,0.1); border:1px solid #ff3b3b; margin-bottom:15px; font-weight:bold; color:#ff5555;"><?php echo $erro; ?></div> <?php endif; ?>

        <div class="split-layout">
            <div class="col-form">
                <div class="form-box">
                    <h3 style="margin-top:0; border-bottom:1px solid rgba(0,255,157,0.2); padding-bottom:10px; color:#fff;">[ INJECT_NEW_DATA ]</h3>
                    <form method="POST" action="gerir_montagem.php">
                        <div class="form-group"><label>ORDEM MONTAGEM</label><input type="number" name="ordem_montagem" class="form-control" value="1" required></div>
                        <div class="form-group"><label>SALAS / ESPAÇO</label><input type="text" name="salas" class="form-control" placeholder="Ex: Sala 102" required></div>
                        <div class="form-group"><label>N° DE COMPUTADORES</label><input type="number" name="num_pc" class="form-control" value="0" required></div>
                        <div class="form-group"><label>EXTENSÕES UTILIZADAS</label><input type="text" name="extensoes" class="form-control" placeholder="Ex: 2 de 5 metros"></div>
                        <div class="form-group"><label>OBSERVAÇÕES TÉCNICAS</label><textarea name="observacoes" class="form-control" rows="3" placeholder="Cabos em falta, tomadas danificadas..."></textarea></div>
                        <button type="submit" name="adicionar" class="btn" style="width:100%; margin-top:10px;">[ ADICIONAR_REGISTO ]</button>
                    </form>
                </div>
            </div>

            <div class="col-table">
                <div style="background: rgba(5,5,5,0.9); border: 1px solid var(--border); padding: 20px; border-radius: 8px;">
                    <table class="terminal-table">
                        <thead>
                            <tr>
                                <th>ORDEM</th>
                                <th>SALA</th>
                                <th>N° PC</th>
                                <th>EXTENSÕES</th>
                                <th>OBSERVAÇÕES</th>
                                <th style="text-align: center;">AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($resultado) > 0) {
                                while($row = mysqli_fetch_assoc($resultado)) {
                                    echo "<tr>";
                                    echo "<td style='font-weight:bold; color:var(--neon);'>#" . intval($row['ordem_montagem']) . "</td>";
                                    echo "<td style='color:var(--yellow); font-weight:bold;'>" . htmlspecialchars($row['salas']) . "</td>";
                                    echo "<td>" . intval($row['num_pc']) . " Unidades</td>";
                                    echo "<td>" . htmlspecialchars($row['extensoes'] ? $row['extensoes'] : '-') . "</td>";
                                    echo "<td style='color:#a2a8b0; font-size:12px;'>" . htmlspecialchars($row['observacoes'] ? $row['observacoes'] : '-') . "</td>";
                                    echo "<td style='text-align:center; white-space:nowrap;'>
                                            <a href='editar_montagem.php?id=".$row['id']."' class='btn' style='padding:4px 8px; font-size:11px; border-color:var(--yellow); color:var(--yellow);'>[ EDITAR ]</a> 
                                            <a href='gerir_montagem.php?apagar=".$row['id']."' class='btn btn-danger' style='padding:4px 8px; font-size:11px;' onclick='return confirm(\"Deseja eliminar esta diretiva permanentemente?\")'>[ X ]</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center; color:#aaa; font-style:italic;'>Nenhuma infraestrutura mapeada.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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