<?php
include('db.php');

if (!isset($_GET['id'])) { header('Location: relatorio.php'); exit; }
$id = (int)$_GET['id'];

$query = "SELECT * FROM relatorios WHERE id = $id";
$resultado = mysqli_query($conexao, $query);
$relatorio = mysqli_fetch_assoc($resultado);

if (!$relatorio) { header('Location: relatorio.php'); exit; }

// Transforma as vírgulas guardadas em quebras de linha para mostrar na caixa de texto
$links_quebra_linha = "";
if (!empty($relatorio['foto'])) {
    $lista_ids = explode(',', $relatorio['foto']);
    $links_quebra_linha = implode("\n", $lista_ids);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $autor = mysqli_real_escape_string($conexao, $_POST['autor']);
    $data_envio = $_POST['data_envio'];
    $conteudo = mysqli_real_escape_string($conexao, $_POST['conteudo']);
    
    $semana_ano = date('Y', strtotime($data_envio)) . '-W' . date('W', strtotime($data_envio));
    $ids_drive = [];

    if (!empty($_POST['fotos_urls'])) {
        $linhas_links = explode("\n", $_POST['fotos_urls']);
        foreach ($linhas_links as $link) {
            $link_limpo = trim($link);
            if (empty($link_limpo)) continue;

            if (preg_match('/([a-zA-Z0-9-_]{25,})/', $link_limpo, $matches)) {
                $ids_drive[] = $matches[1];
            } else {
                $ids_drive[] = mysqli_real_escape_string($conexao, $link_limpo);
            }
        }
    }

    $foto_campo = !empty($ids_drive) ? "'" . implode(',', $ids_drive) . "'" : "NULL";

    $update_query = "UPDATE relatorios SET autor = '$autor', data_envio = '$data_envio', conteudo = '$conteudo', semana_ano = '$semana_ano', foto = $foto_campo WHERE id = $id";
    
    if (mysqli_query($conexao, $update_query)) { 
        header('Location: relatorio.php'); 
        exit; 
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew</title>
    <link rel="icon" type="image/png" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="card-painel">
            <img src="logo.jpg" alt="Tech Crew Logo" class="terminal-logo">

            <h1>UPDATE_LOG_ENTRY</h1>
            <p class="subtitulo">Modifique as diretrizes do log e gira a lista de evidências anexadas.</p>
            
            <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                <div class="form-group">
                    <label for="autor">OPERADOR (NOME):</label>
                    <input type="text" id="autor" name="autor" value="<?php echo htmlspecialchars($relatorio['autor']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="data_envio">TIMESTAMP (DATA):</label>
                    <input type="date" id="data_envio" name="data_envio" value="<?php echo $relatorio['data_envio']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="conteudo">LOG_CONTENT (RELATÓRIO):</label>
                    <textarea id="conteudo" name="conteudo" required><?php echo htmlspecialchars($relatorio['conteudo']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="fotos_urls">GOOGLE_DRIVE_IMAGES_LIST (UM ID/LINK POR LINHA):</label>
                    <textarea id="fotos_urls" name="fotos_urls" placeholder="Cole os links de partilha do Google Drive (um por linha)..." style="height: 100px; font-family: monospace; color: #ffff00; background: #000; border: 1px solid var(--neon-dim); padding: 10px; width: 100%;"><?php echo htmlspecialchars($links_quebra_linha); ?></textarea>
                </div>
                <button type="submit" class="btn btn-warning">[ OVERWRITE_DATA ]</button>
                <a href="relatorio.php" class="btn btn-secondary">[ ABORT ]</a>
            </form>
        </div>
    </div>
</body>
</html>