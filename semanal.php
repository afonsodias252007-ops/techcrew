<?php
// ==========================================================================
// CENTRAL OPERACIONAL DE SISTEMAS - TECH CREW
// FICHEIRO: SEMANAL.PHP (VERSÃO MASTER - LH3 DRIVE ENGINE)
// Interface de Monitorização Semanal com Novo Motor de Imagens Google LH3
// ==========================================================================

// Iniciar a sessão e verificar autenticação da Central Operacional
require_once 'auth.php'; 
require_once 'db.php';

// Garantir que a sessão está activa caso o auth.php não a tenha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------------------------------
// FUNÇÃO INTELIGENTE: EXTRAIR ID DO GOOGLE DRIVE (ACEITA LINK OU ID BRUTO)
// --------------------------------------------------------------------------
function extrair_id_drive($input) {
    $input = trim($input);
    if (empty($input) || strtoupper($input) === 'NULL') {
        return '';
    }
    
    // Se for um link completo de partilha do Drive
    if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $input, $matches)) {
        return $matches[1];
    }
    
    // Se for um link antigo com ?id=
    if (preg_match('/id=([a-zA-Z0-9_-]+)/', $input, $matches)) {
        return $matches[1];
    }
    
    // Se não contiver barras nem pontos, assumimos que já é o ID limpo
    if (strpos($input, '/') === false && strpos($input, '.') === false) {
        return $input;
    }
    
    return '';
}

// --------------------------------------------------------------------------
// MOTOR DE MAPEAMENTO HÍBRIDO (AUTO-DETEÇÃO DE PDO OU MYSQLI)
// --------------------------------------------------------------------------
$db_link = null;
$db_type = ''; 

$nomes_comuns = ['conn', 'ligacao', 'link', 'con', 'db', 'database', 'connect', 'pdo'];
foreach ($nomes_comuns as $nc) {
    if (isset($$nc) && is_object($$nc)) {
        if ($$nc instanceof PDO) { $db_link = $$nc; $db_type = 'pdo'; break; }
        elseif ($$nc instanceof mysqli) { $db_link = $$nc; $db_type = 'mysqli'; break; }
    }
}

if (!$db_link) {
    foreach (get_defined_vars() as $nome_variavel => $conteudo_variavel) {
        if (is_object($conteudo_variavel)) {
            if ($conteudo_variavel instanceof PDO) { $db_link = $conteudo_variavel; $db_type = 'pdo'; break; }
            elseif ($conteudo_variavel instanceof mysqli) { $db_link = $conteudo_variavel; $db_type = 'mysqli'; break; }
        }
    }
}

if (!$db_link) {
    die("<div style='background:#000; color:#ff3333; padding:20px; font-family:monospace; border:2px solid #ff3333;'>
            [ERR_CRITICAL] LINK DE BASE DE DADOS NÃO MAPEADO NO SEU db.php
         </div>");
}

// --------------------------------------------------------------------------
// DETEÇÃO AUTOMÁTICA DE CHAVES DE SESSÃO DO UTILIZADOR
// --------------------------------------------------------------------------
$nome_utilizador_logado = 'Operador Técnico';
if (isset($_SESSION['user_nome'])) { $nome_utilizador_logado = $_SESSION['user_nome']; }
elseif (isset($_SESSION['nome'])) { $nome_utilizador_logado = $_SESSION['nome']; }
elseif (isset($_SESSION['username'])) { $nome_utilizador_logado = $_SESSION['username']; }

$nivel_acesso = 'user'; 
if (isset($_SESSION['user_nivel'])) { $nivel_acesso = $_SESSION['user_nivel']; }
elseif (isset($_SESSION['nivel'])) { $nivel_acesso = $_SESSION['nivel']; }
elseif (isset($_SESSION['role'])) { $nivel_acesso = $_SESSION['role']; }
elseif (isset($_SESSION['tipo'])) { $nivel_acesso = $_SESSION['tipo']; }

// --------------------------------------------------------------------------
// CONFIGURAÇÃO DE FILTROS BASEADOS NA COLUNA 'AUTOR' (PERMISSÕES)
// --------------------------------------------------------------------------
$filtro_tecnico = 'todos';
$tipo_relatorio = "geral";
$nome_tecnico_relatorio = "ALL_OPERATORS";

if (!($nivel_acesso === 'admin' || $nivel_acesso === 'administrador' || strtolower($nome_utilizador_logado) === 'dias')) {
    $filtro_tecnico = $nome_utilizador_logado;
    $tipo_relatorio = "individual";
    $nome_tecnico_relatorio = strtoupper($filtro_tecnico);
} else {
    if (isset($_GET['filtrar_tecnico']) && $_GET['filtrar_tecnico'] !== 'todos') {
        $filtro_tecnico = $_GET['filtrar_tecnico'];
        $tipo_relatorio = "individual_admin";
        $nome_tecnico_relatorio = strtoupper($filtro_tecnico);
    }
}

// --------------------------------------------------------------------------
// EXTRAÇÃO DE DADOS (ÚLTIMOS 7 DIAS)
// --------------------------------------------------------------------------
$atividades = [];
$erro_sql = null;

if ($filtro_tecnico === 'todos') {
    $sql = "SELECT * FROM relatorios 
            WHERE data_envio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY data_envio DESC, id DESC";
            
    if ($db_type === 'pdo') {
        $stmt = $db_link->query($sql);
        if ($stmt !== false) { $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC); }
        else { $info_erro = $db_link->errorInfo(); $erro_sql = $info_erro[2] ?? 'Erro SQL.'; }
    } else {
        $res = $db_link->query($sql);
        if ($res !== false) { $atividades = $res->fetch_all(MYSQLI_ASSOC); }
        else { $erro_sql = $db_link->error; }
    }
} else {
    $sql = "SELECT * FROM relatorios 
            WHERE autor = ? AND data_envio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY data_envio DESC, id DESC";

    if ($db_type === 'pdo') {
        $stmt = $db_link->prepare($sql);
        if ($stmt !== false) {
            $stmt->execute([$filtro_tecnico]);
            $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else { $info_erro = $db_link->errorInfo(); $erro_sql = $info_erro[2]; }
    } else {
        $stmt = $db_link->prepare($sql);
        if ($stmt !== false) {
            $stmt->bind_param("s", $filtro_tecnico);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res !== false) { $atividades = $res->fetch_all(MYSQLI_ASSOC); }
        } else { $erro_sql = $db_link->error; }
    }
}

// Criar a lista de técnicos dinamicamente a partir dos autores ativos
$lista_tecnicos_dropdown = [];
if ($nivel_acesso === 'admin' || $nivel_acesso === 'administrador' || strtolower($nome_utilizador_logado) === 'dias') {
    $sql_autores = "SELECT DISTINCT autor FROM relatorios WHERE autor IS NOT NULL AND autor != '' ORDER BY autor ASC";
    if ($db_type === 'pdo') {
        $stmt_autores = $db_link->query($sql_autores);
        if ($stmt_autores !== false) { $lista_tecnicos_dropdown = $stmt_autores->fetchAll(PDO::FETCH_COLUMN); }
    } else {
        $res_autores = $db_link->query($sql_autores);
        if ($res_autores !== false) {
            while ($row = $res_autores->fetch_row()) {
                $lista_tecnicos_dropdown[] = $row[0];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYS.LOG // MONITOR_SEMANAL</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600;700&display=swap');

        body {
            background-color: #030303 !important;
            color: #00ff66 !important;
            font-family: 'Fira Code', 'Courier New', monospace !important;
            padding: 20px;
            margin: 0;
        }

        .terminal-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        .terminal-header {
            border-bottom: 2px dashed #00ff66;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .terminal-header h1 {
            color: #00ff66 !important;
            font-size: 24px !important;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-shadow: 0 0 8px rgba(0, 255, 102, 0.6);
            margin: 0 0 10px 0;
        }

        .terminal-status {
            font-size: 12px;
            color: #00d9ff;
            text-transform: uppercase;
        }

        .hacker-panel-admin {
            background-color: #0a0a0a;
            border: 1px solid #00d9ff;
            box-shadow: 0 0 12px rgba(0, 217, 255, 0.15);
            padding: 15px;
            margin-bottom: 25px;
        }

        .hacker-panel-admin h2 {
            color: #00d9ff !important;
            font-size: 14px !important;
            text-shadow: 0 0 5px #00d9ff;
        }

        .hacker-panel-admin select {
            background: #000 !important;
            color: #00d9ff !important;
            border: 1px solid #00d9ff !important;
            padding: 8px 12px;
            font-family: 'Fira Code', monospace;
            width: 100%;
            max-width: 400px;
            outline: none;
        }

        .hacker-panel-main {
            background-color: #050505;
            border: 1px solid #00ff66;
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.1);
            padding: 20px;
            width: 100%;
            overflow: hidden;
        }

        .hacker-panel-main h2 {
            color: #00ff66 !important;
            font-size: 15px !important;
            text-shadow: 0 0 5px #00ff66;
            margin-bottom: 20px;
        }

        .btn-terminal-back {
            background: transparent;
            color: #8b949e;
            border: 1px solid #333;
            padding: 8px 16px;
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            text-decoration: none;
            text-transform: uppercase;
        }
        .btn-terminal-back:hover {
            color: #fff;
            border-color: #fff;
            background: rgba(255,255,255,0.05);
        }

        .btn-terminal-pdf {
            background: transparent;
            color: #00d9ff;
            border: 1px solid #00d9ff;
            padding: 8px 16px;
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: bold;
            text-shadow: 0 0 4px #00d9ff;
        }
        .btn-terminal-pdf:hover {
            background: #00d9ff;
            color: #000;
            box-shadow: 0 0 15px #00d9ff;
            text-shadow: none;
        }

        .terminal-grid-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .terminal-grid {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 15px;
            background: #000;
        }

        .terminal-grid th:nth-child(1) { width: 13%; }
        .terminal-grid th:nth-child(2) { width: 17%; }
        .terminal-grid th:nth-child(3) { width: 58%; }
        .terminal-grid th:nth-child(4) { width: 12%; }

        .terminal-grid th {
            border: 1px solid #00ff66;
            background: rgba(0, 255, 102, 0.05);
            color: #00ff66;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }

        .terminal-grid td {
            border: 1px solid rgba(0, 255, 102, 0.2);
            padding: 12px 10px;
            font-size: 13px;
            color: #dcdcdc;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
        }

        .terminal-grid tr:hover {
            background: rgba(0, 255, 102, 0.02);
        }

        .td-date { color: #00d9ff !important; font-weight: bold; }
        .td-author { color: #ffff00 !important; font-weight: bold; }
        .td-week { color: #8b949e !important; text-align: center; }

        /* MULTI-CAMADA DE PREVIEW DO GOOGLE DRIVE */
        .drive-preview-img {
            display: block;
            max-width: 240px;
            max-height: 180px;
            border: 1px solid #00ff66;
            margin-top: 10px;
            box-shadow: 0 0 10px rgba(0, 255, 102, 0.25);
            background-color: #050505;
        }
    </style>
</head>
<body>
    <div class="terminal-container">
        
        <header class="terminal-header">
            <h1>SYS.OPERACIONAL // CORE_RELATÓRIOS_SEMANAIS</h1>
            <div class="terminal-status">
                LINK: SECURE_CHANNEL // OPERADOR: [<?php echo htmlspecialchars(strtoupper($nome_utilizador_logado)); ?>]
            </div>
        </header>

        <?php if (($nivel_acesso === 'admin' || $nivel_acesso === 'administrador' || strtolower($nome_utilizador_logado) === 'dias') && !empty($lista_tecnicos_dropdown)): ?>
            <div class="hacker-panel-admin">
                <h2>[CONEXÃO RESTRITA: SELECIONAR ALVO DE MONITORIZAÇÃO]</h2>
                <form method="GET" action="semanal.php">
                    <select name="filtrar_tecnico" id="filtrar_tecnico" onchange="this.form.submit()">
                        <option value="todos" <?php echo ($filtro_tecnico === 'todos') ? 'selected' : ''; ?>>[LOG_GERAL] - Compilar Todos os Operadores</option>
                        <?php foreach ($lista_tecnicos_dropdown as $tecnico): ?>
                            <option value="<?php echo htmlspecialchars($tecnico); ?>" <?php echo ($filtro_tecnico === $tecnico) ? 'selected' : ''; ?>>
                                [OPERADOR] -> <?php echo htmlspecialchars(strtoupper($tecnico)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <div class="hacker-panel-main">
            <h2>LOG_STREAM_RECENT // REGISTOS DE: <?php echo htmlspecialchars($nome_tecnico_relatorio); ?></h2>
            
            <div style="margin-bottom: 20px; display: flex; gap: 12px;">
                <a href="index.php" class="btn-terminal-back">< ESC_TERMINAL</a>
                <?php if ($erro_sql === null): ?>
                    <a href="exportar_semanal_pdf.php?tipo=<?php echo $tipo_relatorio; ?>&tecnico=<?php echo urlencode($filtro_tecnico); ?>" class="btn-terminal-pdf" target="_blank">
                        ⚡ COMPILAR_RELATORIO_OFICIAL.PDF
                    </a>
                <?php endif; ?>
            </div>

            <?php if (count($atividades) == 0): ?>
                <div style="color: #ffff00; border: 1px dashed #ffff00; padding: 12px;">[AVISO] Sem registos localizados nos logs nos últimos 7 dias.</div>
            <?php else: ?>
                <div class="terminal-grid-wrapper">
                    <table class="terminal-grid">
                        <thead>
                            <tr>
                                <th>TIMESTAMP</th>
                                <th>OPERADOR</th>
                                <th>AÇÕES_TÉCNICAS_COMPILADAS_STREAM</th>
                                <th>PERÍODO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($atividades as $linha): ?>
                                <tr>
                                    <td class="td-date"><?php echo date('d/m/Y', strtotime($linha['data_envio'])); ?></td>
                                    <td class="td-author"><?php echo htmlspecialchars($linha['autor']); ?></td>
                                    <td style="line-height: 1.5; color: #ffffff;">
                                        <?php echo htmlspecialchars($linha['conteudo']); ?>
                                        
                                        <?php 
                                        $drive_id = extrair_id_drive($linha['foto']);
                                        if (!empty($drive_id)): 
                                        ?>
                                            <a href="https://drive.google.com/file/d/<?php echo $drive_id; ?>/view" target="_blank">
                                                <img src="https://lh3.googleusercontent.com/d/<?php echo $drive_id; ?>" 
                                                     class="drive-preview-img" 
                                                     alt="Anexo Hardware"
                                                     onerror="this.onerror=null; this.src='https://drive.google.com/thumbnail?id=<?php echo $drive_id; ?>&sz=s800';">
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-week"><?php echo htmlspecialchars($linha['semana_ano']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>