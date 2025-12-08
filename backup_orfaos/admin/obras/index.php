<?php
$q = $q ?? '';
$placeholder = 'Buscar obra por DESCRIÇÃO...';
?>

<div class="container-fluid px-3 px-md-4 py-4">
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-list-columns-reverse me-2"></i> Obras e serviços de engenharia</h2>
        <p class="lead text-muted">Lista de obras/serviços</p>
        <hr class="my-4">
        <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-start mt-4 mb-4">
            <a href="<?= BASE_URL ?>admin/obras/cadastrar" class="btn btn-primary text-white order-md-1">
                <i class="bi bi-plus-circle"></i> Nova obra ou serviço
            </a>
        </div>
    </div>

    <div data-aos="fade-up">
        <?php include __DIR__ . '/../../partials/search_bar.php'; ?>
    </div>
    <?php if (empty($obras)): ?>
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
                        <th>NATUREZA</th>
                        <th>DESCRIÇÃO</th>
                        <th>STATUS</th>
                        <th>INÍCIO</th>
                        <th>TÉRMINO</th>
                        <th>DURAÇÃO</th>
                        <th>PRIORIDADE</th>
                        <th class="text-end text-center">AÇÕES</th>
               </tr>
          </thead>
          <tbody>
                   <?php foreach ($obras as $obra): ?>
                       <?php
                       $status = $obra['status'] ?? 'Em Andamento';

                       if ($status === 'Em Andamento') {
                           $badgeClass = 'bg-success';
                       } elseif ($status === 'Pausada') {
                           $badgeClass = 'bg-danger';
                       } elseif ($status === 'Planejamento') {
                           $badgeClass = 'bg-warning';
                       } elseif ($status === 'Concluída') {
                           $badgeClass = 'bg-primary';
                       } else {
                           $badgeClass = 'bg-secondary';
                       };

                       ?>


                       <?php
                       $prioridade = $obra['prioridade'] ?? 'Alta';

                       if ($prioridade === 'Alta') {
                           $badClass = 'bg-warning';
                       } elseif ($prioridade === 'Média') {
                           $badClass = 'bg-info';
                       } elseif ($prioridade === 'Baixa') {
                           $badClass = 'bg-secondary';
                       } elseif ($prioridade === 'Urgente') {
                           $badClass = 'bg-danger';
                       } else {
                           $badClass = 'bg-secondary';
                       };

                       ?>

                 <tr>
                        <td class="text-center"><i class="bi bi-house-add text-primary"></i></td>
                        <td><?= $obra['numero_obra'] ?></td>
                        <td><?= $obra['natureza_obra'] ?></td>
                        <td><?= $obra['descricao'] ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= $status ?></span></td>
                        <td><span class="d-block"><?= date('d/m/Y', strtotime($obra['data_inicio'])) ?></span></td>
                        <td><span class="d-block"><?= date('d/m/Y', strtotime($obra['data_termino'])) ?></span></td>
                        <td><span class="badge bg-primary-light text-dark">8 meses</span></td>
                        <td><span class="badge <?= $badClass ?>"><?= $prioridade ?></span></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                             <a href="<?= BASE_URL ?>admin/obras/editar/<?= $obra['id'] ?>"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="tooltip" data-bs-title="Editar">
                                 <i class="bi bi-pencil-square"></i>
                             </a>
                             <a href="<?= BASE_URL ?>admin/obras/deletar/<?= $obra['id'] ?>"
                                class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="tooltip" data-bs-title="Excluir"
                                onclick="return confirm('Deseja excluir este post?')">
                                 <i class="bi bi-trash"></i>
                             </a>
                         </div>
                     </td>
                </tr>
            <?php endforeach; ?>
                   <?php if (empty($obras)): ?>
                   <tr><td colspan="7" class="text-center text-muted">Nenhuma obra cadastrada.</td></tr>
                   <?php endif; ?>
          </tbody>
       </table>
    </div>
    <?php endif ?>
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">Total: <?= count($obras) ?> obras</div>
        <a href="<?= BASE_URL ?>admin" class="btn btn-primary text-white">
            <i class="bi bi-speedometer2 me-2"></i> Painel
        </a>
    </div>
</div>