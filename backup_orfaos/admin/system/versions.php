
    <div class="mb-4">
        <h3 class="text-primary">
            <i class="bi bi-terminal me-2"></i> Versões das Ferramentas
        </h3>
        <p class="text-muted">Aqui você verifica as versões das principais ferramentas de desenvolvimento web instaladas no seu servidor.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($versions as $label => $value): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-highlight card-hover shadow-sm h-100">
                    <div class="card-body">
                        <div class="card-title"><?= strtoupper($label) ?></div>
                        <div class="card-value"><?= $value ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>



