<?php

use App\Helpers\Toast;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>OPUS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#df6301"> <!-- Cor laranja Bombeiros RN para mobile browsers -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Bootstrap & ícones -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/bombeiros-theme.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/navbar-universal.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/footer-robust.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/admin.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/admin-dashboard.css" rel="stylesheet">

</head>
<body class="admin-body">

<!-- Top Navbar - Usa componente centralizado -->
<?php
$navbar_type = 'admin';
$show_sidebar_toggle = true;
include __DIR__ . '/../components/navbar.php';
?>

<!-- 🔲 Overlay de fundo para mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout d-flex">
    <!-- Sidebar - Usa componente centralizado -->
    <?php 
    // Define $mainRoute e $subRoute para o sidebar
    $fullUrl = $_GET['url'] ?? 'admin/dashboard';
    $segments = explode('/', trim($fullUrl, '/'));
    $mainRoute = $segments[0] ?? 'admin';
    $subRoute = end($segments);
    
    include __DIR__ . '/../components/sidebar.php'; 
    ?>

    <!-- Conteúdo Principal -->
    <main class="content-area flex-grow-1">
        <nav aria-label="breadcrumb" class="bg-light px-4 py-2 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>admin/dashboard"><i class="bi bi-house-door"></i></a>
                    </li>


                    <?php
                    $fullUrl = $_GET['url'] ?? 'admin/dashboard';
                    $segments = explode('/', trim($fullUrl, '/'));
                    $accumulatedPath = '';

                    foreach ($segments as $i => $segment):
                        $accumulatedPath .= $segment . '/';
                        $isLast = $i === array_key_last($segments);
                        $text = ucwords(str_replace(['_', '-'], ' ', $segment));
                        $url = BASE_URL . rtrim($accumulatedPath, '/');
                        ?>
                        <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>" <?= $isLast ? 'aria-current="page"' : '' ?>>
                            <?php if (!$isLast): ?>
                                <a href="<?= $url ?>"><?= $text ?></a>
                            <?php else: ?>
                                <?= $text ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </nav>

        <div class="p-3 p-md-4">
            <?= Toast::render() ?>

            <?php
            if (isset($GLOBALS['view'])) {
                $path = '../app/Views/' . $GLOBALS['view'] . '.php';
                if (file_exists($path)) {
                    require $path;
                } else {
                    echo '<div class="alert alert-danger">View <code>' . htmlspecialchars($path) . '</code> não encontrada.</div>';
                }
            }
            ?>

        </div>
        <?php $footer_type = 'admin'; include __DIR__ . '/../components/footer.php'; ?>
    </main>

</div>

<!-- Scripts Externos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Admin Scripts Consolidado -->
<script src="<?= BASE_URL ?>assets/js/admin.js" defer></script>

<!-- Inicialização explícita do Bootstrap -->
<script>
    // Garante que todos os componentes Bootstrap sejam inicializados
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa todos os dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        
        // Inicializa todos os collapses
        var collapseElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="collapse"]'));
        collapseElementList.map(function (collapseToggleEl) {
            return new bootstrap.Collapse(collapseToggleEl, {toggle: false});
        });
    });
</script>

<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    document.addEventListener('DOMContentLoaded', () => {
        mermaid.initialize({ startOnLoad: true });
        mermaid.run(); // força renderização se carregado tardiamente
    });
</script>


</body>
</html>
