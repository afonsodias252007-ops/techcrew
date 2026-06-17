<?php
// Configuração da ligação à Base de Dados
$servidor = "localhost";
$utilizador_bd = "root";
$senha_bd = "";
$nome_bd = "sistema_relatorios"; // 🌟 Substitui pelo nome real da tua Base de Dados

$conexao = mysqli_connect($servidor, $utilizador_bd, $senha_bd, $nome_bd);

if (!$conexao) {
    die("❌ Falha crítica na ligação ao terminal de dados: " . mysqli_connect_error());
}

// Configura o charset para evitar problemas com acentos e caracteres especiais
mysqli_set_charset($conexao, "utf8mb4");

/**
 * GATILHO DE AUDITORIA GLOBAL
 * Grava qualquer inserção, modificação ou remoção feita no site inteiro.
 */
function registarAtividade($acao, $pagina, $descricao) {
    global $conexao;
    
    // Captura o operador logado na sessão. Se não houver, assume o Sistema (ex: rotinas automáticas)
    $utilizador = isset($_SESSION['username']) ? $_SESSION['username'] : 'Sistema';
    
    $acao = mysqli_real_escape_string($conexao, strtoupper($acao));
    $pagina = mysqli_real_escape_string($conexao, $pagina);
    $descricao = mysqli_real_escape_string($conexao, $descricao);
    
    $query = "INSERT INTO logs_atividades (utilizador, acao, pagina, descricao) 
              VALUES ('$utilizador', '$acao', '$pagina', '$descricao')";
    mysqli_query($conexao, $query);
}
?>