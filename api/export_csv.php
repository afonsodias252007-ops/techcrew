<?php
include('db.php');

// Configurar cabeçalhos para download do CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=comodatos_hardware_' . date('Ymd_His') . '.csv');

// Criar ponteiro de saída
$output = fopen('php://output', 'w');

// Forçar o BOM do UTF-8 para o Excel reconhecer os acentos corretamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalho das colunas do CSV
fputcsv($output, array('ID UTENTE', 'NOME COMPLETO', 'NIF', 'ESTATUTO', 'TURMA', 'EQUIPAMENTOS ALOCADOS (ID - MARCA - DATA ENTREGA - CARREGADOR)'));

// Filtragem baseada na pesquisa atual
$where_search = "";
if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    $pesquisa = mysqli_real_escape_string($conexao, trim($_GET['buscar']));
    $where_search = " WHERE nome LIKE '%$pesquisa%' OR nif LIKE '%$pesquisa%' ";
}

$utentes = mysqli_query($conexao, "SELECT * FROM alunos $where_search ORDER BY id DESC");

while ($u = mysqli_fetch_assoc($utentes)) {
    $estatuto = strtoupper($u['tipo']);
    $turma = ($u['tipo'] == 'professor') ? 'N/A' : $u['turma'];
    
    // Agregação de múltiplos equipamentos associados ao utente
    $emp_query = mysqli_query($conexao, "SELECT e.*, c.marca FROM emprestimos e JOIN computadores c ON e.computador_id = c.id WHERE e.utente_id = {$u['id']}");
    $pcs_lista = array();
    
    while ($emp = mysqli_fetch_assoc($emp_query)) {
        $carregador = $emp['tem_carregador'] ? '+Carregador' : 'Falta Carregador';
        $data_ent = date('d/m/Y', strtotime($emp['data_entrega']));
        $pcs_lista[] = "PC ID: " . $emp['computador_id'] . " - " . $emp['marca'] . " (" . $data_ent . " | " . $carregador . ")";
    }
    
    $pcs_string = (count($pcs_lista) > 0) ? implode(" | ", $pcs_lista) : "Nenhum equipamento associado";

    // Escrever linha no ficheiro CSV
    fputcsv($output, array(
        $u['id'],
        $u['nome'],
        $u['nif'],
        $estatuto,
        $turma,
        $pcs_string
    ));
}

fclose($output);
exit;
?>