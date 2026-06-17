<?php
include('db.php');

$id = 0;
$titulo = "";
$descricao = "";

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $resultado = mysqli_query($conexao, "SELECT * FROM tarefas WHERE id = $id");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $row = mysqli_fetch_assoc($resultado);
        $titulo = $row['titulo'];
        $descricao = $row['descricao'];
    } else {
        header('Location: tarefas.php');
        exit;
    }
} else {
    header('Location: tarefas.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bt_atualizar'])) {
    $id_update = (int)$_POST['id_tarefa'];
    $titulo_update = mysqli_real_escape_string($conexao, $_POST['titulo']);
    $descricao_update = mysqli_real_escape_string($conexao, $_POST['descricao']);
    
    mysqli_query($conexao, "UPDATE tarefas SET titulo='$titulo_update', descricao='$descricao_update' WHERE id=$id_update");
    header('Location: tarefas.php');
    exit;
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
    <a href="tarefas.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.7); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; z-index: 9999;">⬅ Voltar</a>

    <div class="container" style="margin-top: 100px; max-width: 800px; padding: 20px;">
        <div class="card-painel" style="padding: 25px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius);">
            <h2 style="color: var(--yellow); margin-bottom: 20px;">[ MODIFICAR_DIRETIVA_TAREFA #<?php echo $id; ?> ]</h2>
            
            <form action="editartarefa.php" method="POST">
                <input type="hidden" name="id_tarefa" value="<?php echo $id; ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">MENSAGEM / TÍTULO DA TAREFA:</label>
                    <input type="text" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">ESPECIFICAÇÕES OPERACIONAIS:</label>
                    <textarea name="descricao" style="width:100%; min-height:100px; padding:10px; background:#000; border:1px solid var(--border); color:#fff; resize:vertical;"><?php echo htmlspecialchars($descricao); ?></textarea>
                </div>

                <button type="submit" name="bt_atualizar" class="btn" style="background:var(--bg-card); color:var(--yellow); border:1px solid var(--yellow); padding:10px 20px; cursor:pointer;">
                    [ CONSERVAR_ALTERAÇÕES ]
                </button>
                
          
            </form>
        </div>
    </div>
</body>
</html>