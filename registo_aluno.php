<?php
include('auth.php'); 
include('db.php');   

$user_logado = $_SESSION['username'];
$mensagem = "";
$erro = "";

if (isset($_POST['registar_utente'])) {
    $nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
    $tipo = mysqli_real_escape_string($conexao, $_POST['tipo']);
    $nif = mysqli_real_escape_string($conexao, trim($_POST['nif']));
    $turma = mysqli_real_escape_string($conexao, trim($_POST['turma']));
    
    if (empty($nome) || empty($nif)) {
        $erro = "❌ Os campos [ NOME ] e [ NIF ] são obrigatórios.";
    } elseif (strlen($nif) !== 9) {
        $erro = "❌ O NIF deve conter exatamente 9 dígitos.";
    } else {
        // Inserção exata com as colunas do teu print
        $sql_insert = "INSERT INTO alunos (tipo, nome, nif, turma, estado) 
                       VALUES ('$tipo', '$nome', '$nif', " . (!empty($turma) ? "'$turma'" : "NULL") . ", 1)";
        
        if (mysqli_query($conexao, $sql_insert)) {
            $mensagem = "✅ " . strtoupper($tipo) . " [ $nome ] registado com sucesso com o NIF $nif!";
            if (function_exists('registarAtividade')) {
                registarAtividade('ADICIONOU', 'registo_aluno.php', "Registou $tipo: '$nome' (NIF: $nif)");
            }
        } else {
            $erro = "❌ Falha crítica ao injetar dados no SQL: " . mysqli_error($conexao);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Injetar Utente</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --neon: #00ff9d; --cyan: #00ffff; --yellow: #ffe600; --red: #ff3b3b;
            --border: rgba(0, 255, 157, 0.25); --font: 'Courier New', monospace;
        }
        body { margin: 0; font-family: var(--font); background: #020202; color: var(--neon); overflow-x: hidden; position: relative; }
        #matrix-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; opacity: 0.10; pointer-events: none; }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(0, 255, 157, 0.015) 1px, transparent 1px), linear-gradient(rgba(0, 255, 157, 0.012) 50%, rgba(0, 0, 0, 0.18) 50%); background-size: 30px 30px, 100% 4px; z-index: 10; opacity: 0.6; pointer-events: none !important; }
        .container { padding: 20px; max-width: 500px; margin: 40px auto; }
        .form-box { background: rgba(5, 5, 5, 0.95); border: 1px solid var(--border); padding: 30px; border-radius: 8px; box-shadow: 0 0 20px rgba(0,255,157,0.15); }
        .form-header { border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 25px; }
        .form-header h2 { margin: 0; font-size: 18px; color: #fff; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 11px; color: #cbd5e1; text-transform: uppercase; }
        .form-control { width: 100%; background: #000; border: 1px solid rgba(0,255,157,0.3); padding: 12px; color: #fff; box-sizing: border-box; font-family: var(--font); font-size: 13px; border-radius: 4px; }
        .form-control:focus { border-color: var(--neon); outline: none; box-shadow: 0 0 10px rgba(0,255,157,0.25); }
        select.form-control { color: var(--neon); }
        select.form-control option { background: #000; color: #fff; }
        .btn { background: rgba(0, 255, 157, 0.1); color: var(--neon); border: 1px solid rgba(0, 255, 157, 0.5); padding: 12px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; font-family: var(--font); width: 100%; text-transform: uppercase; box-sizing: border-box; display: block; text-align: center; }
        .btn:hover { background: var(--neon); color: #000; box-shadow: 0 0 15px var(--neon); }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="container">
        <?php if(!empty($mensagem)): ?> <div style="padding:12px; background:rgba(0,255,157,0.1); border:1px solid var(--neon); margin-bottom:20px; font-weight:bold; border-radius:4px; font-size:12px;"><?php echo $mensagem; ?></div> <?php endif; ?>
        <?php if(!empty($erro)): ?> <div style="padding:12px; background:rgba(255,59,59,0.1); border:1px solid #ff3b3b; margin-bottom:20px; font-weight:bold; color:#ff5555; border-radius:4px; font-size:12px;"><?php echo $erro; ?></div> <?php endif; ?>

        <div class="form-box">
            <div class="form-header">
                <h2>[ INJECT_NEW_UTENTE ]</h2>
                <p>Operador: <span style="color:var(--yellow);"><?php echo strtoupper($user_logado); ?></span></p>
            </div>
            
            <form method="POST" action="registo_aluno.php">
                <div class="form-group">
                    <label>Tipo de Utente</label>
                    <select name="tipo" class="form-control" required>
                        <option value="aluno">Aluno</option>
                        <option value="professor">Professor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" class="form-control" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>NIF (Contribuinte - 9 dígitos)</label>
                    <input type="text" name="nif" class="form-control" maxlength="9" placeholder="Ex: 234567890" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Turma (Deixar vazio se for Professor)</label>
                    <input type="text" name="turma" class="form-control" placeholder="Ex: 12ºI" autocomplete="off">
                </div>
                
                <button type="submit" name="registar_utente" class="btn">[ EXECUTAR_REGISTO ]</button>
                <a href="alunos.php" style="display:block; text-align:center; color:#aaa; font-size:11px; margin-top:15px; text-decoration:none;">[ VOLTAR ]</a>
            </form>
        </div>
    </div>
    <script>
        const canvas = document.getElementById("matrix-canvas"); const ctx = canvas.getContext("2d");
        function res(){ canvas.width = window.innerWidth; canvas.height = window.innerHeight; } res();
        const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""); const fontSize = 16; const cols = canvas.width / fontSize;
        const drops = Array(Math.floor(cols)).fill(1);
        function draw(){ ctx.fillStyle = "rgba(2,2,2,0.05)"; ctx.fillRect(0,0,canvas.width,canvas.height); ctx.fillStyle = "#00ff9d"; ctx.font = fontSize + "px monospace";
        for(let i=0;i<drops.length;i++){ const txt = chars[Math.floor(Math.random()*chars.length)]; ctx.fillText(txt, i*fontSize, drops[i]*fontSize); if(drops[i]*fontSize > canvas.height && Math.random() > 0.975) drops[i]=0; drops[i]++; } }
        setInterval(draw, 35);
    </script>
</body>
</html>