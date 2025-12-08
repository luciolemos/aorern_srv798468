<?php
$placeholder = 'Buscar categoria de post...';
$q = $q ?? '';
?>

<div class="container-fluid px-3 px-md-4 py-4">
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-tags me-2"></i> Categorias de posts</h2>
        <p class="lead text-muted">Gerencie as categorias usadas nas publicações do blog.</p>
        <hr class="my-4">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-start mb-4">
            <a href="<?= BASE_URL ?>admin/post-categories/create" class="btn btn-primary text-white">
                <i class="bi bi-plus-circle"></i> Nova categoria
            </a>
        </div>
    </div>

    <div data-aos="fade-up">
        <?php include __DIR__ . '/../../partials/search_bar.php'; ?>
    </div>

    <?php if (empty($categorias)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Nenhuma categoria encontrada.
        </div>
    <?php else: ?>
        <div class="table-responsive" data-aos="fade-up">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>NOME</th>
                    <th class="d-none d-md-table-cell">DESCRIÇÃO</th>
                    <th class="text-center">AÇÕES</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><i class="bi bi-tag text-primary"></i></td>
                        <td><?= htmlspecialchars($categoria['nome']) ?></td>
                        <td class="d-none d-md-table-cell text-muted"><?= htmlspecialchars($categoria['descricao'] ?? '—') ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="<?= BASE_URL ?>admin/post-categories/edit/<?= $categoria['id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/post-categories/destroy/<?= $categoria['id'] ?>"
                                   class="btn btn-sm btn-outline-danger" title="Excluir"
                                   onclick="return confirm('Excluir a categoria selecionada?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (!empty($pagination) && (($pagination['total'] ?? 0) > 0)): ?>
        <?php include __DIR__ . '/../../partials/pagination.php'; ?>
    <?php endif; ?>

    <div class="d-flex justify-content-end mt-4">
        <a href="<?= BASE_URL ?>admin" class="btn btn-primary text-white">
            <i class="bi bi-speedometer2 me-2"></i> Painel
        </a>
    </div>
</div>
