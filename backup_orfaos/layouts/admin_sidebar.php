<?php
$menu = [
    'dashboard' => [
        'icon' => 'house-door-fill',
        'label' => 'Início',
        'route' => 'admin/dashboard'
    ],

    'post' => [
        'label' => 'Gestão de conteúdo',
        'icon' => 'book',
        'items' => [
            ['label' => 'Posts', 'route' => 'admin/posts', 'icon' => 'journal-text']
        ]
    ],

    'pessoal' => [
        'label' => 'Gestão de pessoal',
        'icon' => 'people',
        'items' => [
            ['label' => 'Bombeiros', 'route' => 'admin/pessoal', 'icon' => 'list-columns-reverse'],
            ['label' => 'Cargos e funções', 'route' => 'admin/funcoes', 'icon' => 'tags']
        ]
    ],

    'obras' => [
        'label' => 'Obras e serviços',
        'icon' => 'bricks',
        'items' => [
            ['label' => 'Listar', 'route' => 'admin/obras', 'icon' => 'list-columns-reverse']
        ]
    ],

    'equipamentos' => [
        'label' => 'Máquinas e equipamentos',
        'icon' => 'tools',
        'items' => [
            ['label' => 'Listar', 'route' => 'admin/equipamentos', 'icon' => 'list-columns-reverse'],
            ['label' => 'Categorias', 'route' => 'admin/categorias', 'icon' => 'tags']
        ]
    ],
    'docs' => [
        'label' => 'Documentação',
        'icon' => 'book',
        'items' => [
            ['label' => 'Sumário', 'route' => 'admin/docs/index', 'icon' => 'tags'],
            ['label' => 'Características', 'route' => 'admin/docs/caracteristicas', 'icon' => 'tags'],
            ['label' => 'Estrutura', 'route' => 'admin/docs/estrutura', 'icon' => 'tags'],
            ['label' => 'Virtual host', 'route' => 'admin/docs/virtualhost', 'icon' => 'tags'],
            ['label' => 'Composer', 'route' => 'admin/docs/composer', 'icon' => 'tags'],
            ['label' => 'Fluxo MVC', 'route' => 'admin/docs/fluxomvc', 'icon' => 'tags'],
            ['label' => 'Fluxo do REQUEST', 'route' => 'admin/docs/fluxopost', 'icon' => 'tags'],
            ['label' => 'Diagrama projeto', 'route' => 'admin/docs/diagrama', 'icon' => 'tags'],
            ['label' => 'Elementos gráficos', 'route' => 'admin/docs/elements', 'icon' => 'tags'],
            ['label' => 'Scripts SQL', 'route' => 'admin/docs/scripts', 'icon' => 'tags'],
            ['label' => 'Blog', 'route' => 'admin/docs/blog', 'icon' => 'tags'],
            ['label' => 'Novo fluxo MVC', 'route' => 'admin/docs/novofluxomvc', 'icon' => 'tags']
        ]
    ],
    'status' => [
        'label' => 'Configuração',
        'icon' => 'gear',
        'items' => [
            ['label' => 'Integridade do sistema', 'route' => 'admin/status', 'icon' => 'check-circle'],
            ['label' => 'Versões', 'route' => 'admin/system/versions', 'icon' => 'code-slash'],
            ['label' => 'Info', 'route' => 'admin/system/info', 'icon' => 'info-circle']
        ]
    ]
];

function renderMenu($menu, $subRoute) {
    foreach ($menu as $key => $item) {
        if (isset($item['items'])) {
            $collapseId = $key . 'Collapse';
            $isActive = in_array($subRoute, array_map(fn($i) => basename($i['route']), $item['items']));
            echo "<li class=\"nav-item\">";
            echo "<a class=\"nav-link text-white py-2 px-3 d-flex justify-content-between align-items-center rounded" .
                ($isActive ? '' : ' collapsed') . "\" data-bs-toggle=\"collapse\" href=\"#$collapseId\">";
            echo "<span><i class=\"bi bi-{$item['icon']} me-2\"></i> {$item['label']}</span><i class=\"bi bi-chevron-down small\"></i></a>";
            echo "<div class=\"collapse" . ($isActive ? ' show' : '') . "\" id=\"$collapseId\"><ul class=\"nav flex-column ms-3\">";
            foreach ($item['items'] as $sub) {
                $active = $subRoute === basename($sub['route']) ? 'active' : '';
                echo "<li class=\"nav-item\"><a href=\"" . BASE_URL . $sub['route'] .
                    "\" class=\"nav-link text-white py-1 px-3 small rounded $active\">";
                echo "<i class=\"bi bi-{$sub['icon']} me-1\"></i> {$sub['label']}</a></li>";
            }
            echo "</ul></div></li>";
        } else {
            $active = $subRoute === basename($item['route']) ? 'active' : '';
            echo "<li class=\"nav-item\"><a href=\"" . BASE_URL . $item['route'] .
                "\" class=\"nav-link text-white py-2 px-3 rounded $active\">";
            echo "<i class=\"bi bi-{$item['icon']} me-2\"></i> {$item['label']}</a></li>";
        }
        echo "<hr class=\"sidebar-divider my-0\">";
    }
}
?>

<aside class="admin-sidebar text-white" style="background: linear-gradient(180deg, #df6301 0%, #b54f01 100%);">
    <div class="d-flex flex-column min-vh-100">
        <!-- Logo e Branding -->
        <div class="sidebar-brand p-4 text-center border-bottom" style="border-color: rgba(255,255,255,0.2) !important;">
            <div class="brand-icon mb-3">
                <img src="<?= BASE_URL ?>assets/images/brasao_cbmrn_oficial.png" alt="CBMRN" style="height: 130px; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));">
            </div>
            <h5 class="mb-0 fw-bold text-white">CBMRN</h5>
            <small class="d-block" style="color: rgba(255,255,255,0.8);">Sistema de Gestão</small>
        </div>

        <!-- Menu de Navegação -->
        <div class="p-3 border-bottom" style="border-color: rgba(255,255,255,0.2) !important;">
            <span class="text-uppercase small fw-bold" style="color: rgba(255,255,255,0.7);">Menu Principal</span>
        </div>
        <ul class="nav flex-column px-2 flex-grow-1">
            <?php renderMenu($menu, $subRoute ?? 'dashboard'); ?>
        </ul>

        <!-- Rodapé fixo na base da sidebar -->
        <div class="sidebar-footer mt-auto text-center small py-3 border-top w-100" style="border-color: rgba(255,255,255,0.2) !important; color: rgba(255,255,255,0.8);">
            <div class="mb-2">
                <span class="badge" style="background-color: rgba(255,255,255,0.2); color: white;">v1.0.0</span>
            </div>
            <span>© CBMRN <?= date('Y') ?></span>
        </div>
    </div>
</aside>

<style>
.sidebar-brand {
    background: rgba(0, 0, 0, 0.15);
}
.brand-icon {
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
.admin-sidebar .nav-link {
    transition: all 0.3s ease;
    margin-bottom: 2px;
    color: rgba(255, 255, 255, 0.9) !important;
}
.admin-sidebar .nav-link:hover {
    background-color: rgba(0, 0, 0, 0.2);
    transform: translateX(5px);
    color: white !important;
}
.admin-sidebar .nav-link.active {
    background-color: rgba(0, 0, 0, 0.3);
    border-left: 3px solid #fff;
    color: white !important;
    font-weight: 600;
}
.admin-sidebar .collapse {
    background-color: rgba(0, 0, 0, 0.1);
}
.admin-sidebar .collapse .nav-link {
    padding-left: 2rem !important;
    font-size: 0.9rem;
}
.sidebar-divider {
    border-color: rgba(255, 255, 255, 0.1) !important;
    margin: 0.25rem 0 !important;
}
</style>
