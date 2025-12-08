<?php include __DIR__ . '/../partials/dashboard_card_function.php'; ?>

<div class="container-fluid px-3 px-md-4 py-4">
    <div data-aos="fade-down">
        <h2 class="text-dark"><i class="bi bi-speedometer2 me-1"></i> Painel Admin</h2>
        <p class="lead text-muted">Gerencie todos os recursos da sua obra</p>
    </div>
    <hr class="my-4">

    <!-- 🔹 GESTÃO DE PESSOAL -->
    <h4 class="text-success mb-3"><i class="bi bi-people-fill me-2"></i> Gestão de Pessoal</h4>
    <div class="row g-4 mb-5" data-aos="fade-up">
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
            'btn_label' => 'Cadastrar',
            'footer' => '<i class="bi bi-person-plus me-1"></i> Adicione funções'
        ]);

        renderDashboardCard([
            'icon' => 'bi bi-person',
            'icon_bg' => 'bg-info-light',
            'icon_color' => 'text-success',
            'title' => 'Listar funções',
            'title_color' => 'text-success',
            'description' => 'Consulte e gerencie as funções na sua empresa',
            'progress' => 75,
            'progress_color' => 'bg-success',
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
            'btn_label' => 'Cadastrar',
            'footer' => '<i class="bi bi-person-plus me-1"></i> Adicione sua equipe'
        ]);

        renderDashboardCard([
            'icon' => 'bi bi-person',
            'icon_bg' => 'bg-info-light',
            'icon_color' => 'text-success',
            'title' => 'Listar bombeiros',
            'title_color' => 'text-success',
            'description' => 'Consulte e gerencie todos os bombeiros da obra',
            'progress' => 75,
            'progress_color' => 'bg-success',
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
    <h4 class="text-info mb-3"><i class="bi bi-tools me-2"></i> Equipamentos</h4>
    <div class="row g-4 mb-5" data-aos="fade-up">
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
            'btn_label' => 'Cadastrar',
            'footer' => '<i class="bi bi-clock me-1"></i> Atualizado recentemente'
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
            'btn_label' => 'Cadastrar',
            'footer' => '<i class="bi bi-tools me-1"></i> Cadastre equipamentos e ferramentas'
        ]);

        renderDashboardCard([
            'icon' => 'bi bi-list-columns-reverse',
            'icon_bg' => 'bg-primary-light',
            'icon_color' => 'text-info',
            'title' => 'Listar equipamentos',
            'title_color' => 'text-info',
            'description' => 'Equipamentos cadastrados.',
            'progress' => 78,
            'progress_color' => 'bg-info',
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
    <h4 class="text-danger mb-3"><i class="bi bi-bricks me-2"></i> Obras e Serviços</h4>
    <div class="row g-4 mb-5" data-aos="fade-up">
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
            'btn_label' => 'Visualizar',
            'footer' => '<i class="bi bi-houses me-1"></i> Cadastre obras e serviços'
        ]);
        ?>
    </div>
</div>
