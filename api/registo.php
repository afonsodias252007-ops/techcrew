<?php
include('auth.php');
include('db.php');

// Segurança: Apenas administradores podem gerir e visualizar as contas do sistema
verificarAdmin();

$mensagem = "";
$erro = "";

// Variáveis de controlo para o formulário dinâmico (Modo Edição vs Modo Criação)
$edit_id = 0;
$edit_username = "";
$edit_role = "user";

// ==========================================
// 1. AÇÃO: RECOLHER DADOS PARA EDIÇÃO (GET)
// ==========================================
if (isset($_GET['editar'])) {
    $edit_id = (int)$_GET['editar'];
    $busca_edit = mysqli_query($conexao, "SELECT * FROM utilizadores WHERE id = $edit_id");
    if ($dados_edit = mysqli_fetch_assoc($busca_edit)) {
        $edit_username = $dados_edit['username'];
        $edit_role = $dados_edit['role'];
    }
}

// ==========================================
// 2. AÇÃO: APAGAR UTILIZADOR (GET)
// ==========================================
if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    
    if ($id_excluir === (int)$_SESSION['user_id']) {
        $erro = "❌ Operação abortada! Não podes apagar a tua própria conta enquanto estás ligado.";
    } else {
        $query_del = "DELETE FROM utilizadores WHERE id = $id_excluir";
        if (mysqli_query($conexao, $query_del)) {
            $mensagem = "✅ Utilizador eliminado com sucesso do terminal.";
        } else {
            $erro = "❌ Erro ao eliminar utilizador: " . mysqli_error($conexao);
        }
    }
}

// ==========================================
// 3. AÇÃO: PROCESSAR FORMULÁRIO (POST - CRIAR OU ATUALIZAR)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bt_salvar'])) {
    $id = (int)$_POST['id_utilizador'];
    $username = mysqli_real_escape_string($conexao, trim($_POST['username']));
    $role = mysqli_real_escape_string($conexao, $_POST['role']);
    $password_crua = trim($_POST['password']);

    if (empty($username)) {
        $erro = "❌ O nome de utilizador não pode ficar vazio.";
    } else {
        if ($id > 0) {
            // ----------------------------------
            // MODO EDIÇÃO / ATUALIZAÇÃO DE CONTA
            // ----------------------------------
            
            // 🌟 VALIDAÇÃO: Verifica se o novo username já pertence a OUTRO utilizador
            $check_repetido = mysqli_query($conexao, "SELECT id FROM utilizadores WHERE username='$username' AND id != $id");
            if (mysqli_num_rows($check_repetido) > 0) {
                $erro = "❌ Erro: O nome de utilizador '$username' já está a ser usado por outra conta.";
            } else {
                if (!empty($password_crua)) {
                    // Atualiza tudo incluindo a nova password (com hash MD5 ou a que usares no login)
                    $password_hash = md5($password_crua);
                    $query_update = "UPDATE utilizadores SET username='$username', password='$password_hash', role='$role' WHERE id=$id";
                } else {
                    // Mantém a password antiga intacta
                    $query_update = "UPDATE utilizadores SET username='$username', role='$role' WHERE id=$id";
                }
                
                if (mysqli_query($conexao, $query_update)) {
                    $mensagem = "✅ Conta de utilizador atualizada com sucesso.";
                    $edit_id = 0; $edit_username = ""; $edit_role = "user"; // Reset formulário
                } else {
                    $erro = "❌ Falha ao atualizar dados: " . mysqli_error($conexao);
                }
            }
        } else {
            // ----------------------------------
            // MODO CRIAÇÃO / NOVA CONTA
            // ----------------------------------
            if (empty($password_crua)) {
                $erro = "❌ É obrigatório definir uma palavra-passe para novas contas.";
            } else {
                
                // 🌟 VALIDAÇÃO CRÍTICA: Bloqueia se o username já existir na base de dados
                $check_existe = mysqli_query($conexao, "SELECT id FROM utilizadores WHERE username='$username'");
                if (mysqli_num_rows($check_existe) > 0) {
                    $erro = "❌ Erro de Sistema: O utilizador '$username' já se encontra registado na base de dados.";
                } else {
                    $password_hash = md5($password_crua);
                    $query_insert = "INSERT INTO utilizadores (username, password, role) VALUES ('$username', '$password_hash', '$role')";
                    if (mysqli_query($conexao, $query_insert)) {
                        $mensagem = "✅ Nova conta registada com sucesso no ecossistema.";
                    } else {
                        $erro = "❌ Erro ao injetar conta: " . mysqli_error($conexao);
                    }
                }
            }
        }
    }
}

// Puxar a lista de contas
$utilizadores = mysqli_query($conexao, "SELECT id, username, role FROM utilizadores ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Registo e Gestão de Contas</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .split-container { display: flex; gap: 25px; margin-top: 30px; }
        .col-form { flex: 1; }
        .col-lista { flex: 2; }
        .alert-sucesso { padding: 12px; background: rgba(0, 255, 102, 0.1); border: 1px solid var(--neon); color: var(--neon); margin-bottom: 20px; border-radius: 6px; font-weight: bold; }
        .alert-erro { padding: 12px; background: rgba(255, 59, 59, 0.1); border: 1px solid var(--danger); color: var(--danger); margin-bottom: 20px; border-radius: 6px; font-weight: bold; }
        .badge-role { padding: 3px 8px; font-size: 11px; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
        .badge-admin { background: rgba(255, 230, 0, 0.15); color: var(--yellow); border: 1px solid var(--yellow); }
        .badge-user { background: rgba(0, 217, 255, 0.15); color: var(--cyan); border: 1px solid var(--cyan); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { padding: 12px; text-align: left; color: var(--neon); border-bottom: 2px solid var(--border); font-size: 12px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid rgba(0,255,102,0.1); color: #fff; font-size: 13px; vertical-align: middle; }
        .btn-pequeno { padding: 5px 10px; font-size: 11px; font-weight: bold; text-decoration: none; border-radius: 4px; margin-right: 5px; display: inline-block; cursor: pointer !important; }
        .btn-cancelar { background: #333; color: #fff; border: 1px solid #555; text-decoration: none; display: flex; align-items: center; justify-content: center; padding: 0 15px; border-radius: 6px; }
    </style>
</head>
<body>
    <a href="index.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; box-shadow: var(--shadow); z-index: 9999;">⬅ Voltar ao Painel</a>

    <div class="container" style="max-width: 1200px; margin-top: 40px;">
        
        <div class="terminal-header" style="margin-bottom: 25px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="logo.jpg" alt="Tech Crew" class="terminal-logo">
                <div>
                    <h1>AUTHENTICATION_GATEWAY // CONTROL_PANEL</h1>
                    <p>Registo e manutenção de credenciais de operadores e privilégios de acesso.</p>
                </div>
            </div>
        </div>

        <?php if(!empty($mensagem)): ?> <div class="alert-sucesso"><?php echo $mensagem; ?></div> <?php endif; ?>
        <?php if(!empty($erro)): ?> <div class="alert-erro"><?php echo $erro; ?></div> <?php endif; ?>

        <div class="split-container">
            
            <div class="col-form">
                <div class="card-painel">
                    <h2 style="color: var(--neon); margin-bottom: 20px; font-size: 15px;">
                        [ <?php echo $edit_id > 0 ? "MODIFICAR_CONTA_#".$edit_id : "INSERIR_NOVO_UTILIZADOR"; ?> ]
                    </h2>
                    
                    <form action="registo.php" method="POST">
                        <input type="hidden" name="id_utilizador" value="<?php echo $edit_id; ?>">

                        <div class="form-group">
                            <label for="username">NOME DE UTILIZADOR (LOGIN):</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($edit_username); ?>" placeholder="Ex: hugo.tecnico" required>
                        </div>

                        <div class="form-group">
                            <label for="password">
                                PALAVRA-PASSE: 
                                <?php if($edit_id > 0): ?>
                                    <span style="color: var(--yellow); font-size: 11px; font-weight: normal;"><br>(Deixar em branco para MANTER a atual)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" id="password" name="password" placeholder="<?php echo $edit_id > 0 ? '••••••••' : 'Defina a palavra-passe'; ?>" <?php echo $edit_id > 0 ? '' : 'required'; ?>>
                        </div>

                        <div class="form-group">
                            <label for="role">NÍVEL DE AUTORIDADE (PERMISSÕES):</label>
                            <select id="role" name="role" style="width: 100%; padding: 12px; background: #000; border: 1px solid var(--border); color: #fff; border-radius: 6px;">
                                <option value="user" <?php if($edit_role == 'user') echo 'selected'; ?>>USER (Operador Técnico)</option>
                                <option value="admin" <?php if($edit_role == 'admin') echo 'selected'; ?>>ADMIN (Administrador Global)</option>
                            </select>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" name="bt_salvar" class="btn" style="flex: 1;">[ COMPILAR_DADOS ]</button>
                            <?php if($edit_id > 0): ?>
                                <a href="registo.php" class="btn-cancelar" title="Cancelar Edição">[ X ]</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lista">
                <div class="card-painel" style="height: 100%;">
                    <h2 style="color: var(--cyan); margin-bottom: 15px; font-size: 15px;">[ CONTAS_ATIVAS_NA_BASE_DE_DADOS ]</h2>
                    
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 12%;">ID</th>
                                    <th style="width: 43%;">UTILIZADOR (USERNAME)</th>
                                    <th style="width: 20%;">PERMISSÕES</th>
                                    <th style="width: 25%; text-align: right;">AÇÕES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($u = mysqli_fetch_assoc($utilizadores)): ?>
                                <tr>
                                    <td style="color: var(--cyan); font-weight: bold;">#<?php echo $u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                    <td>
                                        <?php if($u['role'] == 'admin'): ?>
                                            <span class="badge-role badge-admin">ADMIN</span>
                                        <?php else: ?>
                                            <span class="badge-role badge-user">USER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <a href="registo.php?editar=<?php echo $u['id']; ?>" class="btn-pequeno btn-warning" style="color:#000;">EDITAR</a>
                                        <a href="registo.php?excluir=<?php echo $u['id']; ?>" class="btn-pequeno btn-danger" onclick="return confirm('Tem a certeza absoluta que deseja eliminar a conta de <?php echo $u['username']; ?> permanentemente?')">APAGAR</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>