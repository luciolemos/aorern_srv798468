<?php include __DIR__ . '/../partials/dashboard_card_function.php'; ?>

<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
        <div>
            <h2 class="text-primary"><i class="bi bi-speedometer2 me-2"></i> Painel de controle</h2>
            <p class="text-muted mb-0"><i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y') ?></p>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">Último acesso</small>
            <strong class="text-primary"><?= date('d/m/Y H:i', $ultimo_login) ?></strong>
        </div>
    </div>

    <!-- KPI Cards - Estatísticas Principais -->
    <div class="row g-4 mb-5" data-aos="fade-up">
        <!-- Card: Total de Bombeiros -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-success-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-box bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-people-fill fs-2 text-success"></i>
                        </div>
                        <span class="badge bg-success bg-opacity-25 text-success">Ativos</span>
                    </div>
                    <h3 class="fw-bold text-success mb-1"><?= $total_pessoal ?></h3>
                    <p class="text-muted mb-0 small">Bombeiros cadastrados</p>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Total de Equipamentos -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-info-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-box bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-tools fs-2 text-info"></i>
                        </div>
                        <span class="badge bg-info bg-opacity-25 text-info">Disponíveis</span>
                    </div>
                    <h3 class="fw-bold text-info mb-1"><?= $total_equipamentos ?></h3>
                    <p class="text-muted mb-0 small">Equipamentos registrados</p>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 70%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Total de Obras -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-danger-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-box bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-bricks fs-2 text-danger"></i>
                        </div>
                        <span class="badge bg-danger bg-opacity-25 text-danger">Em andamento</span>
                    </div>
                    <h3 class="fw-bold text-danger mb-1"><?= $total_obras ?></h3>
                    <p class="text-muted mb-0 small">Obras ativas</p>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Total de Funções -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-warning-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-box bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-tags-fill fs-2 text-warning"></i>
                        </div>
                        <span class="badge bg-warning bg-opacity-25 text-warning">Cadastradas</span>
                    </div>
                    <h3 class="fw-bold text-warning mb-1"><?= $total_funcoes ?></h3>
                    <p class="text-muted mb-0 small">Funções disponíveis</p>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="card border-0 shadow-sm mb-5" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-lightning-charge-fill text-primary me-2"></i> Ações Rápidas</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>admin/pessoal/cadastrar" class="btn btn-success w-100 py-3 d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-person-plus-fill me-2 fs-5"></i>
                        <span class="fw-semibold">Cadastrar Bombeiro</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>admin/equipamentos/cadastrar" class="btn btn-info text-white w-100 py-3 d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-tools me-2 fs-5"></i>
                        <span class="fw-semibold">Novo Equipamento</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>admin/obras/cadastrar" class="btn btn-danger w-100 py-3 d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-building-add me-2 fs-5"></i>
                        <span class="fw-semibold">Nova Obra</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Atividades Recentes -->
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-primary me-2"></i> Últimos Cadastros</h5>
                    <span class="badge bg-primary">Recente</span>
                </div>
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">Bombeiros</h6>
                    <div class="list-group list-group-flush mb-4">
                        <?php if (!empty($ultimos_bombeiros)): ?>
                            <?php foreach ($ultimos_bombeiros as $func): ?>
                                <div class="list-group-item border-0 px-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="bi bi-person text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 fw-semibold"><?= htmlspecialchars($func['nome'] ?? 'N/A') ?></p>
                                            <small class="text-muted"><?= htmlspecialchars($func['funcao_nome'] ?? 'Sem função') ?></small>
                                        </div>
                                        <small class="text-muted">ID #<?= $func['id'] ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Nenhum bombeiro cadastrado ainda</p>
                        <?php endif; ?>
                    </div>

                    <h6 class="text-muted text-uppercase small mb-3">Equipamentos</h6>
                    <div class="list-group list-group-flush">
                        <?php if (!empty($ultimos_equipamentos)): ?>
                            <?php foreach ($ultimos_equipamentos as $eqp): ?>
                                <div class="list-group-item border-0 px-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="bi bi-tools text-info"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 fw-semibold"><?= htmlspecialchars($eqp['nome'] ?? 'N/A') ?></p>
                                            <small class="text-muted"><?= htmlspecialchars($eqp['categoria'] ?? 'Sem categoria') ?></small>
                                        </div>
                                        <small class="text-muted">ID #<?= $eqp['id'] ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Nenhum equipamento cadastrado ainda</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Obras em Destaque -->
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-building text-danger me-2"></i> Obras Recentes</h5>
                    <a href="<?= BASE_URL ?>admin/obras" class="text-decoration-none small">Ver todas</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($ultimas_obras)): ?>
                        <?php foreach ($ultimas_obras as $obra): ?>
                            <div class="card mb-3 border-start border-4 border-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($obra['descricao'] ?? 'Obra sem descrição') ?></h6>
                                        <span class="badge bg-danger"><?= htmlspecialchars($obra['status'] ?? 'Ativa') ?></span>
                                    </div>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($obra['endereco'] ?? 'Endereço não informado') ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i> 
                                            Início: <?= htmlspecialchars($obra['data_inicio'] ?? 'N/A') ?>
                                        </small>
                                        <a href="<?= BASE_URL ?>admin/obras/detalhes/<?= $obra['id'] ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-building display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3">Nenhuma obra cadastrada ainda</p>
                            <a href="<?= BASE_URL ?>admin/obras/cadastrar" class="btn btn-danger">
                                <i class="bi bi-plus-circle me-1"></i> Cadastrar Obra
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção Anterior - Cards de Gestão (mantidos para compatibilidade) -->
    <div class="accordion mb-4" id="accordionGestao">
        <div class="accordion-item border-0 shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGestao">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i> Gestão Completa do Sistema
                </button>
            </h2>
            <div id="collapseGestao" class="accordion-collapse collapse" data-bs-parent="#accordionGestao">
                <div class="accordion-body">
                    <!-- 🔹 GESTÃO DE PESSOAL -->
                    <h5 class="text-success mb-3"><i class="bi bi-people-fill me-2"></i> Gestão de Pessoal</h5>
                    <div class="row g-4 mb-5">
                        <?php
                        renderDashboardCard([
                            'icon' => 'bi bi-person',
                            'icon_bg' => 'bg-info-light',
                            'icon_color' => 'text-success',
                            'title' => 'Cadastrar função',
                            'title_color' => 'text-success',
                            'description' => 'Cadastre funções na sua obra',
                            'badge_class' => 'bg-info-light text-success',
                            'badge_text' => 'Essencial',
                            'link' => BASE_URL . 'admin/funcoes/cadastrar',
                            'btn_class' => 'btn-success',
                            'btn_icon' => 'bi bi-plus-circle',
                            'btn_label' => 'Cadastrar'
                        ]);

                        renderDashboardCard([
                            'icon' => 'bi bi-person',
                            'icon_bg' => 'bg-info-light',
                            'icon_color' => 'text-success',
                            'title' => 'Listar funções',
                            'title_color' => 'text-success',
                            'description' => 'Consulte e gerencie as funções na sua empresa',
                            'badge_class' => 'bg-info-light text-primary',
                            'badge_text' => "$total_funcoes Funções",
                            'link' => BASE_URL . 'admin/funcoes',
                            'btn_class' => 'btn-success',
                            'btn_icon' => 'bi bi-eye',
                            'btn_label' => 'Visualizar'
                        ]);

                        renderDashboardCard([
                            'icon' => 'bi bi-person',
                            'icon_bg' => 'bg-info-light',
                            'icon_color' => 'text-success',
                            'title' => 'Cadastrar bombeiro',
                            'title_color' => 'text-success',
                            'description' => 'Cadastre novos bombeiros ou equipes para sua obra',
                            'badge_class' => 'bg-info-light text-success',
                            'badge_text' => 'Essencial',
                            'link' => BASE_URL . 'admin/pessoal/cadastrar',
                            'btn_class' => 'btn-success',
                            'btn_icon' => 'bi bi-plus-circle',
                            'btn_label' => 'Cadastrar'
                        ]);

                        renderDashboardCard([
                            'icon' => 'bi bi-person',
                            'icon_bg' => 'bg-info-light',
                            'icon_color' => 'text-success',
                            'title' => 'Listar bombeiros',
                            'title_color' => 'text-success',
                            'description' => 'Consulte e gerencie todos os bombeiros da obra',
                            'badge_class' => 'bg-info-light text-success',
                            'badge_text' => "$total_pessoal Cadastrados",
                            'link' => BASE_URL . 'admin/pessoal',
                            'btn_class' => 'btn-success',
                            'btn_icon' => 'bi bi-eye',
                            'btn_label' => 'Visualizar'
                        ]);
                        ?>
                    </div>

                    <!-- 🔹 EQUIPAMENTOS -->
                    <h5 class="text-info mb-3"><i class="bi bi-tools me-2"></i> Equipamentos</h5>
                    <div class="row g-4 mb-5">
                        <?php
                        renderDashboardCard([
                            'icon' => 'bi bi-gear',
                            'icon_bg' => 'bg-primary-light',
                            'icon_color' => 'text-info',
                            'title' => 'Cadastrar categoria',
                            'title_color' => 'text-info',
                            'description' => 'Gerencie as categorias de equipamentos.',
                            'badge_class' => 'bg-info text-white',
                            'badge_text' => 'Novo',
                            'link' => BASE_URL . 'admin/categorias/cadastrar',
                            'btn_class' => 'btn-info text-white',
                            'btn_icon' => 'bi bi-plus-circle',
                            'btn_label' => 'Cadastrar'
                        ]);

                        renderDashboardCard([
                            'icon' => 'bi bi-list-columns-reverse',
                            'icon_bg' => 'bg-primary-light',
                            'icon_color' => 'text-info',
                            'title' => 'Listar categorias',
                            'title_color' => 'text-info',
                            'description' => 'Lista das categorias de equipamentos.',
                            'badge_class' => 'bg-info text-white',
                            'badge_text' => "$total_categoria_eqp Categorias",
                            'link' => BASE_URL . 'admin/categorias',
                            'btn_class' => 'btn-info text-white',
                            'btn_icon' => 'bi bi-eye',
                            'btn_label' => 'Visualizar'
                        ]);

                        renderDashboardCard([
                            'icon' => 'bi bi-tools',
                            'icon_bg' => 'bg-primary-light',
                            'icon_color' => 'text-info',
                            'title' => 'Cadastrar equipamento',
                            'title_color' => 'text-info',
                            'description' => 'Adicione novos equipamentos.',
                            'badge_class' => 'bg-info text-white',
                            'badge_text' => 'Popular',
                            'link' => BASE_URL . 'admin/equipamentos/cadastrar',
                            'btn_class' => 'btn-info text-white',
                            'btn_icon' => 'bi bi-plus-circle',
                            'btn_label' => 'Cadastrar'
                        ]);

                        renderDashboardCard([
                            'icon' => 'bi bi-list-columns-reverse',
                            'icon_bg' => 'bg-primary-light',
                            'icon_color' => 'text-info',
                            'title' => 'Listar equipamentos',
                            'title_color' => 'text-info',
                            'description' => 'Equipamentos cadastrados.',
                            'badge_class' => 'bg-info text-white',
                            'badge_text' => "$total_equipamentos Equipamentos",
                            'link' => BASE_URL . 'admin/equipamentos',
                            'btn_class' => 'btn-info text-white',
                            'btn_icon' => 'bi bi-eye',
                            'btn_label' => 'Visualizar'
                        ]);
                        ?>
                    </div>

                    <!-- 🔹 OBRAS -->
                    <h5 class="text-danger mb-3"><i class="bi bi-bricks me-2"></i> Obras e Serviços</h5>
                    <div class="row g-4">
                        <?php
                        renderDashboardCard([
                            'icon' => 'bi bi-house-add',
                            'icon_bg' => 'bg-info-light',
                            'icon_color' => 'text-danger',
                            'title' => 'Listar obra',
                            'title_color' => 'text-danger',
                            'description' => 'Cadastre sua obra ou serviço de engenharia',
                            'badge_class' => 'bg-danger text-white',
                            'badge_text' => "$total_obras Obras",
                            'link' => BASE_URL . 'admin/obras',
                            'btn_class' => 'btn-danger text-white',
                            'btn_icon' => 'bi bi-eye',
                            'btn_label' => 'Visualizar'
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-success-light {
    background: linear-gradient(135deg, #d4edda 0%, #ffffff 100%);
}
.bg-gradient-info-light {
    background: linear-gradient(135deg, #d1ecf1 0%, #ffffff 100%);
}
.bg-gradient-danger-light {
    background: linear-gradient(135deg, #f8d7da 0%, #ffffff 100%);
}
.bg-gradient-warning-light {
    background: linear-gradient(135deg, #fff3cd 0%, #ffffff 100%);
}
.icon-box {
    transition: all 0.3s ease;
}
.card:hover .icon-box {
    transform: scale(1.1) rotate(5deg);
}
</style>
