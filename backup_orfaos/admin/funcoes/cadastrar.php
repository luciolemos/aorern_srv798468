<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Cabeçalho -->   
    <div data-aos="fade-down" class="mb-4">
        <h2 class="text-<?= isset($registro) ? 'warning' : 'success' ?>">
        <i class="bi bi-person-gear me-2"></i>
        <?= isset($registro) ? 'Editar função' : 'Nova função' ?>
    </h2>
        <p class="text-muted mb-0">Cadastre novas funções/cargos no CBMRN</p>
    </div>

    <?php include __DIR__ . '/_form.php'; ?>
</div>

