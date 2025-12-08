<?php
// app/Views/layouts/main.php

use App\Helpers\SystemVersions;
$versions = SystemVersions::get();
?>

<!-- Cabeçalho do site (logo + nav) -->
<?php require __DIR__ . '/header.php'; ?>

<!-- 🎉 Toast para mensagens do site (formulários de contato, etc) -->
<?php if (!empty($_SESSION['toast'])): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        <div class="toast align-items-center text-white bg-<?= $_SESSION['toast']['type'] ?> border-0 show shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($_SESSION['toast']['message']) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<!-- Conteúdo principal do site (cada página pública carrega aqui) -->
<main class="flex-fill py-4">
    <div class="container">
        <?php
        if (isset($GLOBALS['view'])) {
            $path = '../app/Views/' . $GLOBALS['view'] . '.php';
            if (file_exists($path)) {
                require $path;
            } else {
                echo "<div class='alert alert-danger'>Erro: view <code>$path</code> não encontrada.</div>";
            }
        }
        ?>

        <!-- Rodapé com badges visuais -->
        <div class="text-center text-muted mt-5 small py-3 border-top" data-aos="fade-in">
            <div class="mb-1">
                <img src="https://img.shields.io/badge/deploy-production-brightgreen" alt="Deploy OK" class="me-2">
                <img src="https://img.shields.io/uptimerobot/status/m790498088-c8793f75e4fa5243f7e0840e" alt="Uptime">
            </div>
        </div>
    </div>
</main>

<!-- Rodapé do site (copyright, links etc) -->
<?php require __DIR__ . '/footer.php'; ?>
