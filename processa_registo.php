<?php
include('auth.php'); // Garante que está logado
verificarAdmin();    // Garante que APENAS o Admin pode executar este script
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura e higieniza os dados recebidos
    $username = trim(mysqli_real_escape_string($conexao, $_POST['username']));
    $password_crua = $_POST['password'];
    $role = $_POST['role'];

    // 1. Validar se o utilizador já existe na base de dados
    $check_query = "SELECT id FROM utilizadores WHERE username = '$username'";
    $check_result = mysqli_query($conexao, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Se o utilizador já existir, volta com uma mensagem de erro
        header("Location: registo.php?erro=existe");
        exit();
    } else {
        // 2. Segurança Máxima: Encripta a password usando a hash nativa do PHP
        $password_segura = password_hash($password_crua, PASSWORD_DEFAULT);

        // 3. Insere o utilizador na tabela
        $insert_query = "INSERT INTO utilizadores (username, password, role) VALUES ('$username', '$password_segura', '$role')";
        
        if (mysqli_query($conexao, $insert_query)) {
            // Sucesso! Redireciona de volta
            header("Location: registo.php?sucesso=1");
            exit();
        } else {
            die("Erro crítico ao registar credenciais: " . mysqli_error($conexao));
        }
    }
} else {
    // Se tentarem aceder ao ficheiro de forma direta, manda de volta para o formulário
    header("Location: registo.php");
    exit();
}
?>