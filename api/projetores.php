<?php
include('auth.php'); // Garante que o utilizador está logado
include('db.php');

$mensagem = "";
$erro = "";

// Variáveis de controlo para o formulário dinâmico (Modo Edição vs Modo Criação)
$edit_id = 0;
$edit_sala = "";
$edit_bloco = "";
$edit_equipamento = "";
$edit_estado = "Operacional";
$edit_obs = "";

// ==========================================
// 1. AÇÃO: RECOLHER DADOS PARA EDIÇÃO (GET)
// ==========================================
if (isset($_GET['editar'])) {
    $edit_id = (int)$_GET['editar'];
    $busca_edit = mysqli_query($conexao, "SELECT * FROM projetores WHERE id = $edit_id");
    if ($dados_edit = mysqli_fetch_assoc($busca_edit)) {
        $edit_sala        = $dados_edit['sala'];
        $edit_bloco       = $dados_edit['bloco'];
        $edit_equipamento = $dados_edit['equipamento'] ?? $dados_edit['hardware'] ?? '';
        $edit_estado      = $dados_edit['estado'] ?? $dados_edit['status'] ?? 'Operacional';
        $edit_obs         = $dados_edit['observacoes'] ?? $dados_edit['obs'] ?? '';
    }
}

// ==========================================
// 2. AÇÃO: PROCESSAR FORMULÁRIO (POST - CRIAR OU ATUALIZAR)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bt_salvar'])) {
    $id = (int)$_POST['id_projetor'];
    $sala = mysqli_real_escape_string($conexao, trim($_POST['sala']));
    $bloco = mysqli_real_escape_string($conexao, trim($_POST['bloco']));
    $equipamento = mysqli_real_escape_string($conexao, trim($_POST['equipamento']));
    $estado = mysqli_real_escape_string($conexao, $_POST['estado']);
    $observacoes = mysqli_real_escape_string($conexao, trim($_POST['observacoes']));

    if (empty($sala) || empty($bloco)) {
        $erro = "❌ Os campos SALA e BLOCO são obrigatórios.";
    } else {
        if ($id > 0) {
            $query_update = "UPDATE projetores SET sala='$sala', bloco='$bloco', equipamento='$equipamento', estado='$estado', observacoes='$observacoes' WHERE id=$id";
            if (!mysqli_query($conexao, $query_update)) {
                $query_update = "UPDATE projetores SET sala='$sala', bloco='$bloco', hardware='$equipamento', status='$estado', observacoes='$observacoes' WHERE id=$id";
                mysqli_query($conexao, $query_update);
            }
            $mensagem = "✅ Dados do espaço e equipamento atualizados com sucesso.";
            $edit_id = 0; $edit_sala = ""; $edit_bloco = ""; $edit_equipamento = ""; $edit_estado = "Operacional"; $edit_obs = "";
        } else {
            $query_insert = "INSERT INTO projetores (sala, bloco, equipamento, estado, observacoes) VALUES ('$sala', '$bloco', '$equipamento', '$estado', '$observacoes')";
            if (!mysqli_query($conexao, $query_insert)) {
                $query_insert = "INSERT INTO projetores (sala, bloco, hardware, status, observacoes) VALUES ('$sala', '$bloco', '$equipamento', '$estado', '$observacoes')";
                mysqli_query($conexao, $query_insert);
            }
            $mensagem = "✅ Nova infraestrutura mapeada no inventário com sucesso.";
        }
    }
}

// ==========================================
// 3. SISTEMA DE FILTRAGEM DUPLA (GET)
// ==========================================
$bloco_selecionado = isset($_GET['filtro_bloco']) ? mysqli_real_escape_string($conexao, trim($_GET['filtro_bloco'])) : '';
$sala_pesquisa     = isset($_GET['pesquisa_sala']) ? mysqli_real_escape_string($conexao, trim($_GET['pesquisa_sala'])) : '';

// Query para alimentar o <select> de blocos únicos
$query_blocos_unicos = mysqli_query($conexao, "SELECT DISTINCT bloco FROM projetores WHERE bloco != '' ORDER BY bloco ASC");

// Construção dinâmica de filtros SQL clausulados
$condicoes = [];

if (!empty($bloco_selecionado)) {
    $condicoes[] = "bloco = '$bloco_selecionado'";
}

if (!empty($sala_pesquisa)) {
    $condicoes[] = "sala LIKE '%$sala_pesquisa%'";
}

// Junta as condições se elas existirem
if (count($condicoes) > 0) {
    $query_principal = "SELECT * FROM projetores WHERE " . implode(' AND ', $condicoes) . " ORDER BY bloco ASC, sala ASC";
} else {
    $query_principal = "SELECT * FROM projetores ORDER BY bloco ASC, sala ASC";
}

$lista_projetores = mysqli_query($conexao, $query_principal);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Tech Crew - Inventário de Projetores</title>
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .split-container { display: flex; gap: 25px; margin-top: 30px; }
        .col-form { flex: 1; min-width: 320px; }
        .col-lista { flex: 2; }
        .alert-sucesso { padding: 12px; background: rgba(0, 255, 102, 0.1); border: 1px solid var(--neon); color: var(--neon); margin-bottom: 20px; border-radius: 6px; font-weight: bold; }
        .alert-erro { padding: 12px; background: rgba(255, 59, 59, 0.1); border: 1px solid var(--danger); color: var(--danger); margin-bottom: 20px; border-radius: 6px; font-weight: bold; }
        .badge-status { padding: 4px 8px; font-size: 11px; font-weight: bold; border-radius: 4px; text-transform: uppercase; display: inline-block; }
        .status-ok { background: rgba(0, 255, 102, 0.15); color: var(--neon); border: 1px solid var(--neon); }
        .status-aviso { background: rgba(255, 230, 0, 0.15); color: var(--yellow); border: 1px solid var(--yellow); }
        .status-critico { background: rgba(255, 59, 59, 0.15); color: var(--danger); border: 1px solid var(--danger); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { padding: 12px; text-align: left; color: var(--neon); border-bottom: 2px solid var(--border); font-size: 11px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid rgba(0,255,102,0.1); color: #fff; font-size: 13px; vertical-align: middle; }
        .btn-pequeno { padding: 5px 10px; font-size: 11px; font-weight: bold; text-decoration: none; border-radius: 4px; display: inline-block; cursor: pointer !important; color: #000; }
        .btn-cancelar { background: #333; color: #fff; border: 1px solid #555; text-decoration: none; display: flex; align-items: center; justify-content: center; padding: 0 15px; border-radius: 6px; }
        
        /* Barra HUD de filtros combinados */
        .hud-filtros {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
            background: rgba(0, 255, 102, 0.02);
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid rgba(0, 255, 102, 0.05);
            flex-wrap: wrap;
        }
        .filtro-grupo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .select-filtro, .input-pesquisa {
            padding: 8px 12px;
            background: #000;
            border: 1px solid var(--border);
            color: #fff;
            border-radius: 6px;
            font-family: var(--font);
            font-size: 12px;
            outline: none;
        }
        .input-pesquisa {
            width: 140px;
            transition: 0.3s ease;
        }
        .input-pesquisa:focus {
            border-color: var(--cyan);
            width: 180px;
        }
    </style>
</head>
<body>
    <a href="index.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; box-shadow: var(--shadow); z-index: 9999;">⬅ Voltar ao Painel</a>

    <div class="container" style="max-width: 1400px; margin-top: 40px;">
        
        <div class="terminal-header" style="margin-bottom: 25px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="logo.jpg" alt="Tech Crew" class="terminal-logo">
                <div>
                    <h1>HARDWARE_INVENTORY // PROJETORES_SINALIZAÇÃO</h1>
                    <p>Controlo de estado, avarias e manutenção preventiva dos projetores das salas e blocos.</p>
                </div>
            </div>
        </div>

        <?php if(!empty($mensagem)): ?> <div class="alert-sucesso"><?php echo $mensagem; ?></div> <?php endif; ?>
        <?php if(!empty($erro)): ?> <div class="alert-erro"><?php echo $erro; ?></div> <?php endif; ?>

        <div class="split-container">
            
            <div class="col-form">
                <div class="card-painel">
                    <h2 style="color: var(--neon); margin-bottom: 20px; font-size: 15px;">
                        [ <?php echo $edit_id > 0 ? "MODIFICAR_REGISTO_#".$edit_id : "INSERIR_NOVA_SALA"; ?> ]
                    </h2>
                    
                    <form action="projetores.php" method="POST">
                        <input type="hidden" name="id_projetor" value="<?php echo $edit_id; ?>">

                        <div class="form-group">
                            <label for="bloco">BLOCO / SETOR:</label>
                            <input type="text" id="bloco" name="bloco" value="<?php echo htmlspecialchars($edit_bloco); ?>" placeholder="Ex: Bloco A" required>
                        </div>

                        <div class="form-group">
                            <label for="sala">SALA / ESPAÇO:</label>
                            <input type="text" id="sala" name="sala" value="<?php echo htmlspecialchars($edit_sala); ?>" placeholder="Ex: Sala 102" required>
                        </div>

                        <div class="form-group">
                            <label for="equipamento">EQUIPAMENTO USADO (MARCA / MODELO / CABOS):</label>
                            <input type="text" id="equipamento" name="equipamento" value="<?php echo htmlspecialchars($edit_equipamento); ?>" placeholder="Ex: Epson X41+ / HDMI + VGA">
                        </div>

                        <div class="form-group">
                            <label for="estado">ESTADO DO PROJETOR:</label>
                            <select id="estado" name="estado" style="width: 100%; padding: 12px; background: #000; border: 1px solid var(--border); color: #fff; border-radius: 6px;">
                                <option value="Operacional" <?php if($edit_estado == 'Operacional') echo 'selected'; ?>>🟢 Operacional</option>
                                <option value="Necessita Manutenção" <?php if($edit_estado == 'Necessita Manutenção' || $edit_estado == 'Manutenção') echo 'selected'; ?>>🟡 Filtro / Lâmpada (Aviso)</option>
                                <option value="Avariado" <?php if($edit_estado == 'Avariado') echo 'selected'; ?>>🔴 Avariado (Inoperacional)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="observacoes">LOGS_OBSERVAÇÕES (OPCIONAL):</label>
                            <textarea id="observacoes" name="observacoes" placeholder="Anomalias detetadas..." style="height: 80px;"><?php echo htmlspecialchars($edit_obs); ?></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" name="bt_salvar" class="btn" style="flex: 1;">[ SUBMETER_STATUS ]</button>
                            <?php if($edit_id > 0): ?>
                                <a href="projetores.php" class="btn-cancelar" title="Cancelar Edição">[ X ]</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lista">
                <div class="card-painel" style="height: 100%;">
                    
                    <form action="projetores.php" method="GET" class="hud-filtros">
                        <div class="filtro-grupo">
                            <label style="color: var(--cyan); font-size: 11px; font-weight: bold;">[ FILTRO_BLOCO ]</label>
                            <select name="filtro_bloco" class="select-filtro" onchange="this.form.submit();">
                                <option value="">== TODOS OS BLOCOS ==</option>
                                <?php if ($query_blocos_unicos): ?>
                                    <?php while ($b = mysqli_fetch_assoc($query_blocos_unicos)): ?>
                                        <option value="<?php echo htmlspecialchars($b['bloco']); ?>" <?php if ($bloco_selecionado === $b['bloco']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($b['bloco']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="filtro-grupo">
                            <label style="color: var(--yellow); font-size: 11px; font-weight: bold;">[ PESQUISA_SALA ]</label>
                            <input type="text" name="pesquisa_sala" class="input-pesquisa" placeholder="Ex: 102 ou Auditório" value="<?php echo htmlspecialchars($sala_pesquisa); ?>" autocomplete="off">
                            <button type="submit" class="btn" style="height: 32px; padding: 0 12px; font-size: 11px; border-color: var(--yellow); color: var(--yellow); background: transparent;">[ BUSCA ]</button>
                            <?php if (!empty($bloco_selecionado) || !empty($sala_pesquisa)): ?>
                                <a href="projetores.php" class="btn btn-danger" style="height: 32px; padding: 0 10px; font-size: 11px; display: flex; align-items: center; text-decoration: none;">[ X ]</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 15%;">BLOCO</th>
                                    <th style="width: 15%;">SALA</th>
                                    <th style="width: 25%;">EQUIPAMENTO INSTALADO</th>
                                    <th style="width: 20%;">ESTADO</th>
                                    <th style="width: 15%;">OBSERVAÇÕES</th>
                                    <th style="width: 10%; text-align: right;">AÇÃO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($lista_projetores && mysqli_num_rows($lista_projetores) > 0): ?>
                                    <?php while($p = mysqli_fetch_assoc($lista_projetores)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($p['bloco']); ?></strong></td>
                                        <td style="color: var(--cyan); font-weight: bold;"><?php echo htmlspecialchars($p['sala']); ?></td>
                                        <td style="color: #cbd5e1; font-size: 12px;">
                                            <?php echo htmlspecialchars($p['equipamento'] ?? $p['hardware'] ?? 'Não Especificado'); ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $est = $p['estado'] ?? $p['status'] ?? 'Operacional';
                                            if ($est == 'Operacional') {
                                                echo '<span class="badge-status status-ok">OPERACIONAL</span>';
                                            } elseif ($est == 'Avariado') {
                                                echo '<span class="badge-status status-critico">AVARIADO</span>';
                                            } else {
                                                echo '<span class="badge-status status-aviso">MANUTENÇÃO</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="color: #a7f3d0; font-size: 11px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($p['observacoes'] ?? $p['obs'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($p['observacoes'] ?? $p['obs'] ?? '-'); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="projetores.php?editar=<?php echo $p['id']; ?><?php echo !empty($bloco_selecionado) ? '&filtro_bloco='.urlencode($bloco_selecionado) : ''; ?><?php echo !empty($sala_pesquisa) ? '&pesquisa_sala='.urlencode($sala_pesquisa) : ''; ?>" class="btn-pequeno btn-warning">ALTERAR</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" style="text-align: center; color: var(--muted); font-style: italic; padding: 25px;">Nenhum hardware localizado para os filtros inseridos.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>