<?php
$q = $q ?? '';
$placeholder = 'Buscar por título...';
?>

<div class="container-fluid px-3 px-md-4 py-4">
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-file-earmark-text me-2"></i> Posts do Blog</h2>
        <p class="lead text-muted">Aqui você gerencia suas postagens</p>
        <hr class="my-4">

         <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-start mt-4 mb-4">
            <a href="<?= BASE_URL ?>admin/posts/create" class="btn btn-primary text-white">
                <i class="bi bi-plus-circle"></i> Novo post
            </a>
        </div>
    </div>

    <div data-aos="fade-up">
        <?php include __DIR__ . '/../../partials/search_bar.php'; ?>
    </div>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Nenhum post encontrado
        </div>
    <?php else: ?>
        <div class="table-responsive" data-aos="fade-up">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="text-center">#</th>
                    <th>TÍTULO</th>
                    <th class="d-none d-sm-table-cell">CATEGORIA</th>
                    <th class="d-none d-lg-table-cell">AUTOR</th>
                    <th class="d-none d-lg-table-cell">DATA CRIAÇÃO</th>
                    <th class="text-center">AÇÕES</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td class="text-center"><i class="bi bi-file-earmark-text text-primary"></i></td>
                        <td>
                            <div class="d-flex flex-column">
                                <span><?= htmlspecialchars($post['titulo']) ?></span>
                                <small class="text-muted d-md-none"><?= htmlspecialchars($post['slug']) ?></small>
                            </div>
                        </td>
                        <td class="d-none d-sm-table-cell"><?= htmlspecialchars($post['categoria_nome'] ?? 'Sem categoria') ?></td>
                        <td class="d-none d-lg-table-cell"><?= htmlspecialchars($post['autor']) ?></td>
                        <td class="d-none d-lg-table-cell"><?= date('d/m/Y H:i', strtotime($post['criado_em'])) ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="<?= BASE_URL ?>admin/posts/edit/<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/posts/delete/<?= $post['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Deseja excluir este post?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>

    <?php if (!empty($pagination) && (($pagination['total'] ?? 0) > 0)): ?>
        <?php include __DIR__ . '/../../partials/pagination.php'; ?>
    <?php endif; ?>

    <div class="d-flex justify-content-end mt-4">
        <a href="<?= BASE_URL ?>admin" class="btn btn-primary text-white">
            <i class="bi bi-speedometer2 me-2"></i> Painel
        </a>
    </div>
</div>
