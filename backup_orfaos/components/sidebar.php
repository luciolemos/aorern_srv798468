<?php
/**
 * COMPONENTE: Sidebar Admin Reutilizável
 * 
 * Uso:
 * <?php 
 *   $mainRoute = 'admin';
 *   $subRoute = 'dashboard';
 *   include 'components/sidebar.php';
 * ?>
 */

$mainRoute = $mainRoute ?? 'admin';
$subRoute = $subRoute ?? null;
?>

<!-- Sidebar -->
<aside class="admin-sidebar bg-dark text-white" id="adminSidebar">
    <div class="d-flex flex-column h-100">
        <!-- Header do Sidebar -->
        <div class="p-3 border-bottom border-secondary">
            <span class="text-uppercase small" style="color: #df6301; font-weight: 600; letter-spacing: 1px;">Menu</span>
        </div>

        <!-- Menu Principal -->
        <ul class="nav flex-column px-2">

            <!-- INÍCIO -->
            <li class="nav-item">
                <a href="<?= BASE_URL ?>admin/dashboard"
                   class="nav-link text-white py-2 px-3 rounded hover-effect <?= $subRoute === 'dashboard' ? 'bg-secondary' : '' ?>">
                    <i class="bi bi-house-door-fill me-2"></i> Início
                </a>
            </li>

            <!-- GESTÃO DE CONTEÚDO -->
            <li class="nav-item">
                <a class="nav-link text-white py-2 px-3 d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse" href="#gestaoCollapse" role="button" aria-expanded="false">
                    <span><i class="bi bi-folder me-2"></i> Gestão de Conteúdo</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= in_array($subRoute, ['posts', 'post-categories']) ? 'show' : '' ?>" id="gestaoCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/posts"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'posts' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-journal-text me-1"></i> Posts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/post-categories"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'post-categories' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-tags me-1"></i> Categorias
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- GESTÃO DE BOMBEIROS -->
            <li class="nav-item mt-2">
                <a class="nav-link text-white py-2 px-3 d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse" href="#pessoalCollapse" role="button" aria-expanded="false">
                    <span><i class="bi bi-people-fill me-2"></i> Gestão de Pessoal</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= in_array($subRoute, ['pessoal', 'funcoes']) ? 'show' : '' ?>" id="pessoalCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/pessoal"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'pessoal' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-person-lines-fill me-1"></i> Bombeiros
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/funcoes"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'funcoes' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-tags me-1"></i> Funções
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- GESTÃO DE FERRAMENTAS -->
            <li class="nav-item mt-2">
                <a class="nav-link text-white py-2 px-3 d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse" href="#equipCollapse" role="button" aria-expanded="false">
                    <span><i class="bi bi-wrench-adjustable-circle me-2"></i> Gestão de Equipamentos</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= in_array($subRoute, ['equipamentos', 'categorias']) ? 'show' : '' ?>" id="equipCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/equipamentos"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'equipamentos' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-hammer me-1"></i> Equipamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/categorias"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'categorias' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-tags me-1"></i> Categorias
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- DOCUMENTAÇÃO -->
            <li class="nav-item mt-2">
                <a class="nav-link text-white py-2 px-3 d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse" href="#documentacaoCollapse" role="button" aria-expanded="false">
                    <span><i class="bi bi-folder me-2"></i> Documentação</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $mainRoute === 'docs' ? 'show' : '' ?>" id="documentacaoCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/estrutura"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'estrutura' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-diagram-3 me-1"></i> Estrutura
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/virtualhost"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'virtualhost' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-hdd-rack me-1"></i> Virtual Host
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/composer"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'composer' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-box-seam me-1"></i> Composer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/fluxomvc"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'fluxomvc' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-arrow-repeat me-1"></i> Fluxo MVC
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/fluxopost"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'fluxopost' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-diagram-2 me-1"></i> Fluxo POST
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/novofluxomvc"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'novofluxomvc' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-diagram-3-fill me-1"></i> Novo Fluxo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/diagrama"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'diagrama' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-diagram-3 me-1"></i> Diagrama
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/caracteristicas"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'caracteristicas' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-star-fill me-1"></i> Características
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/blog"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'blog' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-journal-richtext me-1"></i> Blog
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/docs/elements"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'elements' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-journal-richtext me-1"></i> Elementos
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- CONFIGURAÇÕES -->
            <li class="nav-item mt-2">
                <a class="nav-link text-white py-2 px-3 d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse" href="#configCollapse" role="button" aria-expanded="false">
                    <span><i class="bi bi-gear me-2"></i> Configurações</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= in_array($subRoute, ['status', 'versions', 'info']) ? 'show' : '' ?>" id="configCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/status"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'status' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-check-circle me-1"></i> Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/system/versions"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'versions' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-code-slash me-1"></i> Versões
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>admin/system/info"
                               class="nav-link text-white py-1 px-3 small <?= $subRoute === 'info' ? 'bg-secondary' : '' ?>">
                                <i class="bi bi-info-circle me-1"></i> Info
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>

        <!-- Footer do Sidebar -->
        <div class="mt-auto p-3 small border-top border-secondary text-center text-secondary" style="background-color: rgba(0,0,0,0.2);">
            <span>© <?= date('Y') ?> MyApp</span>
        </div>
    </div>
</aside>
