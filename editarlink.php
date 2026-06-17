<?php
include('db.php');

$id = 0;
$titulo = "";
$url = "";
$descricao = "";

// 1. Carregar os dados atuais do link
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $resultado = mysqli_query($conexao, "SELECT * FROM links_uteis WHERE id = $id");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $row = mysqli_fetch_assoc($resultado);
        $titulo = $row['titulo'];
        $url = $row['url'];
        $descricao = $row['descricao'];
    } else {
        header('Location: links.php');
        exit;
    }
} else {
    header('Location: links.php');
    exit;
}

// 2. Processar a Atualização (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bt_atualizar'])) {
    $id_update = (int)$_POST['id_link'];
    $titulo_update = mysqli_real_escape_string($conexao, $_POST['titulo']);
    $url_update = mysqli_real_escape_string($conexao, $_POST['url']);
    $descricao_update = mysqli_real_escape_string($conexao, $_POST['descricao']);
    
    mysqli_query($conexao, "UPDATE links_uteis SET titulo='$titulo_update', url='$url_update', descricao='$descricao_update' WHERE id=$id_update");
    header('Location: links.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <a href="links.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; z-index: 99999; cursor: pointer !important;">⬅ Voltar</a>

    <div class="container" style="margin-top: 100px; max-width: 800px; padding: 20px;">
        <div class="card-painel" style="padding: 25px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius);">
            <h2 style="color: var(--yellow); margin-bottom: 20px;">[ MODIFICAR_ATALHO_LINK #<?php echo $id; ?> ]</h2>
            
            <form action="editarlink.php" method="POST">
                <input type="hidden" name="id_link" value="<?php echo $id; ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">NOME / TÍTULO DO ATALHO:</label>
                    <input type="text" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px; box-sizing: border-box;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">URL / ENDEREÇO WEB (EX: https://...):</label>
                    <input type="url" name="url" value="<?php echo htmlspecialchars($url); ?>" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px; box-sizing: border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">DESCRIÇÃO OPERACIONAL:</label>
                    <input type="text" name="descricao" value="<?php echo htmlspecialchars($descricao); ?>" style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px; box-sizing: border-box;">
                </div>

                <button type="submit" name="bt_atualizar" class="btn" style="background:var(--bg-card); color:var(--yellow); border:1px solid var(--yellow); padding:10px 20px; cursor:pointer !important;">
                    [ CONSERVAR_ALTERAÇÕES ]
                </button>
                

            </form>
        </div>
    </div>
</body>
</html>