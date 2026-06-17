<?php
// ==========================================================================
// CENTRAL OPERACIONAL DE SISTEMAS - TECH CREW
// FICHEIRO: EXPORTAR_SEMANAL_PDF.PHP (VERSÃO COMPLETA - CORRIGIDA SEM CRASH)
// Objetivo: Gerar o relatório semanal formal (Suporte a Imagens e Sistema Antifalha)
// ==========================================================================

require_once 'auth.php';
require_once 'db.php';

// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------------------------------
// FUNÇÃO INTELIGENTE: EXTRAIR ID DO GOOGLE DRIVE (ACEITA LINK OU ID BRUTO)
// --------------------------------------------------------------------------
function pdf_extrair_id_drive($input) {
    $input = trim($input);
    if (empty($input) || strtoupper($input) === 'NULL') {
        return '';
    }
    if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $input, $matches)) {
        return $matches[1];
    }
    if (preg_match('/id=([a-zA-Z0-9_-]+)/', $input, $matches)) {
        return $matches[1];
    }
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

// Resgatar os parâmetros de filtro enviados pelo semanal.php
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'individual';
$tecnico_alvo = isset($_GET['tecnico']) ? $_GET['tecnico'] : 'todos';

$nome_utilizador_logado = 'Operador Técnico';
if (isset($_SESSION['user_nome'])) { $nome_utilizador_logado = $_SESSION['user_nome']; }
elseif (isset($_SESSION['nome'])) { $nome_utilizador_logado = $_SESSION['nome']; }
elseif (isset($_SESSION['username'])) { $nome_utilizador_logado = $_SESSION['username']; }

$titulo_documento = "Relatório Semanal de Atividades";
$sub_titulo_documento = "Central Operacional de Sistemas";
$meta_tecnico = $tecnico_alvo;

// Extração dos dados baseada na tabela 'relatorios'
if ($tipo === 'geral' || $tecnico_alvo === 'todos') {
    $titulo_documento = "Relatório Geral de Atividades";
    $sub_titulo_documento = "Consolidado Semanal do Sistema";
    $meta_tecnico = "Todos os Utilizadores (Geral)";
    
    $sql = "SELECT * FROM relatorios 
            WHERE data_envio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY data_envio DESC, id DESC";
            
    if ($db_type === 'pdo') {
        $stmt = $db_link->query($sql);
        $atividades = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } else {
        $res = $db_link->query($sql);
        $atividades = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
} else {
    $sql = "SELECT * FROM relatorios 
            WHERE autor = ? AND data_envio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY data_envio DESC, id DESC";

    if ($db_type === 'pdo') {
        $stmt = $db_link->prepare($sql);
        if ($stmt) {
            $stmt->execute([$tecnico_alvo]);
            $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $stmt = $db_link->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $tecnico_alvo);
            $stmt->execute();
            $res = $stmt->get_result();
            $atividades = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
    }
}

// Montagem do HTML formal para o PDF (Capa e Índice inclusos — Sem Vibe Hacker)
$html = '
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($titulo_documento) . '</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: "Helvetica", "Arial", sans-serif; color: #333333; line-height: 1.6; background-color: #ffffff; margin: 0; padding: 0; }
        
        /* --- ESTILO FORMAL DA CAPA --- */
        .capa-container { text-align: center; padding-top: 60px; height: 100vh; position: relative; box-sizing: border-box; page-break-after: always; }
        .escola-cabecalho { font-size: 14pt; letter-spacing: 2px; text-transform: uppercase; color: #4a5568; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 80px; font-weight: bold; }
        .titulo-principal { font-size: 30pt; color: #1a365d; margin-bottom: 15px; font-weight: bold; line-height: 1.2; }
        .sub-titulo { font-size: 16pt; color: #4a5568; margin-bottom: 150px; }
        .metadados-capa { border-top: 1px solid #cbd5e0; padding-top: 20px; width: 85%; margin: 0 auto; text-align: left; }
        .linha-meta { margin-bottom: 10px; font-size: 12pt; }
        .rotulo-meta { font-weight: bold; color: #1a365d; display: inline-block; width: 160px; }
        
        /* --- ESTILO DO ÍNDICE --- */
        .indice-container { page-break-after: always; padding-top: 40px; }
        .titulo-indice { font-size: 20pt; color: #1a365d; border-bottom: 1px solid #1a365d; padding-bottom: 6px; margin-bottom: 40px; font-weight: bold; }
        .tabela-indice { width: 100%; border-collapse: collapse; }
        .tabela-indice td { padding: 12px 0; font-size: 12pt; }
        .pontos-guia { border-bottom: 1px dotted #a0aec0; }
        .num-pagina { text-align: right; font-weight: bold; color: #1a365d; width: 40px; }
        
        /* --- CONTEÚDO DO RELATÓRIO --- */
        .corpo-container { padding-top: 20px; }
        .seccao-titulo { font-size: 16pt; color: #1a365d; border-left: 4px solid #2b6cb0; padding-left: 10px; margin-top: 40px; margin-bottom: 20px; font-weight: bold; page-break-after: avoid; }
        .tabela-dados { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 10.5pt; page-break-inside: auto; }
        .tabela-dados tr { page-break-inside: avoid; page-break-after: auto; }
        .tabela-dados th { background-color: #2b6cb0; color: #ffffff; font-weight: bold; text-align: left; padding: 12px; border: 1px solid #2b6cb0; }
        .tabela-dados td { padding: 12px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; vertical-align: top; }
        .tabela-dados tr:nth-child(even) { background-color: #f7fafc; }
        .texto-introducao { font-size: 11.5pt; margin-bottom: 25px; color: #2d3748; text-align: justify; }
        
        /* IMAGEM FORMATADA PARA O PDF COMPILADO */
        .pdf-drive-img {
            display: block;
            width: 160px;
            height: auto;
            margin-top: 8px;
            border: 1px solid #cbd5e0;
        }

        @media print {
            .capa-container { height: auto; page-break-after: always; }
            .indice-container { page-break-after: always; }
            nav, button, .btn-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="capa-container">
        <div class="escola-cabecalho">Escola Secundária Terras de Larus — Tech Crew</div>
        <div class="titulo-principal">' . htmlspecialchars($titulo_documento) . '</div>
        <div class="sub-titulo">' . htmlspecialchars($sub_titulo_documento) . '</div>
        
        <div class="metadados-capa">
            <div class="linha-meta"><span class="rotulo-meta">Entidade Emissora:</span> Central Operacional de Sistemas</div>
            <div class="linha-meta"><span class="rotulo-meta">Responsável/Técnico:</span> ' . htmlspecialchars($meta_tecnico) . '</div>
            <div class="linha-meta"><span class="rotulo-meta">Âmbito de Análise:</span> Atividades Escolares Recentes</div>
            <div class="linha-meta"><span class="rotulo-meta">Data de Emissão:</span> ' . date('d/m/Y') . '</div>
        </div>
    </div>

    <div class="indice-container">
        <div class="titulo-indice">Índice Geral do Documento</div>
        <table class="tabela-indice">
            <tr>
                <td style="width: 45%;">1. Introdução e Enquadramento Técnico</td>
                <td class="pontos-guia"></td>
                <td class="num-pagina">3</td>
            </tr>
            <tr>
                <td>2. Histórico de Atividades Realizadas na Semana</td>
                <td class="pontos-guia"></td>
                <td class="num-pagina">3</td>
            </tr>
        </table>
    </div>

    <div class="corpo-container">
        <div class="seccao-titulo">1. Introdução e Enquadramento Técnico</div>
        <p class="texto-introducao">Este documento formal apresenta os registos consolidados das manutenções, configurações e intervenções gerais efetuadas no parque informático e infraestruturas de rede desta instituição de ensino durante o ciclo de avaliação semanal.</p>
        
        <div class="seccao-titulo">2. Histórico de Atividades Realizadas na Semana</div>
        <table class="tabela-dados">
            <thead>
                <tr>
                    <th style="width: 15%;">Data de Envio</th>
                    ' . (($tipo === 'geral' || $tecnico_alvo === 'todos') ? '<th style="width: 25%;">Técnico / Autor</th>' : '') . '
                    <th>Descrição das Ações Técnicas Realizadas</th>
                    <th style="width: 15%;">Período</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($atividades as $linha) {
                $data_formatada = isset($linha['data_envio']) ? date('d/m/Y', strtotime($linha['data_envio'])) : date('d/m/Y');
                
                $html .= '<tr>
                    <td>' . $data_formatada . '</td>';
                    if ($tipo === 'geral' || $tecnico_alvo === 'todos') {
                        $html .= '<td style="font-weight: bold; color: #2b6cb0;">' . htmlspecialchars($linha['autor'] ?? '') . '</td>';
                    }
                $html .= '<td>' . htmlspecialchars($linha['conteudo'] ?? '');
                
                // NOVO MOTOR COMPATÍVEL BYPASS PARA RENDERIZAÇÃO DE IMAGENS DO DRIVE NO PDF
                $pdf_drive_id = pdf_extrair_id_drive($linha['foto'] ?? '');
                if (!empty($pdf_drive_id)) {
                    $html .= '<br><img src="https://drive.google.com/thumbnail?id=' . $pdf_drive_id . '&sz=s800" class="pdf-drive-img">';
                }
                
                $html .= '</td>
                    <td style="color: #666666;">' . htmlspecialchars($linha['semana_ano'] ?? '') . '</td>
                </tr>';
            }

$html .= '
            </tbody>
        </table>
    </div>

</body>
</html>';

// --------------------------------------------------------------------------
// PROCURA DINÂMICA DO DOMPDF SEM CRASHAR LINHA 12 (SISTEMA ANTIFALHA MASTER)
// --------------------------------------------------------------------------
$caminhos_autoload = [
    'dompdf/autoload.inc.php',
    '../dompdf/autoload.inc.php',
    'vendor/autoload.php',
    '../vendor/autoload.php'
];

$carregou_dompdf = false;
foreach ($caminhos_autoload as $caminho) {
    if (file_exists($caminho)) {
        require_once $caminho;
        $carregou_dompdf = true;
        break;
    }
}

if ($carregou_dompdf && class_exists('Dompdf\Dompdf')) {
    // Se localizou a biblioteca Dompdf, compila o arquivo PDF em background
    $options = new Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Permitir download remoto das miniaturas do Drive
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $nome_ficheiro = "Relatorio_Semanal_Escolar_" . date('Ymd') . ".pdf";
    $dompdf->stream($nome_ficheiro, array("Attachment" => true));
    exit();
} else {
    // FALLBACK DE SEGURANÇA: Dispara o Driver de Impressão Nativa de Alta Fidelidade do Browser
    $html_contingencia = str_replace('</body>', '<script>window.onload = function() { window.print(); }</script></body>', $html);
    echo $html_contingencia;
    exit();
}
?>