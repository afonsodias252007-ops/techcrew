<?php
// Inicia a sessão para ter acesso aos dados guardados
session_start();

// Limpa todas as variáveis de sessão ativos
$_SESSION = array();

// Se o browser utilizar cookies de sessão, destrói o cookie associado
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão completamente no servidor
session_destroy();

// Redireciona o utilizador de volta para a tela de autenticação
header("Location: login.php");
exit();
?>