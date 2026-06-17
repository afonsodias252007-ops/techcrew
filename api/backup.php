<?php
include('db.php');

// Caminhos para as pastas de salvaguarda
$pasta_backups = 'backups/';
if (!file_exists($pasta_backups)) {
    mkdir($pasta_backups, 0777, true);
}

// ==========================================
// 1. BACKUP DA BASE DE DADOS (.SQL)
// ==========================================
if (isset($_GET['gerar_sql'])) {
    $nome_ficheiro = $pasta_backups . 'backup_bd_' . date('Ymd_His') . '.sql';
    $handle = fopen($nome_ficheiro, 'w+');
    
    $tabelas = array();
    $result = mysqli_query($conexao, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tabelas[] = $row[0];
    }
    
    $sql_dump = "-- TECH CREW - BACKUP BD AUTOMÁTICO\n";
    $sql_dump .= "-- Gerado em: " . date('d/m/Y H:i:s') . "\n\n";
    
    foreach ($tabelas as $tabela) {
        $result = mysqli_query($conexao, "SHOW CREATE TABLE $tabela");
        $row = mysqli_fetch_row($result);
        $sql_dump .= "\n\n" . $row[1] . ";\n\n";
        
        $result = mysqli_query($conexao, "SELECT * FROM $tabela");
        $num_campos = mysqli_num_fields($result);
        
        while ($row = mysqli_fetch_row($result)) {
            $sql_dump .= "INSERT INTO $tabela VALUES(";
            for ($j=0; $j<$num_campos; $j++) {
                if (isset($row[$j])) {
                    $sql_dump .= '"' . mysqli_real_escape_string($conexao, $row[$j]) . '"';
                } else {
                    $sql_dump .= 'NULL';
                }
                if ($j < ($num_campos-1)) { $sql_dump .= ','; }
            }
            $sql_dump .= ");\n";
        }
    }
    
    fwrite($handle, $sql_dump);
    fclose($handle);
    
    header('Location: backup.php?sucesso=bd');
    exit;
}

// ==========================================
// 2. BACKUP DOS FICHEIROS DO PROJETO (.ZIP)
// ==========================================
if (isset($_GET['gerar_zip'])) {
    $nome_zip = $pasta_backups . 'backup_ficheiros_' . date('Ymd_His') . '.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($nome_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        // Obter o caminho real da pasta atual do projeto
        $diretorio_raiz = realpath('.');
        
        $arquivos = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($diretorio_raiz),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($arquivos as $nome => $arquivo) {
            // Ignorar diretórios (são adicionados automaticamente com os ficheiros)
            if (!$arquivo->isDir()) {
                $caminho_real = $arquivo->getRealPath();
                $caminho_relativo = substr($caminho_real, strlen($diretorio_raiz) + 1);

                // SEGURANÇA: Não incluir a própria pasta de backups dentro do ZIP para não criar um loop infinito!
                if (strpos($caminho_relativo, 'backups' . DIRECTORY_SEPARATOR) === 0 || $caminho_relativo === 'backups') {
                    continue;
                }

                $zip->addFile($caminho_real, $caminho_relativo);
            }
        }
        $zip->close();
        header('Location: backup.php?sucesso=zip');
        exit;
    } else {
        header('Location: backup.php?erro=1');
        exit;
    }
}

// ==========================================
// 3. PROCESSAR ELIMINAR QUALQUER BACKUP
// ==========================================
if (isset($_GET['eliminar'])) {
    $arq = basename($_GET['eliminar']); 
    if (file_exists($pasta_backups . $arq)) {
        unlink($pasta_backups . $arq);
    }
    header('Location: backup.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <a href="index.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; z-index: 99999; display: inline-block; cursor: pointer !important;">⬅ Voltar ao Painel</a>

    <div class="container" style="margin-top: 80px; max-width: 1200px; padding: 20px;">
        
        <?php if (isset($_GET['sucesso'])): ?>
            <div style="background: rgba(0, 255, 157, 0.1); border: 1px solid var(--neon); color: var(--neon); padding: 15px; border-radius: 6px; margin-bottom: 25px; font-weight: bold; font-size: 14px;">
                <?php if($_GET['sucesso'] == 'bd'): ?>
                    [ SUCESSO ] -> BACKUP DA BASE DE DADOS (.SQL) GERADO E GUARDADO!
                <?php else: ?>
                    [ SUCESSO ] -> BACKUP DOS FICHEIROS DO PROJETO (.ZIP) CONCLUÍDO COM SUCESSO!
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div style="display: block; width: 100%; margin-bottom: 30px;">
            <div class="card-painel" style="padding: 30px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); text-align: center;">
                <h2 style="color: var(--neon); margin-bottom: 10px;">[ SCRIPT DE SALVAGUARDA DE SISTEMAS ]</h2>
                <p style="color: #aaa; font-size: 13px; margin-bottom: 25px;">Garante a segurança total do ecossistema Tech Crew gerando cópias da Base de Dados ou dos Ficheiros locais.</p>
                
                <a href="backup.php?gerar_sql=1" style="display: inline-block; padding: 12px 25px; font-weight: bold; text-decoration: none; background: var(--bg-card); color: var(--neon); border: 1px solid var(--neon); border-radius: 6px; font-size: 13px; cursor: pointer !important; margin-right: 15px; box-shadow: 0 0 15px rgba(0,255,102,0.1);">
                    ⚡ COPIAR BASE DE DADOS (.SQL)
                </a>

                <a href="backup.php?gerar_zip=1" style="display: inline-block; padding: 12px 25px; font-weight: bold; text-decoration: none; background: var(--bg-card); color: var(--cyan); border: 1px solid var(--cyan); border-radius: 6px; font-size: 13px; cursor: pointer !important; box-shadow: 0 0 15px rgba(0,255,255,0.1);">
                    📦 COMPRIMIR FICHEIROS (.ZIP)
                </a>
            </div>
        </div>

        <div class="card-painel" style="padding: 25px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius);">
            <h3 style="color: var(--yellow); margin: 0 0 20px 0;">[ ARQUIVOS DE RESTAURO EM DISCO ]</h3>
            
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); color: var(--neon);">
                        <th style="padding: 10px; width: 45%;">NOME DO ARQUIVO</th>
                        <th style="padding: 10px; width: 25%;">DATA / HORA</th>
                        <th style="padding: 10px; width: 15%;">TAMANHO</th>
                        <th style="padding: 10px; width: 15%; text-align: right;">AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ler todos os ficheiros .sql e .zip na pasta
                    $arquivos = array_merge(glob($pasta_backups . "*.sql"), glob($pasta_backups . "*.zip"));
                    
                    if ($arquivos) {
                        // Ordenar por data de modificação (mais recentes primeiro)
                        array_multisort(array_map('filemtime', $arquivos), SORT_DESC, $arquivos);
                        
                        foreach ($arquivos as $arquivo) {
                            $nome_simples = basename($arquivo);
                            $data_modificacao = date('d/m/Y H:i:s', filemtime($arquivo));
                            $tamanho = round(filesize($arquivo) / 1024, 2);
                            
                            // Formatar exibição do tamanho (KB ou MB)
                            $tamanho_formatado = ($tamanho > 1024) ? round($tamanho / 1024, 2) . ' MB' : $tamanho . ' KB';
                            
                            // Ícone dinâmico baseado na extensão
                            $extensao = pathinfo($arquivo, PATHINFO_EXTENSION);
                            $icone = ($extensao == 'zip') ? '📦' : '📄';
                            $cor_nome = ($extensao == 'zip') ? 'var(--cyan)' : '#fff';
                    ?>
                        <tr style="border-bottom: 1px solid rgba(0,255,102,0.1);">
                            <td style="padding: 12px; color: <?php echo $cor_nome; ?>; font-weight: bold; font-family: monospace;">
                                <?php echo $icone . ' ' . $nome_simples; ?>
                            </td>
                            <td style="padding: 12px; color: #aaa;">
                                <?php echo $data_modificacao; ?>
                            </td>
                            <td style="padding: 12px; color: var(--yellow);">
                                <?php echo $tamanho_formatado; ?>
                            </td>
                            <td style="padding: 12px; text-align: right; vertical-align: middle;">
                                <a href="<?php echo $arquivo; ?>" download style="display: inline-block; padding: 5px 10px; font-size: 11px; font-weight: bold; text-decoration: none; background: transparent; color: var(--yellow); border: 1px solid var(--yellow); border-radius: 4px; margin-right: 5px; cursor: pointer !important;">
                                    DOWNLOAD
                                </a>
                                <a href="backup.php?eliminar=<?php echo $nome_simples; ?>" style="display: inline-block; padding: 5px 10px; font-size: 11px; font-weight: bold; text-decoration: none; background: var(--danger); color: #000; border: none; border-radius: 4px; cursor: pointer !important;" onclick="return confirm('Pretende apagar permanentemente este arquivo de backup?')">
                                    APAGAR
                                </a>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="4" style="padding: 25px; text-align: center; color: #555; font-style: italic;">
                                Nenhum arquivo de backup localizado na pasta de salvaguarda.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>