<?php
include('db.php');

$id = 0;
$bloco = "";
$sala = "";
$equipamento = "";
$observacoes = "";

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $resultado = mysqli_query($conexao, "SELECT * FROM projetores WHERE id = $id");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $row = mysqli_fetch_assoc($resultado);
        $bloco = $row['bloco'];
        $sala = $row['sala'];
        $equipamento = $row['equipamento'];
        $observacoes = $row['observacoes'];
    } else {
        header('Location: projetores.php');
        exit;
    }
} else {
    header('Location: projetores.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bt_atualizar'])) {
    $id_update = (int)$_POST['id_projetor'];
    $bloco_update = mysqli_real_escape_string($conexao, $_POST['bloco']);
    $sala_update = mysqli_real_escape_string($conexao, $_POST['sala']);
    $equipamento_update = mysqli_real_escape_string($conexao, $_POST['equipamento']);
    $observacoes_update = mysqli_real_escape_string($conexao, $_POST['observacoes']);
    
    // Se a observação for vazia, grava como NULL na BD para ficar limpo
    if (trim($observacoes_update) == "") {
        $query_update = "UPDATE projetores SET bloco='$bloco_update', sala='$sala_update', equipamento='$equipamento_update', observacoes=NULL WHERE id=$id_update";
    } else {
        $query_update = "UPDATE projetores SET bloco='$bloco_update', sala='$sala_update', equipamento='$equipamento_update', observacoes='$observacoes_update' WHERE id=$id_update";
    }
    
    mysqli_query($conexao, $query_update);
    header('Location: projetores.php');
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
    <a href="projetores.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; z-index: 99999; cursor: pointer !important;">⬅ Voltar</a>

    <div class="container" style="margin-top: 100px; max-width: 800px; padding: 20px;">
        <div class="card-painel" style="padding: 25px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius);">
            <h2 style="color: var(--yellow); margin-bottom: 20px;">[ MODIFICAR_EQUIPAMENTO_PROJETOR #<?php echo $id; ?> ]</h2>
            
            <form action="editarprojetor.php?id=<?php echo $id; ?>" method="POST">
                <input type="hidden" name="id_projetor" value="<?php echo $id; ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">BLOCO / ZONA:</label>
                    <select name="bloco" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px;">
                        <option value="Bloco A" <?php echo $bloco == 'Bloco A' ? 'selected' : ''; ?>>Bloco A</option>
                        <option value="Bloco B" <?php echo $bloco == 'Bloco B' ? 'selected' : ''; ?>>Bloco B</option>
                        <option value="Bloco C" <?php echo $bloco == 'Bloco C' ? 'selected' : ''; ?>>Bloco C</option>
                        <option value="Bloco D" <?php echo $bloco == 'Bloco D' ? 'selected' : ''; ?>>Bloco D</option>
                        <option value="Bloco E" <?php echo $bloco == 'Bloco E' ? 'selected' : ''; ?>>Bloco E</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">SALA / DIVISÃO:</label>
                    <input type="text" name="sala" value="<?php echo htmlspecialchars($sala); ?>" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">EQUIPAMENTO INSTALADO:</label>
                    <input type="text" name="equipamento" value="<?php echo htmlspecialchars($equipamento); ?>" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color: var(--text); display:block; margin-bottom: 5px;">OBSERVAÇÕES DO ESPAÇO:</label>
                    <input type="text" name="observacoes" value="<?php echo htmlspecialchars($observacoes ?? ''); ?>" style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px;">
                </div>

                <button type="submit" name="bt_atualizar" class="btn" style="background:var(--bg-card); color:var(--yellow); border:1px solid var(--yellow); padding:10px 20px; cursor:pointer !important;">
                    [ CONSERVAR_ALTERAÇÕES ]
                </button>
            </form>
        </div>
    </div>
</body>
</html>