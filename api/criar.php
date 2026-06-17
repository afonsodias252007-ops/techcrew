<?php
include('auth.php'); // Garante que o session_start() é corrido e bloqueia invasores deslogados
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CAPTURA AUTOMÁTICA DA SESSÃO:
    // Usamos o 'user_id' para saber quem está logado de forma trancada e inviolável
    $autor_id = (int)$_SESSION['user_id'];
    
    // Se quiseres continuar a guardar o Nome textual na tabela de relatórios, 
    // podemos fazer um pequeníssimo SELECT rápido para puxar o username real a partir do ID da sessão:
    $busca_user = mysqli_query($conexao, "SELECT username FROM utilizadores WHERE id = $autor_id");
    $dados_user = mysqli_fetch_assoc($busca_user);
    $autor = mysqli_real_escape_string($conexao, $dados_user['username'] ?? 'Operador Anónimo');

    $data_envio = $_POST['data_envio'];
    $conteudo = mysqli_real_escape_string($conexao, $_POST['conteudo']);
    
    // Cálculo automatizado e estável da semana ISO-8601
    $semana_ano = date('Y', strtotime($data_envio)) . '-W' . date('W', strtotime($data_envio));
    
    $ids_drive = [];

    // Processa os múltiplos links enviados pelo terminal de evidências
    if (!empty($_POST['fotos_urls'])) {
        $linhas_links = explode("\n", $_POST['fotos_urls']);
        
        foreach ($linhas_links as $link) {
            $link_limpo = trim($link);
            if (empty($link_limpo)) continue;

            // Expressão regular estável para isolar o ID único do Google Drive
            if (preg_match('/([a-zA-Z0-9-_]{25,})/', $link_limpo, $matches)) {
                $ids_drive[] = $matches[1];
            } else {
                $ids_drive[] = mysqli_real_escape_string($conexao, $link_limpo);
            }
        }
    }

    // Junta todos os IDs limpos e extraídos por vírgulas para a BD
    $foto_campo = implode(',', $ids_drive);

    // Injeção segura na Base de Dados
    $query = "INSERT INTO relatorios (autor, data_envio, semana_ano, conteudo, foto) VALUES ('$autor', '$data_envio', '$semana_ano', '$conteudo', '$foto_campo')";
    
    if (mysqli_query($conexao, $query)) {
        header('Location: relatorio.php');
        exit;
    } else {
        die("❌ ERRO CRÍTICO CRONOS: Falha ao injetar registo técnico na BD: " . mysqli_error($conexao));
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Inserir Registo Técnico</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-group input[readonly] {
            background: rgba(255, 255, 255, 0.02) !important;
            color: var(--neon) !important;
            border-color: rgba(0, 255, 102, 0.1) !important;
            cursor: not-allowed !important;
            font-weight: bold;
            text-transform: uppercase;
        }
        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        .btn-cancelar {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 750px; margin-top: 40px;">
        <div class="card-painel">
            
            <div class="terminal-header" style="margin-bottom: 25px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img src="logo.jpg" alt="Tech Crew" class="terminal-logo">
                    <div>
                        <h1>NEW_LOG_ENTRY // ENTRADA DE RELATÓRIO</h1>
                        <p>Controlo de acessos ativo. Identidade do operador protegida e automatizada.</p>
                    </div>
                </div>
            </div>

            <form action="criar.php" method="POST">
                
                <div class="form-group">
                    <label for="autor_display">OPERADOR SYSTEM_ID (IDENTIFICAÇÃO DE SESSÃO):</label>
                    <?php 
                    // Puxa o nome para mostrar no ecrã de forma meramente informativa
                    $id_atual = (int)$_SESSION['user_id'];
                    $check = mysqli_query($conexao, "SELECT username FROM utilizadores WHERE id = $id_atual");
                    $u_dados = mysqli_fetch_assoc($check);
                    ?>
                    <input type="text" id="autor_display" value="<?php echo htmlspecialchars($u_dados['username'] ?? 'Indefinido'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="data_envio">TIMESTAMP (DATA DA OPERAÇÃO):</label>
                    <input type="date" id="data_envio" name="data_envio" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="conteudo">LOG_CONTENT (RELATÓRIO DETALHADO):</label>
                    <textarea id="conteudo" name="conteudo" placeholder="Descreva de forma clara e detalhada as operações técnicas efetuadas hoje..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="fotos_urls">EVIDÊNCIAS GOOGLE DRIVE (UM LINK DE PARTILHA POR LINHA):</label>
                    <textarea id="fotos_urls" name="fotos_urls" placeholder="https://drive.google.com/file/d/XXXXX/view&#10;Cole múltiplos links quebrando a linha..." style="height: 120px; font-family: monospace; color: var(--cyan); background: #000; padding: 12px;"></textarea>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn" style="flex: 2;">[ SUBMETER_LOG_DADOS ]</button>
                    <a href="relatorio.php" class="btn btn-danger btn-cancelar">[ CANCELAR ]</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>