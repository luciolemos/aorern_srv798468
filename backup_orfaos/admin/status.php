<div class="container">
    <!-- 🧭 TÍTULO PRINCIPAL -->
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-search me-2"></i> Painel de Verificação de Integridade</h2>
        <p class="lead text-muted">Bem-vindo, admin! Aqui você gerencia o sistema.</p>
    </div>
    <hr class="my-4">

    <div data-aos="fade-up">
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php
        $itens = [
            'env' => ['label' => '.env carregado', 'icon' => 'bi-file-earmark-lock', 'color' => 'primary'],
            'db' => ['label' => 'Banco de Dados', 'icon' => 'bi-database-check', 'color' => 'success'],
            'smtp' => ['label' => 'SMTP (PHPMailer)', 'icon' => 'bi-envelope-paper', 'color' => 'info'],
            'recaptcha' => ['label' => 'Google reCAPTCHA', 'icon' => 'bi-shield-lock', 'color' => 'warning'],
        ];
        ?>

        <?php foreach ($itens as $key => $info): ?>
            <div class="col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi <?= $info['icon'] ?> fs-2 text-<?= $info['color'] ?> me-3"></i>
                        <div>
                            <h5 class="card-title mb-1"><?= $info['label'] ?></h5>
                            <p class="card-text small">
                                <?= $status[$key] ? '<span class="text-success"><i class="bi bi-check2-square me-2"></i> OK</span>' : '<span class="text-danger">❌ Falhou</span>' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
</div>

