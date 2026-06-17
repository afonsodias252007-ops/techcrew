<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se não houver uma sessão de utilizador ativa, expulsa para o login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Função para proteger páginas exclusivas do Admin (ex: criar novos utilizadores)
function verificarAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die("Acesso Negado: Nível de autoridade insuficiente para esta operação.");
    }
}
?>