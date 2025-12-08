<div class="container-fluid px-3 px-md-4 py-4">
    <h2 class="text-<?= isset($registro) ? 'warning' : 'info' ?>">
        <i class="bi bi-person-gear me-2"></i>
        <?= isset($registro) ? 'Editar categoria' : 'Nova categoria de equipamento' ?>
    </h2>
    <p class="lead text-muted">Cadastrar nova categoria de equipamento</p>
    <hr class="my-4">
    <?php include __DIR__ . '/_form.php'; ?>
</div>


