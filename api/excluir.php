<?php
include('db.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Proteção contra SQL Injection
    $query = "DELETE FROM relatorios WHERE id = $id";
    mysqli_query($conexao, $query);
}

header('Location: relatorio.php');
exit;
?>