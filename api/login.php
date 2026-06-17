<?php
session_start();
// Se o utilizador já estiver logado, manda-o diretamente para o dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew // ACESSO_RESTRITO</title>
    <style>
        body { 
            background: #020202; 
            color: #00ff9d; 
            font-family: 'Courier New', monospace; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
        }
        .login-box { 
            border: 2px solid #00ff9d; 
            padding: 40px; 
            background: #050505; 
            width: 320px; 
            box-shadow: 0 0 20px rgba(0,255,157,0.2); 
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }
        input { 
            background: #000; 
            border: 1px solid #00ff9d; 
            color: #00ff9d; 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0; 
            box-sizing: border-box;
            border-radius: 4px;
            outline: none;
        }
        input:focus {
            box-shadow: 0 0 10px rgba(0,255,157,0.5);
        }
        button { 
            background: #00ff9d; 
            color: #000; 
            border: none; 
            padding: 12px; 
            width: 100%; 
            cursor: pointer; 
            font-weight: bold; 
            margin-top: 15px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        button:hover {
            background: #00cc7a;
            box-shadow: 0 0 15px rgba(0,255,157,0.4);
        }
        /* Caixa de Alerta de Erro Tático */
        .alert-terminal {
            background: rgba(255, 59, 59, 0.15);
            border: 1px solid #ff3b3b;
            color: #ff5555;
            padding: 10px;
            font-size: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>[ LOGIN_SYSTEM ]</h2>
        
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert-terminal">
                <?php 
                if ($_GET['erro'] === 'user') {
                    echo "❌ ACESSO NEGADO: O USER_ID introduzido não existe no sistema.";
                } elseif ($_GET['erro'] === 'pass') {
                    echo "❌ ACESSO NEGADO: A palavra-passe (ACCESS_KEY) está incorreta.";
                } else {
                    echo "❌ ERRO: Autenticação falhou. Tente novamente.";
                }
                ?>
            </div>
        <?php endif; ?>

        <form action="processa_login.php" method="POST">
            <label style="font-size: 11px; color: #00aa66;">IDENTIFICAÇÃO:</label>
            <input type="text" name="user" placeholder="USER_ID" required autocomplete="off">
            
            <label style="font-size: 11px; color: #00aa66;">CHAVE DE ACESSO:</label>
            <input type="password" name="pass" placeholder="ACCESS_KEY" required>
            
            <button type="submit">[ INICIAR_SESSÃO ]</button>
        </form>
    </div>
</body>
</html>