<?php
$title = $data['title'] ?? 'Alterar Senha';
?>

<div class="container-fluid p-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 fw-bold mb-1" style="color: #212529;">
                    <i class="bi bi-key me-2" style="color: #df6301;"></i><?= htmlspecialchars($title) ?>
                </h1>
                <p class="text-muted mb-0">Altere sua senha para manter sua conta segura</p>
            </div>

            <!-- Card de Alteração de Senha -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>admin/alterar-senha/update" method="POST">
                        <?php echo \App\Helpers\CsrfHelper::inputField(); ?>
                        
                        <!-- Senha Atual -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-bold">
                                <i class="bi bi-lock me-2" style="color: #df6301;"></i>Senha Atual
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="current_password" 
                                       name="current_password" placeholder="Digite sua senha atual" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="bi bi-eye" id="eye-current_password"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Precisamos verificar sua senha atual para segurança
                            </small>
                        </div>

                        <!-- Divider -->
                        <div class="my-4">
                            <hr style="border-top: 2px dashed #df6301;">
                        </div>

                        <!-- Nova Senha -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">
                                <i class="bi bi-shield-check me-2" style="color: #df6301;"></i>Nova Senha
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="password" 
                                       name="password" placeholder="Digite uma nova senha" minlength="6" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="eye-password"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Mínimo 6 caracteres
                            </small>
                        </div>

                        <!-- Confirmar Nova Senha -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold">
                                <i class="bi bi-check-circle me-2" style="color: #df6301;"></i>Confirmar Nova Senha
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="password_confirmation" 
                                       name="password_confirmation" placeholder="Confirme a nova senha" minlength="6" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="eye-password_confirmation"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Deve ser igual à nova senha acima
                            </small>
                        </div>

                        <!-- Dicas de Segurança -->
                        <div class="alert alert-info border-0" style="background-color: #e7f3ff;">
                            <h6 class="fw-bold mb-2" style="color: #0066cc;">
                                <i class="bi bi-info-circle me-2"></i>Dicas para uma Senha Forte:
                            </h6>
                            <ul class="mb-0 small">
                                <li>Use letras maiúsculas, minúsculas e números</li>
                                <li>Adicione caracteres especiais (!@#$%)</li>
                                <li>Evite datas de nascimento ou informações pessoais</li>
                                <li>Não reutilize senhas antigas</li>
                            </ul>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-lg flex-grow-1" 
                                    style="background-color: #df6301; color: white; border: none;">
                                <i class="bi bi-check-circle me-2"></i>Alterar Senha
                            </button>
                            <a href="<?= BASE_URL ?>admin/perfil" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Aviso de Segurança -->
            <div class="alert alert-warning mt-4 border-0" style="background-color: #fff3cd; color: #664d03;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Aviso:</strong> Após alterar a senha, você será desconectado e precisará fazer login novamente.
            </div>
        </div>
    </div>
</div>

<style>
.form-control-lg {
    border-color: #df6301;
    border-radius: 0.375rem;
}

.form-control-lg:focus {
    border-color: #df6301;
    box-shadow: 0 0 0 0.2rem rgba(223, 99, 1, 0.25);
}

.form-control-lg::placeholder {
    color: #999;
}

.input-group .btn-outline-secondary {
    border-color: #df6301;
    color: #df6301;
}

.input-group .btn-outline-secondary:hover {
    background-color: #df6301;
    border-color: #df6301;
    color: white;
}

.btn-lg {
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.btn-lg:hover:not(:disabled) {
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
