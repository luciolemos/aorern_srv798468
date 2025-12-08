<!-- app/Views/admin/dashboard.php -->
<div class="container">
    <div data-aos="fade-down">
        <h2 class="text-dark"><i class="bi bi-speedometer2 me-1"></i> Painel Admin</h2>
        <p class="lead text-muted">Gerencie todos os recursos da sua obra</p>
    </div>
    <hr class="my-4">

<!-- 🔹 SEÇÃO 1: PESSOAL -->
    <h4 class="text-success mb-3"><i class="bi bi-people-fill me-2"></i> Gestão de Pessoal</h4>
    <div class="row g-4 mb-5" data-aos="fade-up">
        <!-- CARD CADASTRAR FUNÇÃO NA EMPRESA/OBRA-->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="bi bi-person text-success"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0 text-success">Cadastrar função</h5>
                    </div>
                    <p class="card-text small text-muted">Cadastre funções na sua obra</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-info-light text-success small">Essencial</div>
                        <a href="<?= BASE_URL ?>admin/funcoes/cadastrar" class="btn btn-success btn-sm stretched-link">
                            <i class="bi bi-plus-circle me-1"></i> Cadastrar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="bi bi-person-plus me-1"></i> Adicione funções
                </div>
            </div>
        </div>

        <!-- CARD LISTAR FUNÇÕES EXISTENTES NA BASE DADSOS -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="bi bi-person text-success"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0 text-success">Listar funções</h5>
                    </div>
                    <p class="card-text small text-muted">Consulte e gerencie as funções na sua empresa</p>
                    <div class="progress mt-2 mb-3" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-info-light text-success small"><?= $total_pessoal ?? '0' ?> Cadastradas</span>
                        <a href="<?= BASE_URL ?>admin/funcoes" class="btn btn-success btn-sm stretched-link">
                            <i class="bi bi-eye me-1"></i> Visualizar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD CADASTRO DE PESSOAL -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="bi bi-person text-success"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0 text-success">Cadastrar bombeiros</h5>
                    </div>
                    <p class="card-text small text-muted">Cadastre novos bombeiros ou equipes para sua obra</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-info-light text-success small">Essencial</div>
                        <a href="<?= BASE_URL ?>admin/pessoal/cadastrar" class="btn btn-success btn-sm stretched-link">
                            <i class="bi bi-plus-circle me-1"></i> Cadastrar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="bi bi-person-plus me-1"></i> Adicione sua equipe
                </div>
            </div>
        </div>

        <!-- CARD LISTAR PESSOAL -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="bi bi-person text-success"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0 text-success">Listar bombeiros</h5>
                    </div>
                    <p class="card-text small text-muted">Consulte e gerencie todos os colaboradores da obra</p>
                    <div class="progress mt-2 mb-3" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-info-light text-success small"><?= $total_pessoal ?? '0' ?> Bombeiros</span>
                        <a href="<?= BASE_URL ?>admin/pessoal" class="btn btn-success btn-sm stretched-link">
                            <i class="bi bi-eye me-1"></i> Visualizar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD CADASTRO OBRA -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="bi bi-person text-success"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0 text-success">Cadastrar obra</h5>
                    </div>
                    <p class="card-text small text-muted">Cadastre novas funções para o seu pessoal</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="badge bg-success text-white">5 frentes</span>
                        <a href="<?= BASE_URL ?>admin/opus/cad_opus" class="btn btn-danger btn-sm stretched-link">
                            <i class="fas fa-project-diagram me-1"></i> Cadastro
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD CADASTRO DE BOMBEIRO -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="bi bi-person text-success"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0 text-success">Cadastrar bombeiro</h5>
                    </div>
                    <p class="card-text small text-muted">Cadastre novos bombeiros ou equipes para sua obra</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-danger-light text-success small">Essencial</div>
                        <a href="<?= BASE_URL ?>admin/opus/cad_user" class="btn btn-danger btn-sm stretched-link">
                            <i class="bi bi-plus-circle me-1"></i> Cadastrar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="bi bi-person-plus me-1"></i> Adicione sua equipe
                </div>
            </div>
        </div>


    </div>

<!-- 🔹 SEÇÃO 2: EQUIPAMENTOS -->
    <h4 class="text-dark mb-3"><i class="bi bi-tools me-2"></i> Equipamentos</h4>
    <div class="row g-4 mb-5" data-aos="fade-up">
        <!-- CADASTRAR CATEGORIAS DE FERRAMENTAS -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-primary-light">
                            <i class="bi bi-gear"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Cadastrar categoria</h5>
                    </div>
                    <p class="card-text small text-muted">Gerencie as categorias de equipamentos.</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-primary-light text-primary small">Novo</div>
                        <a href="<?= BASE_URL ?>admin/funcoes/cadastrar" class="btn btn-primary btn-sm stretched-link">
                            <i class="bi bi-plus-circle me-1"></i> Cadastrar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="bi bi-clock me-1"></i> Atualizado recentemente
                </div>
            </div>
        </div>

        <!-- CADASTRAR FERRAMENTAS/EQUIPAMENTOS -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success-light">
                            <i class="bi bi-tools"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Cadastrar equipamento</h5>
                    </div>
                    <p class="card-text small text-muted">Adicione novos equipamentos ao sistema.</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-success-light text-success small">Popular</div>
                        <a href="<?= BASE_URL ?>admin/equipamentos/cadastrar" class="btn btn-success btn-sm stretched-link">
                            <i class="bi bi-plus-circle me-1"></i> Cadastrar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="bi bi-people me-1"></i> 15 cadastros esta semana
                </div>
            </div>
        </div>

        <!-- LISTAR FERRAMENTAS CADASTRADAS -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="bi bi-list-columns-reverse me-1"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Listar equipamentos</h5>
                    </div>
                    <p class="card-text small text-muted">Relatórios de equipamentos cadastrados.</p>
                    <div class="progress mt-2 mb-3" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 78%;"></div>
                    </div>
                    <a href="<?= BASE_URL ?>admin/equipamentos" class="btn btn-info btn-sm stretched-link">
                        <i class="bi bi-eye me-1"></i> Visualizar
                    </a>
                </div>
            </div>
        </div>

        <!-- LISTAR CATEGORIAS CADASTRADAS -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-warning-light">
                            <i class="bi bi-list-columns-reverse me-1"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Listar categorias</h5>
                    </div>
                    <p class="card-text small text-muted">Lista das categorias de equipamentos.</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="badge bg-warning text-dark">12 categorias</span>
                        <a href="<?= BASE_URL ?>admin/opus/lista_cat_eqp" class="btn btn-warning btn-sm stretched-link">
                            <i class="bi bi-eye me-1"></i> Visualizar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- 🔹 SEÇÃO 3: OBRAS -->
    <h4 class="text-success mb-3"><i class="bi bi-building-fill me-2"></i> Obras e Serviços</h4>
    <div class="row g-4 mb-5" data-aos="fade-up">
        <!-- CADASTRAR OBRA SERVIÇO -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success-light">
                            <i class="bi bi-house-add"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Cadastrar obra</h5>
                    </div>
                    <p class="card-text small text-muted">Cadastre sua obra ou serviço de engenharia</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-success-light text-success small">Essencial</div>
                        <a href="<?= BASE_URL ?>admin/opus/cad_opus" class="btn btn-success btn-sm stretched-link">
                            <i class="bi bi-plus-circle me-1"></i> Cadastrar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="bi bi-houses me-1"></i> Cadastre obras e serviços
                </div>
            </div>
        </div>

        <!-- CADASTRAR OBRA SERVIÇO -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success-light">
                            <i class="bi bi-house-add"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Cadastrar obra</h5>
                    </div>
                    <p class="card-text small text-muted">Cadastre sua obra ou serviço de engenharia</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="badge bg-success-light text-success small">Essencial</div>
                        <a href="<?= BASE_URL ?>admin/obras/cadastrar" class="btn btn-success btn-sm stretched-link">
                            <i class="bi bi-plus-circle me-1"></i> Cadastrar
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 small text-muted">
                    <i class="bi bi-houses me-1"></i> Cadastre obras e serviços
                </div>
            </div>
        </div>

        <!-- LISTAR OBRAS OU SERVIÇOS EM ANDAMENTO -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-warning-light">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Em andamento</h5>
                    </div>
                    <p class="card-text small text-muted">Obras e/ou serviços de engenharia em execução</p>
                    <div class="progress mt-2 mb-3" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 65%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-warning text-dark">3 obras ativas</span>
                        <a href="<?= BASE_URL ?>admin/opus/obras_andamento" class="btn btn-warning btn-sm stretched-link">
                            <i class="bi bi-eye me-1"></i> Visualizar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD EXTRA (DESCOMENTE SE NECESSÁRIO) -->
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-info-light">
                            <i class="fas fa-tools text-info"></i>
                        </div>
                        <h5 class="card-title ms-3 mb-0">Ferramentas</h5>
                    </div>
                    <p class="card-text small text-muted">Alocação de equipamentos por obra</p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="badge bg-info text-white">12 alocações</span>
                        <a href="<?= BASE_URL ?>admin/opus/lista_eqp" class="btn btn-info btn-sm stretched-link">
                            <i class="fas fa-list me-1"></i> Gerenciar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Efeitos de hover e animações */
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

    .bg-success-light {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-info-light {
        background-color: rgba(13, 202, 240, 0.1);
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
</style>

<script>
    // Adicione interatividade adicional com JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');

        cards.forEach(card => {
            // Efeito ao passar o mouse
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
            });

            // Efeito de clique
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('btn')) {
                    const link = this.querySelector('a.stretched-link');
                    if (link) link.click();
                }
            });
        });

        // Adicione contadores animados (exemplo para o card de categorias)
        const categoryBadge = document.querySelector('.bg-warning.text-dark');
        if (categoryBadge) {
            animateValue(categoryBadge, 0, 12, 1000);
        }

        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.textContent = value + ' categorias';
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
    });
</script>
