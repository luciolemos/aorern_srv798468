<?php
$q = $q ?? '';
$placeholder = 'Buscar cargo/função por DESCRIÇÃO...';
?>

<div class="container-fluid px-3 px-md-4 py-4">
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-list-columns-reverse me-2"></i> Cargos e funções</h2>
        <p class="lead text-muted">Lista de cargos/funções na estrutura organizacional do CBMRN</p>
        <hr class="my-4">
        <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-start mt-4 mb-4">
            <a href="<?= BASE_URL ?>admin/funcoes/cadastrar" class="btn btn-primary text-white order-md-1">
                <i class="bi bi-plus-circle"></i> Nova função
            </a>
        </div>
    </div>

    <div data-aos="fade-up">
        <?php include __DIR__ . '/../../partials/search_bar.php'; ?>
    </div>

    <?php if (empty($funcoes)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Não há função com esse nome em nossa base de dados.
        </div>
    <?php else: ?>
        <div class="table-responsive" data-aos="fade-up">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="text-center">#</th>
                    <th>CÓD DA FUNÇÃO</th>
                    <th>DESCRIÇÃO</th>
                    <th class="text-center">AÇÕES</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($funcoes as $f): ?>
                    <tr>
                        <td class="text-center"><i class="bi bi-tags text-primary"></i></td>
                        <td><?= htmlspecialchars($f['staff_id']) ?></td>
                        <td><?= htmlspecialchars($f['nome']) ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="<?= BASE_URL ?>admin/funcoes/editar/<?= $f['id'] ?>"
                                   class="btn btn-sm btn-outline-primary"
                                   data-bs-toggle="tooltip" data-bs-title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/funcoes/deletar/<?= $f['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   data-bs-toggle="tooltip" data-bs-title="Excluir"
                                   onclick="return confirm('Deseja excluir esta categoria de equipamento?')">
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
