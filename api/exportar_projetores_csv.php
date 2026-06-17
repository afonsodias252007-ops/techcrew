<?php
include('db.php');

// Definir cabeçalhos para download do ficheiro CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventario_projetores_techcrew.csv');

// Criar o ponteiro de escrita de dados
$output = fopen('php://output', 'w');

// Adicionar a indicação de UTF-8 para o Excel ler acentos corretamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalho das colunas do CSV
fputcsv($output, array('ID', 'Bloco', 'Sala', 'Equipamento Instalado', 'Observações'));

// Puxar os dados ordenados por bloco e sala
$query = "SELECT id, bloco, sala, equipamento, observacoes FROM projetores ORDER BY bloco ASC, sala ASC";
$rows = mysqli_query($conexao, $query);

while ($row = mysqli_fetch_assoc($rows)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>