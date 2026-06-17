<?php
include('auth.php');
include('db.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ordem_montagem_salas.csv');

$output = fopen('php://output', 'w');
// Adiciona a marca UTF-8 para o Excel não quebrar os acentos
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos das colunas
fputcsv($output, array('ORDEM MONTAGEM', 'SALAS', 'Nº DE PC', 'EXTENSÕES', 'OBSERVAÇÕES'));

$query = "SELECT ordem_montagem, salas, num_pc, extensoes, observacoes FROM ordem_montagem ORDER BY ordem_montagem ASC";
$rows = mysqli_query($conexao, $query);

while ($row = mysqli_fetch_assoc($rows)) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>