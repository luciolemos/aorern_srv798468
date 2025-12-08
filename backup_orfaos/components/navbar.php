<?php
/**
 * COMPONENTE: Navbar Reutilizável
 * 
 * Uso para site público:
 * <?php include 'components/navbar.php'; ?>
 * 
 * Uso para admin:
 * <?php include 'components/navbar.php'; ?>
 * 
 * Variáveis esperadas:
 * - $navbar_type: 'public' ou 'admin' (padrão: 'public')
 * - $logo_text: Texto da marca (padrão: 'CBMRN')
 * - $show_sidebar_toggle: true/false (padrão: false)
 */

$navbar_type = $navbar_type ?? 'public';
$logo_text = $logo_text ?? 'CBMRN';
$show_sidebar_toggle = $show_sidebar_toggle ?? false;
$current = $_GET['url'] ?? 'home';
$segments = explode('/', $current);
$mainRoute = $segments[0] ?? 'home';
?>

<!-- 🔝 NAVBAR UNIVERSAL -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm"
     style="background-color: #df6301 !important; padding: 0;"
     data-navbar-type="<?= $navbar_type ?>">
    
    <div class="container-fluid" style="padding-left: 1rem; padding-right: 1rem;">
        
        <!-- Seção Esquerda: Hamburger + Logo -->
        <div class="d-flex align-items-center">
            <?php if ($show_sidebar_toggle): ?>
                <!-- Botão Hamburger para Toggle Sidebar (igual ao mobile do site) -->
                <button class="navbar-toggler me-3" 
                        id="sidebarToggle" 
                        type="button"
                        title="Alternar menu lateral">
                    <span class="navbar-toggler-icon"></span>
                </button>
            <?php endif; ?>
            
            <!-- Logo/Brand -->
            <a class="navbar-brand d-flex align-items-center mb-0" href="<?= BASE_URL ?><?= $navbar_type === 'admin' ? 'admin/dashboard' : '' ?>">
                <?php if ($navbar_type === 'public'): ?>
                    <!-- Logo pública -->
                    <img src="<?= BASE_URL ?>assets/images/brasao_cbmrn_oficial.png" 
                         alt="CBMRN" 
                         height="40" 
                         class="me-2 navbar-logo">
                    <span class="fw-bold d-none d-md-inline"><?= $logo_text ?></span>
                <?php else: ?>
                    <!-- Logo admin (brasão) -->
                    <img src="<?= BASE_URL ?>assets/images/brasao_cbmrn_oficial.png"
                         alt="CBMRN"
                         height="40"
                         class="me-2 navbar-logo">
                    <span class="fw-bold">CBMRN</span>
                <?php endif; ?>
            </a>
        </div>
        
        <!-- Seção Direita -->
        <div class="ms-auto d-flex align-items-center gap-2 navbar-right-section">
            <?php if ($navbar_type === 'public'): ?>
                <!-- Menu Colapsável Público -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto mb-0">
                        <li class="nav-item">
                            <a class="nav-link <?= $mainRoute === 'home' ? 'active border-bottom border-2 pb-1' : '' ?>" 
                               href="<?= BASE_URL ?>home">
                                <i class="bi bi-house-door me-2"></i>INÍCIO</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $mainRoute === 'about' ? 'active border-bottom border-2 pb-1' : '' ?>" 
                               href="<?= BASE_URL ?>about">
                                <i class="bi bi-info-circle me-2"></i>SOBRE</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $mainRoute === 'blog' ? 'active border-bottom border-2 pb-1' : '' ?>" 
                               href="<?= BASE_URL ?>blog">
                                <i class="bi bi-newspaper me-2"></i>BLOG</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $mainRoute === 'contact' ? 'active border-bottom border-2 pb-1' : '' ?>" 
                               href="<?= BASE_URL ?>contact">
                                <i class="bi bi-envelope me-2"></i>CONTATO</a>
                        </li>
                        <li class="nav-item">
                            <?php 
                            $adminLink = BASE_URL . 'admin/auth';
                            if (isset($_SESSION['user_id'])) {
                                $adminLink = BASE_URL . 'admin/dashboard';
                            }
                            ?>
                            <a class="nav-link <?= $mainRoute === 'admin' ? 'active border-bottom border-2 pb-1' : '' ?>" 
                               href="<?= $adminLink ?>">
                                <i class="bi bi-speedometer2 me-1"></i>ADMIN</a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <?php
                $userName = $_SESSION['user_name'] ?? $_SESSION['user'] ?? 'Administrador';
                $userEmail = $_SESSION['user_email'] ?? 'admin@cbmrn.gov.br';
                $userRole = $_SESSION['user_role'] ?? 'admin';
                $userAvatar = $_SESSION['user_avatar'] ?? null;
                $userInitial = strtoupper(substr($userName, 0, 1));
                if (function_exists('mb_substr')) {
                    $userInitial = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
                }
                $avatarUrl = $userAvatar ? BASE_URL . ltrim($userAvatar, '/') : null;
                ?>

                <button id="sidebarCollapseBtn"
                        type="button"
                        class="btn btn-outline-light btn-sm d-none d-lg-inline-flex align-items-center gap-1 me-2"
                        title="Recolher menu"
                        aria-label="Alternar menu lateral">
                    <i class="bi bi-arrow-left-square-fill fs-5"></i>
                </button>

                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2 admin-user-toggle"
                            type="button"
                            id="adminUserDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <span class="admin-user-avatar">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($userName) ?>" class="rounded-circle">
                            <?php else: ?>
                                <span class="admin-user-initial"><?= htmlspecialchars($userInitial) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="d-none d-md-flex flex-column lh-1 text-start">
                            <span class="fw-semibold"><?= htmlspecialchars($userName) ?></span>
                            <small class="opacity-75"><?= htmlspecialchars($userEmail) ?></small>
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end p-0 overflow-hidden admin-user-dropdown" aria-labelledby="adminUserDropdown">
                        <li class="admin-user-dropdown-header p-3 bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <div class="admin-user-avatar-lg">
                                    <?php if ($avatarUrl): ?>
                                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($userName) ?>" class="rounded-circle">
                                    <?php else: ?>
                                        <span class="admin-user-initial-lg"><?= htmlspecialchars($userInitial) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-start">
                                    <div class="fw-semibold mb-1"><?= htmlspecialchars($userName) ?></div>
                                    <div class="text-muted small mb-1"><?= htmlspecialchars($userEmail) ?></div>
                                    <span class="badge text-bg-light text-uppercase small px-2 py-1 admin-user-role">
                                        <?= htmlspecialchars($userRole) ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-0"></li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/perfil">
                                <i class="bi bi-person-circle me-2 text-primary"></i> Meu Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/configuracoes">
                                <i class="bi bi-gear-wide-connected me-2 text-primary"></i> Preferências
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-0"></li>
                        <li>
                            <a class="dropdown-item text-danger py-2 d-flex align-items-center" href="<?= BASE_URL ?>admin/auth/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
