<!-- admin/categorias/editar.php -->
<!--<div class="container">
    <h2 class="text-warning"><i class="bi bi-pencil-square me-2"></i> Editar Categoria</h2>
    <hr class="my-4">

    <form action="<?/*= BASE_URL */?>admin/categorias/atualizar/<?/*= $registro['id'] */?>" method="post">
        <?php /*include __DIR__ . '/_form.php'; */?>
        <button type="submit" class="btn btn-warning mt-3">
            <i class="bi bi-save me-1"></i> Atualizar
        </button>
        <a href="<?/*= BASE_URL */?>admin/categorias" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
</div>-->

<div class="container-fluid px-3 px-md-4 py-4">
    <h2 class="text-<?= isset($registro) ? 'info' : 'primary' ?>">
        <i class="bi bi-person-gear me-2"></i>
        <?= isset($registro) ? 'Editar categoria' : 'Nova cat' ?>
    </h2>
    <p class="lead text-muted">Bem-vindo, admin! Aqui você altera as características das categorias de seus equipamentos</p>
    <hr>
    <?php include __DIR__ . '/_form.php'; ?>
</div>
