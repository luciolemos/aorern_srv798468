<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - MyApp MVC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & ícones -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/navbar-universal.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/footer-robust.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/admin.css" rel="stylesheet">
</head>
<body>

<?php
// Variáveis para o componente navbar
$navbar_type = 'admin';
$show_sidebar_toggle = true;
include __DIR__ . '/components/navbar.php';
?>

<div class="admin-layout d-flex">
    <!-- Overlay para fechar menu em mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <?php
    // Importa sidebar centralizada
    include __DIR__ . '/components/sidebar.php';
    ?>
    
    <!-- Conteúdo Principal -->
    <main class="content-area flex-grow-1 p-4">
        <?php if (!empty($_SESSION['toast'])): ?>
            <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
                <div class="toast align-items-center text-white bg-<?= $_SESSION['toast']['type'] ?> border-0 show shadow" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><?= htmlspecialchars($_SESSION['toast']['message']) ?></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>

        <?php
        if (isset($GLOBALS['view'])) {
            $path = '../app/Views/' . $GLOBALS['view'] . '.php';
            file_exists($path) ? require $path : print("<div class='alert alert-danger'>View <code>$path</code> não encontrada.</div>");
        }
        ?>
    </main>
</div>

<!-- Footer -->
<footer class="bg-dark text-light py-3 mt-auto admin-footer">
    <div class="container text-center">
        <p class="mb-0 small">
            &copy; PHP Full-Stack <?= date('Y') ?> —
            <a href="http://lattes.cnpq.br/6156274538172427" class="text-white text-decoration-none">
                <i>by</i> Lúcio Lemos
            </a>
        </p>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const editor = document.querySelector('#conteudo');
        if (editor) ClassicEditor.create(editor).catch(err => console.error(err));
        
        // ========== CONTROLE DO MENU MOBILE ==========
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        // Abre/fecha o menu ao clicar no hamburger
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-active');
            });
        }
        
        // Fecha o menu ao clicar no overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-active');
            });
        }
        
        // Fecha o menu ao clicar em um link (melhora UX mobile)
        const sidebarLinks = sidebar.querySelectorAll('.nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Só fecha se estiver em mobile (sidebar com class active)
                if (sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.classList.remove('sidebar-active');
                }
            });
        });
    });
</script>



<!-- JS e Scripts finais -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>

<!-- Mermaid.js -->
<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ startOnLoad: true });
</script>

<!-- Copiar comandos -->
<script>
    function copiarComandos() {
        const texto = document.getElementById("blocoComandos").innerText;
        navigator.clipboard.writeText(texto).then(() => {
            alert("Comandos copiados!");
        }).catch(err => {
            alert("Erro ao copiar: " + err);
        });
    }
</script>

<?php $footer_type = 'admin'; include __DIR__ . '/components/footer.php'; ?>

</body>
</html>
