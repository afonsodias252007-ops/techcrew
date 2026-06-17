<?php
include('auth.php');
include('db.php');

$id = intval($_GET['id']);
$res = mysqli_query($conexao, "SELECT * FROM ordem_montagem WHERE id = $id");
$row = mysqli_fetch_assoc($res);

if (isset($_POST['atualizar'])) {
    $ordem = intval($_POST['ordem_montagem']);
    $sala = mysqli_real_escape_string($conexao, $_POST['salas']);
    $num_pc = intval($_POST['num_pc']);
    $obs = mysqli_real_escape_string($conexao, $_POST['observacoes']);
    $ext = mysqli_real_escape_string($conexao, $_POST['extensoes']);

    $sql_up = "UPDATE ordem_montagem SET ordem_montagem=$ordem, salas='$sala', num_pc=$num_pc, observacoes='$obs', extensoes='$ext' WHERE id=$id";
    mysqli_query($conexao, $sql_up);
    header("Location: gerir_montagem.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Registo</title>
    <style>
        body { background: #020202; color: #00ff9d; font-family: monospace; padding: 50px; }
        .box { max-width: 400px; margin: 0 auto; background: #050505; border: 1px solid #00ff9d; padding: 25px; border-radius: 8px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-control { width: 100%; background: #000; border: 1px solid rgba(0,255,157,0.4); padding: 8px; color: #fff; box-sizing: border-box; }
        .btn { background: rgba(0, 255, 157, 0.1); color: #00ff9d; border: 1px solid #00ff9d; padding: 8px 15px; cursor: pointer; width: 100%; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h3>[ EDIT_DATA_NODE ]</h3>
        <form method="POST">
            <div class="form-group"><label>ORDEM MONTAGEM</label><input type="number" name="ordem_montagem" class="form-control" value="<?php echo $row['ordem_montagem']; ?>" required></div>
            <div class="form-group"><label>SALAS</label><input type="text" name="salas" class="form-control" value="<?php echo htmlspecialchars($row['salas']); ?>" required></div>
            <div class="form-group"><label>N° de PC</label><input type="number" name="num_pc" class="form-control" value="<?php echo $row['num_pc']; ?>" required></div>
            <div class="form-group"><label>EXTENSÕES</label><input type="text" name="extensoes" class="form-control" value="<?php echo htmlspecialchars($row['extensoes']); ?>"></div>
            <div class="form-group"><label>OBSERVAÇÕES</label><textarea name="observacoes" class="form-control" rows="3"><?php echo htmlspecialchars($row['observacoes']); ?></textarea></div>
            <button type="submit" name="atualizar" class="btn">[ SALVAR_ALTERAÇÕES ]</button>
            <a href="gerir_montagem.php" style="display:block; text-align:center; color:#ff3b3b; margin-top:15px; font-size:12px; text-decoration:none;">[ CANCELAR ]</a>
        </form>
    </div>
</body>
</html>