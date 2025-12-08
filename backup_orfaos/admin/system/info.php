<div class="mb-4" data-aos="fade-down">
    <h3 class="text-primary">
        <i class="fas fa-server me-2"></i> Informações do Sistema
    </h3>
    <p class="text-muted">Bem-vindo, admin! Aqui você gerencia o sistema.</p>
</div>

<div class="row g-4" data-aos="fade-up">
    <?php
    // Cores e ícones correspondentes para cada tipo de informação
    $cardStyles = [
        'versão' => ['color' => 'primary', 'icon' => 'code-branch'],
        'ambiente' => ['color' => 'info', 'icon' => 'layer-group'],
        'usuários' => ['color' => 'success', 'icon' => 'users'],
        'ultimo backup' => ['color' => 'warning', 'icon' => 'history'],
        'status' => ['color' => 'danger', 'icon' => 'heartbeat'],
        'armazenamento' => ['color' => 'secondary', 'icon' => 'hdd']
    ];

    foreach ($info as $label => $value):
        $style = $cardStyles[strtolower($label)] ?? ['color' => 'primary', 'icon' => 'info-circle'];
        ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-<?= $style['color'] ?>-light">
                            <i class="fas fa-<?= $style['icon'] ?> text-<?= $style['color'] ?>"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0"><?= ucfirst($label) ?></h5>
                    </div>
<!--                    <p class="card-text small text-muted">Informação do sistema</p>-->
                    <div class="d-flex justify-content-between align-items-center mt-auto">
<!--                        <div class="badge bg-<?/*= $style['color'] */?>-light text-<?/*= $style['color'] */?> small">
                            <?/*= strtoupper($style['color']) */?>
                        </div>-->
                        <span class="text-<?= $style['color'] ?>">
                            <?= htmlspecialchars($value) ?>
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="fas fa-sync-alt me-1"></i> Atualizado em <?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    /* Estilos consistentes com a identidade visual */
    .hover-effect {
        transition: all 0.3s ease;
        border-radius: 10px;
    }

    .hover-effect:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
    .bg-secondary-light { background-color: rgba(108, 117, 125, 0.1); }

    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .card-footer {
        background-color: rgba(13, 110, 253, 0.03);
    }
</style>