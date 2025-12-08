<!-- app/Views/admin/dashboard.php -->
<div class="container">
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-speedometer2 me-2"></i> Painel Administrativo</h2>
        <p class="lead text-muted">Bem-vindo, admin! Aqui você gerencia o sistema.</p>
    </div>
    <hr class="my-4">

    <div class="row g-4" data-aos="fade-up">
        <!-- POSTS -->
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-primary-light">
                            <i class="fas fa-newspaper text-primary"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Posts</h5>
                    </div>
                    <p class="card-text small text-muted">Gerencie os artigos do seu blog.</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-primary-light text-primary small">Blog</div>
                        <a href="<?= BASE_URL ?>admin/posts" class="btn btn-primary btn-sm stretched-link">
                            <i class="fas fa-edit me-1"></i> Gerenciar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="fas fa-lightbulb me-1"></i> Última postagem: <?= date('d/m/Y') ?>
                </div>
            </div>
        </div>

        <!-- STATUS -->
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="fas fa-server text-info"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Status do Sistema</h5>
                    </div>
                    <p class="card-text small text-muted">Verifique ambiente, conexões e versões.</p>
                    <div class="progress mt-2 mb-3" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 85%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-info text-white"><?= phpversion() ?></span>
                        <a href="<?= BASE_URL ?>admin/status" class="btn btn-info btn-sm stretched-link">
                            <i class="fas fa-chart-bar me-1"></i> Detalhes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- TESTES -->
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success-light">
                            <i class="fas fa-vial text-success"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Testes</h5>
                    </div>
                    <p class="card-text small text-muted">Relatórios PHPUnit com cobertura.</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-success-light text-success small">PHPUnit</div>
                        <a href="<?= BASE_URL ?>coverage/html/index.html" target="_blank" class="btn btn-success btn-sm stretched-link">
                            <i class="fas fa-chart-pie me-1"></i> Relatórios
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="fas fa-shield-alt me-1"></i> Cobertura: 92%
                </div>
            </div>
        </div>
    </div>
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

    .bg-primary-light {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .bg-info-light {
        background-color: rgba(13, 202, 240, 0.1);
    }

    .bg-success-light {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .stretched-link::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1;
        content: "";
    }

    /* Destaque para o tema administrativo */
    .card-footer {
        background-color: rgba(13, 110, 253, 0.03);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Efeito de clique em todo o card
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('btn') && !e.target.closest('.btn')) {
                    const link = this.querySelector('a.stretched-link');
                    if (link) {
                        if (link.target === '_blank') {
                            window.open(link.href, '_blank');
                        } else {
                            window.location.href = link.href;
                        }
                    }
                }
            });
        });

        // Animação do progresso do status
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            let width = 0;
            const targetWidth = parseInt(progressBar.style.width);
            const interval = setInterval(() => {
                if (width >= targetWidth) {
                    clearInterval(interval);
                } else {
                    width++;
                    progressBar.style.width = width + '%';
                }
            }, 20);
        }
    });
</script>