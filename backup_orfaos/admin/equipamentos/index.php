<?php
$q = $q ?? '';
$placeholder = 'Buscar equipamento por NOME...';
?>

<div class="container-fluid px-3 px-md-4 py-4">
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-list-columns-reverse me-2"></i> Equipamentos</h2>
        <p class="lead text-muted">Lista de ativos</p>
        <hr class="my-4">
        <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-start mt-4 mb-4">
            <a href="<?= BASE_URL ?>admin/equipamentos/cadastrar" class="btn btn-primary text-white order-md-1">
                <i class="bi bi-plus-circle"></i> Novo equipamento
            </a>
        </div>
    </div>

    <div data-aos="fade-up">
        <?php include __DIR__ . '/../../partials/search_bar.php'; ?>
    </div>

    <?php if (empty($equipamentos)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Nenhuma categoria encontrada.
        </div>
    <?php else: ?>
    <div class="table-responsive" data-aos="fade-up">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="text-center">#</th>
                <th>CÓD</th>
                <th>NOME</th>
                <th>MARCA</th>
                <th>MODELO</th>
                <th>CATEGORIA</th>
                <th>ESTOQUE</th>
                <th>ESTADO</th>
                <th>AQUISIÇÃO</th>
                <th class="text-center">AÇÕES</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($equipamentos as $e): ?>
                <?php
                $estado = $e['estado'] ?? 'Nova';

                if ($estado === 'Nova') {
                    $badgeClass = 'bg-primary';
                } elseif ($estado === 'Restaurada') {
                    $badgeClass = 'bg-warning';
                } elseif ($estado === 'Danificada') {
                    $badgeClass = 'bg-danger';
                } elseif ($estado === 'Usada') {
                    $badgeClass = 'bg-info';
                } else {
                    $badgeClass = 'bg-secondary';
                };

                ?>
                <tr>
                    <td class="text-center"><i class="bi bi-tools text-primary"></i></td>
                    <td><?= $e['staff_id'] ?></td>
                    <td><?= htmlspecialchars($e['nome']) ?></td>
                    <td><?= htmlspecialchars($e['marca']) ?></td>
                    <td><?= htmlspecialchars($e['modelo']) ?></td>
                    <td><?= htmlspecialchars($e['categoria_nome'] ?? 'Sem categoria') ?></td>
                    <td><?= $e['quantidade_estoque'] ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $estado ?></span></td>
                    <td><span class="d-block"><?= date('d/m/Y', strtotime($e['data_fabricacao'])) ?></span></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="<?= BASE_URL ?>admin/equipamentos/editar/<?= $e['id'] ?>"
                               class="btn btn-sm btn-outline-primary"
                               data-bs-toggle="tooltip" data-bs-title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="<?= BASE_URL ?>admin/equipamentos/deletar/<?= $e['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               data-bs-toggle="tooltip" data-bs-title="Excluir"
                               onclick="return confirm('Deseja excluir este equipamento ou ferramenta?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
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
