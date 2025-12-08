<div class="container py-5 min-vh-100 d-flex align-items-center">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg p-0 border-0" style="border-top: 5px solid #df6301;">
                <!-- Cabeçalho com cor institucional -->
                <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); color: white;" class="p-4 text-center">
                    <h4 class="fw-bold mb-1"><i class="bi bi-chat-left-text me-2"></i>Fale Conosco</h4>
                    <p class="small mb-0">Sistema CBMRN</p>
                </div>

                <div class="p-4">
                        <!-- Mensagens de status -->
                        <?php if (!empty($_GET['ok'])): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check2"></i> Mensagem enviada com sucesso! Entraremos em contato em breve.
                            </div>
                        <?php elseif (!empty($_GET['erro']) && $_GET['erro'] === 'recaptcha'): ?>
                            <div class="alert alert-danger">
                                ⚠️ Validação de segurança falhou. Marque o reCAPTCHA corretamente.
                            </div>
                        <?php elseif (!empty($_GET['erro'])): ?>
                            <div class="alert alert-danger">
                                ❌ Ocorreu um erro ao enviar. Tente novamente.
                            </div>
                        <?php endif; ?>

                        <!-- Formulário -->
                        <form method="POST" action="<?= BASE_URL ?>contact/send">
                            <div class="mb-3">
                                <label for="nome" class="form-label fw-medium">Nome completo</label>
                                <input type="text" name="nome" id="nome" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">E-mail</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="mensagem" class="form-label fw-medium">Mensagem</label>
                                <textarea name="mensagem" id="mensagem" rows="1" class="form-control" required></textarea>
                            </div>

                            <!-- reCAPTCHA -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-center">
                                    <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
                                </div>
                            </div>

                            <!-- Botão -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-lg fw-semibold" style="background-color: #df6301; color: white; border: none;">
                                    <i class="bi bi-send me-1"></i> Enviar Mensagem
                                </button>
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
    </div>
</div>

<?php $footer_type = 'public'; include __DIR__ . '/components/footer.php'; ?>

<!-- Google reCAPTCHA Script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
.form-control:focus {
    border-color: #df6301;
    box-shadow: 0 0 0 0.2rem rgba(223, 99, 1, 0.25);
}

.btn-lg:hover {
    background-color: #b54f01 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(223, 99, 1, 0.3);
    transition: all 0.3s ease;
}

.card {
    transition: transform 0.3s ease;
}
</style>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const url = new URL(window.location);
        const paramsToRemove = ['ok', 'erro'];

        let shouldUpdate = false;

        paramsToRemove.forEach(param => {
            if (url.searchParams.has(param)) {
                url.searchParams.delete(param);
                shouldUpdate = true;
            }
        });

        if (shouldUpdate) {
            window.history.replaceState({}, document.title, url.pathname + url.search);
        }
    });
</script>

