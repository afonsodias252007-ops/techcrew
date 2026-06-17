<?php
include('db.php');

// ==========================================
// 1. PROCESSAR AÇÕES DIRETAS (ELIMINAR / TOGGLE)
// ==========================================

// Ação: Eliminar Tarefa
if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    mysqli_query($conexao, "DELETE FROM tarefas WHERE id = $id_excluir");
    header('Location: tarefas.php');
    exit;
}

// Ação: Alternar Estado (Concluir / Reabrir)
if (isset($_GET['toggle']) && isset($_GET['estado'])) {
    $id_toggle = (int)$_GET['toggle'];
    $estado_atual = (int)$_GET['estado'];
    $novo_estado = ($estado_atual == 1) ? 0 : 1;
    
    mysqli_query($conexao, "UPDATE tarefas SET concluida = $novo_estado WHERE id = $id_toggle");
    
    $filtro_retorno = isset($_GET['filtro']) ? $_GET['filtro'] : 'todas';
    header("Location: tarefas.php?filtro=" . $filtro_retorno);
    exit;
}

// ==========================================
// 2. PROCESSAR INSERÇÃO DE NOVA TAREFA
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bt_salvar'])) {
    $titulo = mysqli_real_escape_string($conexao, $_POST['titulo']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    
    mysqli_query($conexao, "INSERT INTO tarefas (titulo, descricao, concluida) VALUES ('$titulo', '$descricao', 0)");
    header('Location: tarefas.php');
    exit;
}

// Controlo dos Filtros de Visualização
$filtro = "todas";
$where_filter = "";
if (isset($_GET['filtro'])) {
    if ($_GET['filtro'] == 'concluidas') {
        $filtro = "concluidas";
        $where_filter = " WHERE concluida = 1 ";
    } elseif ($_GET['filtro'] == 'pendentes') {
        $filtro = "pendentes";
        $where_filter = " WHERE concluida = 0 ";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew</title>
    <link rel="icon" type="image/png" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <a href="index.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; z-index: 99999; display: inline-block; cursor: pointer !important;">⬅ Voltar ao Painel</a>

    <div class="container" style="margin-top: 80px; max-width: 1300px; padding: 20px; position: relative; z-index: 1;">

        <div class="card-painel" style="margin-bottom: 30px; padding: 25px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); position: relative; z-index: 5;">
            <h2 style="color: var(--neon); margin-bottom: 20px;">[ AGENDAR NOVA TAREFA ]</h2>
            <form action="tarefas.php" method="POST">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">MENSAGEM / TÍTULO DA TAREFA:</label>
                    <input type="text" name="titulo" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">ESPECIFICAÇÕES OPERACIONAIS (OPCIONAL):</label>
                    <textarea name="descricao" style="width:100%; min-height:70px; padding:10px; background:#000; border:1px solid var(--border); color:#fff; resize:vertical;"></textarea>
                </div>

                <button type="submit" name="bt_salvar" class="btn" style="background:var(--bg-card); color:var(--neon); border:1px solid var(--neon); padding:10px 20px; cursor:pointer !important; position: relative; z-index: 10;">
                    [ ENVIAR PARA MONITOR ]
                </button>
            </form>
        </div>

        <div class="card-painel" style="padding: 25px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); position: relative; z-index: 5;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <h2 style="color: var(--cyan); margin: 0;">[ MONITOR DE CENTRAL DE TRABALHO ]</h2>
                
                <div style="display: inline-flex; gap: 6px; background: #000; padding: 4px; border-radius: 8px; border: 1px solid var(--border); position: relative; z-index: 10;">
                    <a href="tarefas.php?filtro=todas" style="padding: 6px 12px; font-size: 11px; text-decoration: none; font-weight: bold; border-radius: 6px; cursor: pointer !important; <?php echo $filtro == 'todas' ? 'background: var(--neon); color:#000;' : 'color:#fff;'; ?>">VER TODAS</a>
                    <a href="tarefas.php?filtro=pendentes" style="padding: 6px 12px; font-size: 11px; text-decoration: none; font-weight: bold; border-radius: 6px; cursor: pointer !important; <?php echo $filtro == 'pendentes' ? 'background: var(--yellow); color:#000;' : 'color:#fff;'; ?>">PENDENTES</a>
                    <a href="tarefas.php?filtro=concluidas" style="padding: 6px 12px; font-size: 11px; text-decoration: none; font-weight: bold; border-radius: 6px; cursor: pointer !important; <?php echo $filtro == 'concluidas' ? 'background: var(--cyan); color:#000;' : 'color:#fff;'; ?>">CONCLUÍDAS</a>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; text-align: left; position: relative; z-index: 6;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); color: var(--neon);">
                        <th style="padding: 10px; width: 15%; text-align: center;">ESTADO REAL</th>
                        <th style="padding: 10px; width: 50%;">DETALHES DO REGISTO</th>
                        <th style="padding: 10px; width: 15%; text-align: center;">MUDAR ESTADO</th>
                        <th style="padding: 10px; width: 20%; text-align: right;">AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query_tarefas = "SELECT * FROM tarefas $where_filter ORDER BY concluida ASC, id DESC";
                    $resultado = mysqli_query($conexao, $query_tarefas);

                    if ($resultado && mysqli_num_rows($resultado) > 0): 
                        while($row = mysqli_fetch_assoc($resultado)): 
                            $is_done = ((int)$row['concluida'] === 1);
                    ?>
                        <tr style="border-bottom: 1px solid rgba(0,255,102,0.1); background: <?php echo $is_done ? 'rgba(0,217,255,0.03)' : 'transparent'; ?>;">
                            
                            <td style="padding: 12px; text-align: center; vertical-align: middle;">
                                <?php if($is_done): ?>
                                    <span style="color: #000; background: var(--cyan); padding: 4px 10px; font-size: 11px; font-weight: bold; border-radius: 4px; display: inline-block; box-shadow: 0 0 10px var(--cyan);">CONCLUÍDA</span>
                                <?php else: ?>
                                    <span style="color: #000; background: var(--danger); padding: 4px 10px; font-size: 11px; font-weight: bold; border-radius: 4px; display: inline-block;">PENDENTE</span>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px; vertical-align: middle;">
                                <span style="font-size: 14px; font-weight: bold; <?php echo $is_done ? 'text-decoration: line-through; color: #555;' : 'color: #fff;'; ?>">
                                    <?php echo htmlspecialchars($row['titulo']); ?>
                                </span>
                                <?php if(!empty($row['descricao'])): ?>
                                    <br><span style="font-size: 11px; color: #888; <?php echo $is_done ? 'text-decoration: line-through; color: #444;' : ''; ?>">
                                        <?php echo htmlspecialchars($row['descricao']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px; text-align: center; vertical-align: middle;">
                                <a href="tarefas.php?toggle=<?php echo $row['id']; ?>&estado=<?php echo $row['concluida']; ?>&filtro=<?php echo $filtro; ?>" 
                                   style="display: inline-block; padding: 6px 12px; font-size: 11px; font-weight: bold; text-decoration: none; border-radius: 4px; border: 1px solid <?php echo $is_done ? '#444' : 'var(--neon)'; ?>; color: <?php echo $is_done ? '#aaa' : 'var(--neon)'; ?>; background: <?php echo $is_done ? '#222' : 'transparent'; ?>; position: relative; z-index: 999; cursor: pointer !important;">
                                   <?php echo $is_done ? 'REABRIR' : 'CONCLUIR'; ?>
                                </a>
                            </td>

                            <td style="padding: 12px; text-align: right; vertical-align: middle;">
                                <a href="editartarefa.php?id=<?php echo $row['id']; ?>" 
                                   style="display: inline-block; padding: 6px 12px; font-size: 11px; font-weight: bold; text-decoration: none; background: transparent; color: var(--yellow); border: 1px solid var(--yellow); border-radius: 6px; margin-right: 5px; position: relative; z-index: 999; cursor: pointer !important;">
                                   EDITAR
                                </a>
                                <a href="tarefas.php?excluir=<?php echo $row['id']; ?>" 
                                   style="display: inline-block; padding: 6px 12px; font-size: 11px; font-weight: bold; text-decoration: none; background: var(--danger); color: #000; border: none; border-radius: 6px; position: relative; z-index: 999; cursor: pointer !important;"
                                   onclick="return confirm('Deseja eliminar permanentemente esta tarefa do painel?')">
                                   APAGAR
                                </a>
                            </td>

                        </tr>
                        <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="4" style="padding: 25px; text-align: center; color: #555; font-style: italic;">
                                Sem tarefas correspondentes ao filtro ativo.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>