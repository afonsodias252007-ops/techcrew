<?php
include('auth.php'); // Garante que o utilizador está logado
include('db.php');

// Captura o username correto da sessão e o cargo (role)
$user_logado = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$cargo_logado = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// SE FOR ADMIN: Tem autoridade total, puxa todos os relatórios
if ($cargo_logado === 'admin') {
    $query = "SELECT * FROM relatorios ORDER BY data_envio DESC";
} 
// SE FOR USER: Isolamento completo, vê apenas os registos criados por ele
else {
    $query = "SELECT * FROM relatorios WHERE LOWER(autor) = LOWER('$user_logado') ORDER BY data_envio DESC";
}

$resultado = mysqli_query($conexao, $query);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Logs Operacionais</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .btn-voltar {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(5, 5, 5, 0.9);
            border: 1px solid var(--neon);
            color: var(--neon);
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(0,255,102,0.15);
            transition: 0.3s ease;
            z-index: 9999;
        }
        .btn-voltar:hover {
            background: var(--neon);
            color: #000;
            box-shadow: 0 0 20px rgba(0,255,102,0.4);
        }
        .terminal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .terminal-table th {
            border-bottom: 2px solid var(--border);
            color: var(--neon);
            text-align: left;
            padding: 12px;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .terminal-table td {
            padding: 14px 12px;
            border-bottom: 1px solid rgba(0, 255, 102, 0.08);
            font-size: 0.9rem;
            vertical-align: middle;
        }
        .badge-semana {
            background: rgba(0, 217, 255, 0.1);
            color: var(--cyan);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid rgba(0, 217, 255, 0.2);
        }
        .table-responsive {
            overflow-x: auto;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <a href="index.php" class="btn-voltar">⬅ DASHBOARD</a>

    <div class="container">
        <div class="card-painel">
            
            <div class="terminal-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <img src="logo.jpg" alt="Tech Crew" class="terminal-logo">
                    <div>
                        <h1>LOG_RECORDS // RELATÓRIOS</h1>
                        <p>Histórico de atividades (Logado como: <?php echo htmlspecialchars($user_logado); ?>)</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="criar.php" class="btn btn-primary" style="font-weight: bold;">[ + NOVO_LOG ]</a>
                    <a href="semanal.php" class="btn btn-warning" style="font-weight: bold;">[ CONSOLIDAÇÃO_SEMANAL ]</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="terminal-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">OPERADOR</th>
                            <th style="width: 12%;">DATA</th>
                            <th style="width: 12%;">SEMANA</th>
                            <th style="width: 46%;">CONTEÚDO DO LOG</th>
                            <th style="width: 15%; text-align: right;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                            <?php while ($linha = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td style="color: var(--neon); font-weight: bold;">
                                    <?php echo htmlspecialchars(strtoupper($linha['autor'])); ?>
                                </td>
                                <td style="color: #fff;">
                                    <?php echo date('d/m/Y', strtotime($linha['data_envio'])); ?>
                                </td>
                                <td>
                                    <span class="badge-semana"><?php echo $linha['semana_ano']; ?></span>
                                </td>
                                <td style="color: #cbd5e1; max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php if (!empty($linha['foto'])): ?>
                                        <span style="color: var(--yellow); font-weight: bold; margin-right: 5px;">[📸 ANEXO]</span> 
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($linha['conteudo']); ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="editar.php?id=<?php echo $linha['id']; ?>" class="btn btn-warning" style="padding: 4px 10px; font-size: 0.75rem; height: 28px; border-radius: 6px; margin-right: 4px;">EDIT</a>
                                    <a href="excluir.php?id=<?php echo $linha['id']; ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; height: 28px; border-radius: 6px;" onclick="return confirm('Pretende eliminar este registo?')">DELETE</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 30px; text-align: center; color: var(--muted); font-style: italic;">
                                    Nenhum log operacional acessível.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>