<!-- app/Views/admin/login.php -->

<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg p-0 border-0" style="border-top: 5px solid #df6301;">
            <!-- Cabeçalho com cor institucional -->
            <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); color: white;" class="p-4 text-center">
                <h4 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2"></i>Bem-vindo</h4>
                <p class="small mb-0">Sistema CBMRN</p>
            </div>

            <div class="p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/auth/login" method="post">
                    <?php use App\Helpers\CsrfHelper; echo CsrfHelper::inputField(); ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label fw-medium">Usuário</label>
                        <input type="text" name="username" id="username" class="form-control" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium">Senha</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                <i class="bi bi-eye" id="eye-password"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-lg fw-semibold" style="background-color: #df6301; color: white; border: none;">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                        </button>
                    </div>

                    <!-- Link para registro -->
                    <div class="text-center">
                        <p class="mb-0 text-muted">Não possui conta? 
                            <a href="<?= BASE_URL ?>admin/auth/register" class="fw-medium" style="color: #df6301; text-decoration: none;">Cadastre-se aqui</a>
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

<!-- Toast Container (Bootstrap) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <?php 
        // Verifica primeiro no cookie (usado após logout)
        $toast = null;
        if (isset($_COOKIE['flash_message'])) {
            $toast = json_decode($_COOKIE['flash_message'], true);
            // Remove o cookie imediatamente
            setcookie('flash_message', '', time() - 3600, '/');
        } 
        // Senão, verifica na sessão (fluxo normal)
        elseif (isset($_SESSION['toast'])) {
            $toast = $_SESSION['toast'];
            unset($_SESSION['toast']);
        }
        
        if ($toast):
            $toastType = $toast['type'] ?? 'info';
            $toastMessage = $toast['message'] ?? '';
    ?>
        <div id="liveToast" class="toast align-items-center text-white bg-<?= $toastType ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($toastMessage) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
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

// Exibe o toast automaticamente se existir
document.addEventListener('DOMContentLoaded', function() {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 5000
        });
        toast.show();
    }
});
</script>
