<?php
include('db.php');

header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=Relatorio_Semanal_" . date('Y-m-d') . ".doc");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT * FROM relatorios ORDER BY semana_ano DESC, data_envio ASC";
$resultado = mysqli_query($conexao, $query);

$relatorios_por_semana = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $relatorios_por_semana[$linha['semana_ano']][] = $linha;
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tech Crew</title>
    <link rel="icon" type="image/png" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
<style>
body {
    font-family: Arial, sans-serif;
    color: #111;
    padding: 20px;
}
h1 {
    color: #00aa55;
    border-bottom: 2px solid #00aa55;
    padding-bottom: 10px;
}
.section {
    margin-top: 30px;
    padding: 15px;
    border-left: 4px solid #00aa55;
    background: #f7f7f7;
}
.item {
    margin-top: 15px;
    padding: 10px;
    border: 1px solid #ccc;
    background: white;
}
.meta {
    font-size: 12px;
    color: #555;
    margin-bottom: 8px;
}
.text {
    white-space: pre-wrap;
    line-height: 1.5;
}
</style>
</head>
<body>
<h1>RELATÓRIO SEMANAL CONSOLIDADO</h1>
<p>Documento gerado automaticamente pelo sistema.</p>

<?php foreach ($relatorios_por_semana as $semana => $registos): ?>
<div class="section">
    <h2>Semana: <?php echo $semana; ?></h2>

    <?php foreach ($registos as $registo): ?>
        <div class="item">
            <div class="meta">
                Data: <?php echo date('d/m/Y', strtotime($registo['data_envio'])); ?> |
                Autor: <?php echo htmlspecialchars($registo['autor']); ?>
            </div>

            <div class="text">
                <?php echo nl2br(htmlspecialchars($registo['conteudo'])); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

</body>
</html>
