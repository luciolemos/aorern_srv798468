<div class="col-sm-6 col-xl-3">
    <div class="card h-100 border-0 shadow-sm hover-effect">
        <div class="card-body d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center mb-3">
                <div class="icon-circle <?= $icon_bg ?>">
                    <i class="<?= $icon ?> <?= $icon_color ?>"></i>
                </div>
                <h5 class="card-title ms-3 mb-0 <?= $title_color ?>"><?= $title ?></h5>
            </div>
            <p class="card-text small text-muted"><?= $description ?></p>

            <?php if (!empty($progress)): ?>
                <div class="progress mt-2 mb-3" style="height: 6px;">
                    <div class="progress-bar <?= $progress_color ?>" role="progressbar" style="width: <?= $progress ?>%;"></div>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mt-auto">
                <span class="badge <?= $badge_class ?> small"><?= $badge_text ?></span>
                <a href="<?= $link ?>" class="btn <?= $btn_class ?> btn-sm stretched-link">
                    <i class="<?= $btn_icon ?> me-1"></i> <?= $btn_label ?>
                </a>
            </div>
        </div>
        <?php if (!empty($footer)): ?>
            <div class="card-footer bg-transparent border-0 small text-muted">
                <?= $footer ?>
            </div>
        <?php endif; ?>
    </div>
</div>

