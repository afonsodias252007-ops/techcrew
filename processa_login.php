<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = mysqli_real_escape_string($conexao, trim($_POST['user']));
    $pass = $_POST['pass']; // Palavra-passe vinda do formulário

    // 1. Procura o utilizador na base de dados pelo username
    $query = "SELECT * FROM utilizadores WHERE username = '$user' LIMIT 1";
    $resultado = mysqli_query($conexao, $query);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $utilizador = mysqli_fetch_assoc($resultado);
        
        // 2. O utilizador existe! Vamos verificar a palavra-passe.
        // Testa com password_verify (para a hash nova que inseriste) ou texto limpo/MD5 se usares nos testes
        if (password_verify($pass, $utilizador['password']) || $pass === $utilizador['password'] || md5($pass) === $utilizador['password']) {
            
            // Login feito com sucesso! Guarda os dados cruciais na Sessão
            $_SESSION['user_id']  = $utilizador['id'];
            $_SESSION['username'] = $utilizador['username'];
            $_SESSION['user']     = $utilizador['username']; // Chave de compatibilidade usada nos relatórios
            $_SESSION['role']     = $utilizador['role'];     // 'admin' ou 'user'

            header("Location: index.php");
            exit();
        } else {
            // Palavra-passe errada
            header("Location: login.php?erro=pass");
            exit();
        }
    } else {
        // Utilizador/USER_ID não foi encontrado
        header("Location: login.php?erro=user");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>