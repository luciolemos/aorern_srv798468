<!-- app/Views/admin/register.php -->

<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-lg-8 col-xl-7">
        <div class="card shadow-lg p-0 border-0" style="border-top: 5px solid #df6301;">
            <!-- Cabeçalho com cor institucional -->
            <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); color: white;" class="p-4 text-center">
                <h4 class="fw-bold mb-1"><i class="bi bi-person-plus me-2"></i>Criar Conta</h4>
                <p class="small mb-0">Junte-se ao sistema CBMRN</p>
            </div>

            <div class="p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/auth/store" method="post" enctype="multipart/form-data">
                    <?php use App\Helpers\CsrfHelper; echo CsrfHelper::inputField(); ?>
                    
                    <div class="row g-3">
                        <!-- Username (col-6) -->
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-medium">Usuário</label>
                            <input type="text" name="username" id="username" class="form-control" 
                                   placeholder="Nome de usuário" required autofocus
                                   value="<?= htmlspecialchars($_SESSION['old_input']['username'] ?? '') ?>">
                            <small class="form-text text-muted">3 a 50 caracteres</small>
                        </div>

                        <!-- Email (col-6) -->
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium">Email</label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   placeholder="seu@email.com" required
                                   value="<?= htmlspecialchars($_SESSION['old_input']['email'] ?? '') ?>">
                        </div>

                        <!-- Senha (col-6) -->
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-medium">Senha</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" 
                                       placeholder="Mínimo 6 caracteres" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="eye-password"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Letras, números e símbolos</small>
                        </div>

                        <!-- Confirmar Senha (col-6) -->
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-medium">Confirmar</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                       class="form-control" placeholder="Repita a senha" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="eye-password_confirmation"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Avatar (col-12) -->
                        <div class="col-12">
                            <label for="avatar" class="form-label fw-medium">Foto de Perfil (Opcional)</label>
                            <input type="file" name="avatar" id="avatar" class="form-control" 
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="form-text text-muted">JPG, PNG ou WebP (máx 50MB)</small>
                        </div>
                    </div>

                    <!-- Botão -->
                    <div class="d-grid mt-4 mb-3">
                        <button type="submit" class="btn btn-lg fw-semibold" style="background-color: #df6301; color: white; border: none;">
                            <i class="bi bi-check-circle me-1"></i> Criar Conta
                        </button>
                    </div>

                    <!-- Link para login -->
                    <div class="text-center">
                        <p class="mb-0 text-muted">Já possui conta? 
                            <a href="<?= BASE_URL ?>admin/auth" class="fw-medium" style="color: #df6301; text-decoration: none;">Faça login aqui</a>
                        </p>
                        <p class="mt-2 mb-0">
                            <a href="<?= BASE_URL ?>" class="text-decoration-none fw-medium" style="color: #df6301;">
                                &laquo; Voltar ao site
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Rodapé -->
            <div style="background-color: #f8f9fa; border-top: 1px solid #e9ecef;" class="p-3 text-center text-muted small">
                &copy; Corpo de Bombeiros - RN <?= date('Y') ?>
            </div>
        </div>
    </div>
</div>

<style>
.input-group .btn-outline-secondary {
    border-color: #dee2e6;
    color: #666;
}

.input-group .btn-outline-secondary:hover {
    background-color: #df6301;
    border-color: #df6301;
    color: white;
}

.input-group .form-control:focus {
    border-color: #df6301;
    box-shadow: none;
}

.btn-lg:hover {
    background-color: #b54f01 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(223, 99, 1, 0.3);
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('eye-' + fieldId);
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>

<?php
// Limpa old_input após renderizar
if (isset($_SESSION['old_input'])) {
    unset($_SESSION['old_input']);
}
?>
