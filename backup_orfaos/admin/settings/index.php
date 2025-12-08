<?php
$title = $data['title'] ?? 'Configurações';
?>

<div class="container-fluid p-4">
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 fw-bold mb-1" style="color: #212529;">
                        <i class="bi bi-gear me-2" style="color: #df6301;"></i><?= htmlspecialchars($title) ?>
                    </h1>
                    <p class="text-muted mb-0">Personalize suas preferências do sistema</p>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-lg-8">
            <!-- Preferências de Tema -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-palette me-2"></i>Tema e Aparência
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>admin/configuracoes/update" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Modo de Tema</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="theme" id="theme-light" value="light" checked>
                                <label class="btn btn-outline-primary flex-grow-1" for="theme-light" style="border-color: #df6301; color: #df6301;">
                                    <i class="bi bi-sun me-2"></i>Claro
                                </label>

                                <input type="radio" class="btn-check" name="theme" id="theme-dark" value="dark">
                                <label class="btn btn-outline-primary flex-grow-1" for="theme-dark" style="border-color: #df6301; color: #df6301;">
                                    <i class="bi bi-moon me-2"></i>Escuro
                                </label>

                                <input type="radio" class="btn-check" name="theme" id="theme-auto" value="auto">
                                <label class="btn btn-outline-primary flex-grow-1" for="theme-auto" style="border-color: #df6301; color: #df6301;">
                                    <i class="bi bi-brightness-high me-2"></i>Automático
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Escolha como o painel será exibido</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sidebar-collapsed" name="sidebar_collapsed" value="1">
                                <label class="form-check-label" for="sidebar-collapsed">
                                    Manter menu lateral recolhido por padrão
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn" style="background-color: #df6301; color: white; border: none;">
                            <i class="bi bi-check-circle me-2"></i>Salvar Preferências
                        </button>
                    </form>
                </div>
            </div>

            <!-- Notificações -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-bell me-2"></i>Notificações
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>admin/configuracoes/update" method="POST">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notify-email" name="notify_email" value="1" checked>
                            <label class="form-check-label" for="notify-email">
                                <strong>Notificações por Email</strong>
                                <small class="d-block text-muted">Receba alertas importantes por email</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notify-system" name="notify_system" value="1" checked>
                            <label class="form-check-label" for="notify-system">
                                <strong>Notificações do Sistema</strong>
                                <small class="d-block text-muted">Exiba notificações no painel</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="notify-updates" name="notify_updates" value="1">
                            <label class="form-check-label" for="notify-updates">
                                <strong>Atualizações e Novidades</strong>
                                <small class="d-block text-muted">Receba informações sobre novas funcionalidades</small>
                            </label>
                        </div>

                        <button type="submit" class="btn" style="background-color: #df6301; color: white; border: none;">
                            <i class="bi bi-check-circle me-2"></i>Salvar Notificações
                        </button>
                    </form>
                </div>
            </div>

            <!-- Privacidade -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-shield-lock me-2"></i>Privacidade e Segurança
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Controle como seus dados são usados e armazenados no sistema.</p>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="profile-public" name="profile_public" value="1">
                        <label class="form-check-label" for="profile-public">
                            <strong>Perfil Público</strong>
                            <small class="d-block text-muted">Permita que outros usuários vejam seu perfil</small>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="two-factor" name="two_factor" value="1">
                        <label class="form-check-label" for="two-factor">
                            <strong>Autenticação em Dois Fatores</strong>
                            <small class="d-block text-muted">Adicione uma camada extra de segurança à sua conta</small>
                        </label>
                    </div>

                    <a href="<?= BASE_URL ?>admin/alterar-senha" class="btn btn-outline-primary">
                        <i class="bi bi-key me-2"></i>Alterar Senha
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar - Informações Úteis -->
        <div class="col-lg-4">
            <!-- Card de Ajuda -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-question-circle me-2"></i>Precisa de Ajuda?
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <a href="<?= BASE_URL ?>admin/docs" class="text-decoration-none" style="color: #df6301;">
                            <i class="bi bi-file-pdf me-2"></i>Documentação
                        </a>
                    </p>
                    <p class="mb-3">
                        <a href="<?= BASE_URL ?>admin/help" class="text-decoration-none" style="color: #df6301;">
                            <i class="bi bi-chat-dots me-2"></i>Contato com Suporte
                        </a>
                    </p>
                    <p class="mb-0">
                        <a href="<?= BASE_URL ?>admin/faq" class="text-decoration-none" style="color: #df6301;">
                            <i class="bi bi-question-square me-2"></i>Perguntas Frequentes
                        </a>
                    </p>
                </div>
            </div>

            <!-- Sobre -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-info-circle me-2"></i>Sobre
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        <strong>Sistema CBMRN</strong><br>
                        Versão 1.0.0
                    </p>
                    <p class="text-muted small mb-0">
                        © 2025 Corpo de Bombeiros Militar do RN
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-group .btn {
    border-color: #df6301 !important;
    color: #df6301 !important;
}

.btn-group .btn.active {
    background-color: #df6301 !important;
    color: white !important;
}

.form-check-input {
    border-color: #df6301;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #df6301;
    border-color: #df6301;
}

.form-check-input:focus {
    border-color: #df6301;
    box-shadow: 0 0 0 0.2rem rgba(223, 99, 1, 0.25);
}

.card {
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}
</style>
