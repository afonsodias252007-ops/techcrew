<?php
include('db.php');

// ==========================================
// 1. PROCESSAR AÇÕES DIRETAS (ELIMINAR)
// ==========================================
if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    mysqli_query($conexao, "DELETE FROM links_uteis WHERE id = $id_excluir");
    header('Location: links.php');
    exit;
}

// ==========================================
// 2. PROCESSAR SUBMISSÃO DO FORMULÁRIO (CRIAR)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bt_salvar'])) {
    $titulo = mysqli_real_escape_string($conexao, $_POST['titulo']);
    $url = mysqli_real_escape_string($conexao, $_POST['url']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    
    mysqli_query($conexao, "INSERT INTO links_uteis (titulo, url, descricao) VALUES ('$titulo', '$url', '$descricao')");
    header('Location: links.php');
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
    <script>
        // Pesquisa e Filtragem Dinâmica de Cartões sem recarregar a página
        function filtrarLinks() {
            var input = document.getElementById('search-links');
            var filter = input.value.toLowerCase();
            var cards = document.getElementsByClassName('link-card');

            for (var i = 0; i < cards.length; i++) {
                var title = cards[i].getElementsByClassName('link-title')[0].innerText.toLowerCase();
                var desc = cards[i].getElementsByClassName('link-desc')[0].innerText.toLowerCase();
                
                if (title.includes(filter) || desc.includes(filter)) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }
    </script>
</head>
<body>
    <a href="index.php" style="position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); border: 1px solid var(--neon); color: var(--neon); padding: 10px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; z-index: 99999; display: inline-block; cursor: pointer !important;">⬅ Voltar ao Painel</a>

    <div class="container" style="margin-top: 80px; max-width: 1300px; padding: 20px; position: relative; z-index: 1;">
        
        <div class="card-painel" style="margin-bottom: 30px; padding: 25px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); position: relative; z-index: 5;">
            <h2 style="color: var(--neon); margin-bottom: 20px;">[ ADICIONAR_NOVO_ATALHO_OPERACIONAL ]</h2>
            <form action="links.php" method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    
                    <div class="form-group">
                        <label style="color: var(--text); display:block; margin-bottom: 5px;">NOME / TÍTULO:</label>
                        <input type="text" name="titulo" placeholder="Ex: Portal Inovar, Router Central" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px; box-sizing: border-box;">
                    </div>

                    <div class="form-group">
                        <label style="color: var(--text); display:block; margin-bottom: 5px;">LINK / URL COMPLETA:</label>
                        <input type="url" name="url" placeholder="https://exemplo.com" required style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px; box-sizing: border-box;">
                    </div>

                    <div class="form-group">
                        <label style="color: var(--text); display:block; margin-bottom: 5px;">BREVE DESCRIÇÃO / NOTA:</label>
                        <input type="text" name="descricao" placeholder="Ex: Plataforma para gerir dados das turmas" style="width:100%; padding:10px; background:#000; border:1px solid var(--border); color:#fff; height: 40px; box-sizing: border-box;">
                    </div>

                </div>

                <button type="submit" name="bt_salvar" class="btn" style="background:var(--bg-card); color:var(--neon); border:1px solid var(--neon); padding:10px 20px; cursor:pointer !important; position: relative; z-index: 10;">
                    [ REGISTAR_ATALHO ]
                </button>
            </form>
        </div>

        <div class="card-painel" style="padding: 20px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 30px; position: relative; z-index: 5;">
            <div style="position: relative; z-index: 10;">
                <input type="text" id="search-links" onkeyup="filtrarLinks()" placeholder="Digita para filtrar os teus links em tempo real..." 
                       style="width: 100%; padding: 12px 15px; background: #000; border: 1px solid var(--border); color: #fff; font-size: 14px; outline: none; border-radius: 6px; box-sizing: border-box;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; position: relative; z-index: 5;">
            
            <?php 
            $query = "SELECT * FROM links_uteis ORDER BY id DESC";
            $resultado = mysqli_query($conexao, $query);

            if ($resultado && mysqli_num_rows($resultado) > 0): 
                while($row = mysqli_fetch_assoc($resultado)): 
            ?>
                <div class="link-card card-painel" style="padding: 20px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); display: flex; flex-direction: column; justify-content: space-between; min-height: 180px;">
                    <div>
                        <h3 class="link-title" style="color: var(--cyan); margin: 0 0 8px 0; font-size: 18px; text-shadow: 0 0 5px rgba(0,217,255,0.2);"><?php echo htmlspecialchars($row['titulo']); ?></h3>
                        <p class="link-desc" style="color: #aaa; font-size: 12px; margin: 0 0 15px 0; line-height: 1.4;">
                            <?php echo !empty($row['descricao']) ? htmlspecialchars($row['descricao']) : 'Nenhuma nota operacional registada para este atalho.'; ?>
                        </p>
                    </div>
                    
                    <div>
                        <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" 
                           style="display: block; text-align: center; padding: 10px; background: transparent; border: 1px solid var(--neon); color: var(--neon); text-decoration: none; font-weight: bold; font-size: 12px; border-radius: 4px; margin-bottom: 12px; position: relative; z-index: 999; cursor: pointer !important;">
                           LANÇAR_PLATAFORMA ↗
                        </a>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a href="editarlink.php?id=<?php echo $row['id']; ?>" 
                               style="font-size: 11px; font-weight: bold; color: var(--yellow); text-decoration: none; position: relative; z-index: 999; cursor: pointer !important;">
                                [ EDITAR ]
                            </a>
                            <a href="links.php?excluir=<?php echo $row['id']; ?>" 
                               style="font-size: 11px; font-weight: bold; color: var(--danger); text-decoration: none; position: relative; z-index: 999; cursor: pointer !important;"
                               onclick="return confirm('Deseja remover este atalho permanentemente do launchpad?')">
                                [ APAGAR ]
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div class="card-painel" style="grid-column: 1 / -1; padding: 30px; text-align: center; color: #555; font-style: italic; border: 1px dashed var(--border);">
                    Nenhum atalho operável guardado no launchpad da Tech Crew.
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>