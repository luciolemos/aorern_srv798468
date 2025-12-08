<?php
$q = $q ?? '';
$placeholder = 'Buscar por nome, CPF, telefone ou função...';
function ordenarLink($campo, $label, $orderBy, $direction, $q) {
    $icone = '';
    $dir = 'ASC';
    if ($orderBy === $campo) {
        $icone = $direction === 'ASC' ? '↑' : '↓';
        $dir = $direction === 'ASC' ? 'DESC' : 'ASC';
    }

    $url = "?orderBy={$campo}&direction={$dir}&q=" . urlencode($q);
    return "<a href='{$url}' class='text-decoration-none text-dark'>{$label} {$icone}</a>";
}



?>



<div class="container-fluid px-3 px-md-4 py-4">
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-list-columns-reverse me-2"></i> Bombeiros</h2>
        <p class="lead text-muted">Lista de bombeiros cadastrados</p>
        <hr class="my-4">
        <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-start mt-4 mb-4">
            <a href="<?= BASE_URL ?>admin/pessoal/cadastrar" class="btn btn-primary text-white order-md-1">
                <i class="bi bi-plus-circle"></i> Novo registro
            </a>
        </div>
    </div>
    <div data-aos="fade-up">
        <?php include __DIR__ . '/../../partials/search_bar.php'; ?>
    </div>

    <?php if (empty($pessoal)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Não encontramos bombeiro com esse nome na base de dados.
        </div>
    <?php else: ?>
        <div class="table-responsive" data-aos="fade-up">
            <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="text-center">#</th>
                <th>NOME</th>
                <th class="text-center">CPF</th>
                <th>FUNÇÃO</th>
                <th>ADMISSÃO</th>
                <th>TELEFONE</th>
                <th>STATUS</th>
                <th class="text-end text-center">AÇÕES</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pessoal as $p): ?>
                <?php
                $status = $p['status'] ?? 'Ativo';

                if ($status === 'Ativo') {
                    $badgeClass = 'bg-success';
                } elseif ($status === 'Afastado') {
                    $badgeClass = 'bg-warning';
                } elseif ($status === 'Férias') {
                    $badgeClass = 'bg-primary';
                } elseif ($status === 'Demitido') {
                    $badgeClass = 'bg-danger';
                } else {
                    $badgeClass = 'bg-secondary';
                }

                ?>
                <tr>
                    <td class="text-center">
                        <?php if (!empty($p['avatar'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($p['avatar']) ?>"
                                 alt="<?= htmlspecialchars($p['nome'] ?? '') ?>"
                                 class="rounded-circle shadow-sm"
                                 style="width: 42px; height: 42px; object-fit: cover; border: 2px solid #df6301;">
                        <?php else: ?>
                            <span class="avatar-fallback rounded-circle d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold"
                                  style="width: 42px; height: 42px;">
                                <?= htmlspecialchars(mb_strtoupper(mb_substr($p['nome'] ?? '?', 0, 1, 'UTF-8'), 'UTF-8')) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['nome'] ?? '') ?></td>
                    <td class="text-center"><?= htmlspecialchars($p['cpf'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['funcao_nome'] ?? 'Sem função') ?></td>
                    <td><span class="d-block"><?= date('d/m/Y', strtotime($p['data_admissao'])) ?></span></td>

                    <td><?= htmlspecialchars($p['telefone'] ?? '') ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $status ?></span></td>

                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="<?= BASE_URL ?>admin/pessoal/editar/<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-primary"
                               data-bs-toggle="tooltip" data-bs-title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="<?= BASE_URL ?>admin/pessoal/deletar/<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               data-bs-toggle="tooltip" data-bs-title="Excluir"
                               onclick="return confirm('Deseja excluir este post?')">
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
