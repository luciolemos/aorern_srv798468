<?php
$user = $data['user'] ?? [];
$title = $data['title'] ?? 'Meu Perfil';

// Dados para o form
$username = $user['username'] ?? '';
$email = $user['email'] ?? '';
$role = ucfirst($user['role'] ?? 'usuario');
$created_at = $user['created_at'] ?? '';
$avatar = $user['avatar'] ?? null;

// Gera token CSRF uma única vez para todos os forms
$csrfToken = \App\Helpers\CsrfHelper::generateToken();
?>

<div class="container-fluid p-4">
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 fw-bold mb-1" style="color: #212529;">
                        <i class="bi bi-person-circle me-2" style="color: #df6301;"></i><?= htmlspecialchars($title) ?>
                    </h1>
                    <p class="text-muted mb-0">Gerencie seus dados pessoais e preferências</p>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-lg-8">
            <!-- Formulário Único -->
            <form action="<?= BASE_URL ?>admin/perfil/update" method="POST" enctype="multipart/form-data" id="profileForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <!-- Card de Dados Pessoais -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold" style="color: #df6301;">
                            <i class="bi bi-info-circle me-2"></i>Dados Pessoais
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-bold">Usuário</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($username) ?>" 
                                       placeholder="Digite seu nome de usuário"
                                       minlength="3" maxlength="50" required>
                                <small class="text-muted">3 a 50 caracteres</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($email) ?>" 
                                       placeholder="seu@email.com"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label fw-bold">Função</label>
                                <input type="text" class="form-control" id="role" 
                                       value="<?= htmlspecialchars($role) ?>" 
                                       disabled>
                                <small class="text-muted">Somente administradores podem alterar</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="created_at" class="form-label fw-bold">Membro desde</label>
                                <input type="text" class="form-control" id="created_at" 
                                       value="<?= date('d/m/Y H:i', strtotime($created_at)) ?>" 
                                       disabled>
                            </div>
                        </div>

                        <!-- Avatar Upload no mesmo formulário -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="avatar" class="form-label fw-bold">
                                    <i class="bi bi-image me-1"></i>Foto de Perfil
                                </label>
                                <input type="file" class="form-control" id="avatar" name="avatar" 
                                       accept="image/jpeg,image/png,image/webp" 
                                       onchange="validarAvatar(this)">
                                <small class="text-muted">JPG, PNG ou WebP • Máximo 50MB (opcional)</small>
                                <small class="text-danger d-none" id="errorMsg"></small>
                                
                                <!-- Preview -->
                                <div class="mt-2">
                                    <img id="preview" style="display: none;" class="rounded" style="max-width: 200px; max-height: 200px;">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" style="background-color: #df6301; border-color: #df6301;">
                                <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                            </button>
                            <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Card de Segurança -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-shield-lock me-2"></i>Segurança
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Altere sua senha periodicamente para manter sua conta segura.</p>
                    <a href="<?= BASE_URL ?>admin/alterar-senha" class="btn btn-outline-primary">
                        <i class="bi bi-key me-2"></i>Alterar Senha
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar Avatar e Informações -->
        <div class="col-lg-4">
            <!-- Card de Preview do Avatar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-image me-2"></i>Foto Atual
                    </h5>
                </div>
                <div class="card-body text-center">
                    <!-- Avatar Atual -->
                    <div class="mb-3">
                        <?php if ($avatar): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($avatar) ?>" 
                                 alt="<?= htmlspecialchars($username) ?>" 
                                 class="rounded-circle shadow" 
                                 style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #df6301;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; color: white; font-size: 3rem; font-weight: bold; border: 4px solid #df6301;">
                                <?= strtoupper(substr($username, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-0">Altere a foto no formulário ao lado</p>
                </div>
            </div>

            <!-- Card de Estatísticas -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-graph-up me-2"></i>Informações
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">ID do Usuário</small>
                        <strong><?= htmlspecialchars($user['id'] ?? '-') ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Último Acesso</small>
                        <strong>
                            <?php 
                            $ultimo_login = $user['ultimo_login'] ?? null;
                            echo $ultimo_login ? date('d/m/Y H:i', strtotime($ultimo_login)) : 'Primeiro acesso';
                            ?>
                        </strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Última Atualização</small>
                        <strong><?= date('d/m/Y H:i', strtotime($user['updated_at'] ?? 'now')) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validarAvatar(input) {
    const preview = document.getElementById('preview');
    const errorMsg = document.getElementById('errorMsg');
    
    errorMsg.classList.add('d-none');
    
    if (!input.files || input.files.length === 0) {
        preview.style.display = 'none';
        return;
    }

    const file = input.files[0];
    const maxSize = 50 * 1024 * 1024; // 50MB

    // Validar tamanho
    if (file.size > maxSize) {
        showError(`Arquivo muito grande (${(file.size / 1024 / 1024).toFixed(2)}MB). Máximo 50MB.`);
        input.value = '';
        preview.style.display = 'none';
        return;
    }

    // Preview simples
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function showError(msg) {
    const errorMsg = document.getElementById('errorMsg');
    errorMsg.textContent = msg;
    errorMsg.classList.remove('d-none');
}
</script>

<style>
.form-control:focus,
.form-control:disabled {
    border-color: #df6301;
    box-shadow: 0 0 0 0.2rem rgba(223, 99, 1, 0.25);
}

.form-control:disabled {
    background-color: #f8f9fa;
    color: #212529;
}

.btn-primary {
    background-color: #df6301;
    border-color: #df6301;
}

.btn-primary:hover {
    background-color: #b54f01;
    border-color: #b54f01;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(223, 99, 1, 0.3);
}

.card {
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}
</style>
