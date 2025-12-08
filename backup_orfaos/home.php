<div class="container-fluid px-0 py-0" style="margin-top: -0.5rem;">
    <!-- 🎯 HERO SECTION - PROPOSTA 3: FULL-WIDTH HERO COM CONTEÚDO INFERIOR -->
    <section class="hero-fullwidth" style="position: relative; min-height: 650px; width: 100%; overflow: hidden;">
        <!-- Imagem de fundo -->
        <div class="hero-bg" style="position: absolute; inset: 0; width: 100%; height: 100%; background: url('<?= BASE_URL ?>assets/images/malinois_1600_1600.jpg') center/cover no-repeat; z-index: 1;"></div>
        <!-- Overlay escuro -->
        <div class="hero-overlay" style="position: absolute; inset: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.3) 45%, rgba(223,99,1,0.15) 100%); z-index: 2;"></div>
        <!-- Conteúdo centralizado na parte inferior -->
        <div class="hero-content-bottom" style="position: relative; z-index: 3; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; min-height: 550px; width: 100%; padding: 0 2rem;">
            <div style="margin-bottom: 3.5rem; text-align: center; max-width: 900px;">
                <div class="mb-4" style="animation: float 3s ease-in-out infinite; animation-delay: 0.3s;">
                    <img src="<?= BASE_URL ?>assets/images/brasao_cbmrn_fenix.png" alt="Brasão CBMRN" style="height: 100px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));">
                </div>
                <h1 class="fw-bold text-white mb-2" style="font-size: clamp(2.2rem, 5vw, 3.2rem); line-height: 1.15; letter-spacing: -0.5px;">CBMRN</h1>
                <h3 class="fw-bold text-white mb-2" style="font-size: clamp(1.2rem, 5vw, 3.2rem); line-height: 1.15; letter-spacing: -0.5px;">2º Subgrupamento de Bombeiros Militar</h3>
                <!-- <h2 class="fw-light text-white mb-3" style="font-size: 1.5rem; opacity: 0.95; border-left: 4px solid rgba(255,255,255,0.8); padding-left: 1.2rem; display: inline-block;">Rio Grande do Norte</h2> -->
                <p class="text-white mb-4" style="font-size: 1.1rem; line-height: 1.7; max-width: 600px; opacity: 0.92; margin: 0 auto;"><i>"Alienam vitam et bona salvare"</i></p>
                <!-- Cards flutuantes -->
<!--                 <div class="row g-3 justify-content-center" style="max-width: 600px; margin: 0 auto;">
                    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="stat-card" style="background: rgba(255,255,255,0.95); padding: clamp(0.8rem,2vw,1.5rem); border-radius: clamp(0.6rem,1.5vw,1rem); box-shadow: 0 8px 20px rgba(0,0,0,0.3); text-align: center; border-top: 4px solid #df6301; animation: float 3s ease-in-out infinite; animation-delay: 0s; min-height: fit-content; display: flex; flex-direction: column; justify-content: center;">
                            <div style="font-size: clamp(1.8rem,4vw,2.5rem); font-weight: bold; color: #df6301; line-height: 1; margin-bottom: 0.5rem;"><?= $totalProfissionais ?>+</div>
                            <small style="color: #555; font-weight: 600; font-size: clamp(0.7rem,1.5vw,0.875rem); word-wrap: break-word;">Profissionais</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="stat-card" style="background: rgba(255,255,255,0.95); padding: clamp(0.8rem,2vw,1.5rem); border-radius: clamp(0.6rem,1.5vw,1rem); box-shadow: 0 8px 20px rgba(0,0,0,0.3); text-align: center; border-top: 4px solid #df6301; animation: float 3s ease-in-out infinite; animation-delay: 0.5s; min-height: fit-content; display: flex; flex-direction: column; justify-content: center;">
                            <div style="font-size: clamp(1.8rem,4vw,2.5rem); font-weight: bold; color: #df6301; line-height: 1; margin-bottom: 0.5rem;">24/7</div>
                            <small style="color: #555; font-weight: 600; font-size: clamp(0.7rem,1.5vw,0.875rem); word-wrap: break-word;">Disponível</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="stat-card" style="background: rgba(255,255,255,0.95); padding: clamp(0.8rem,2vw,1.5rem); border-radius: clamp(0.6rem,1.5vw,1rem); box-shadow: 0 8px 20px rgba(0,0,0,0.3); text-align: center; border-top: 4px solid #df6301; animation: float 3s ease-in-out infinite; animation-delay: 1.0s; min-height: fit-content; display: flex; flex-direction: column; justify-content: center;">
                            <div style="font-size: clamp(1.8rem,4vw,2.5rem); font-weight: bold; color: #df6301; line-height: 1; margin-bottom: 0.5rem;">100%</div>
                            <small style="color: #555; font-weight: 600; font-size: clamp(0.7rem,1.5vw,0.875rem); word-wrap: break-word;">Comprometidos</small>
                        </div>
                    </div>
                </div> -->
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-sm fw-bold">
                    <i class="bi bi-envelope me-2"></i>Envie uma Mensagem
                </a>
            </div>
            </div>
        </div>
    </section>

    <!-- Animações CSS -->
    <style>
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        /* Responsividade Hero Split Cards */
        @media (max-width: 991.98px) {
            .hero-split-cards {
                min-height: auto !important;
                flex-direction: column !important;
                background: url('assets/images/malinois11_900_900.jpg') center/cover no-repeat;
                background-attachment: scroll;
            }

            .hero-left-content {
                flex: none !important;
                width: 100% !important;
                min-height: 650px;
                background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(223, 99, 1, 0.45) 100%) !important;
            }
        }

        @media (max-width: 767.98px) {
            .hero-left-content {
                padding: 3rem 2rem !important;
                min-height: 550px;
            }
            
            .hero-fullwidth {
                margin: 0 !important;
                border-radius: 0 !important;
            }
                        .cta-actions a {
                            width: 100%;
                        }
        }
    </style>

    <!-- SEÇÃO PRINCIPAL COM ESTATÍSTICAS -->
    <section class="py-5 bg-light" style="margin-top: 3rem;">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-down">
                <div class="col">
                    <h2 class="fw-bold mb-3" style="color: #df6301;">Números que falam</h2>
                    <p class="text-muted fs-5">Nosso trabalho em números</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="text-center p-4 bg-white rounded-4 shadow-sm h-100" style="border-top: 5px solid #df6301;">
                        <div style="font-size: 3rem; font-weight: bold; color: #df6301;"><?= $totalProfissionais ?></div>
                        <p class="text-muted fw-bold mt-2">Profissionais capacitados</p>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="150">
                    <div class="text-center p-4 bg-white rounded-4 shadow-sm h-100" style="border-top: 5px solid #df6301;">
                        <div style="font-size: 3rem; font-weight: bold; color: #df6301;">24/7</div>
                        <p class="text-muted fw-bold mt-2">Disponíveis</p>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="250">
                    <div class="text-center p-4 bg-white rounded-4 shadow-sm h-100" style="border-top: 5px solid #df6301;">
                        <div style="font-size: 3rem; font-weight: bold; color: #df6301;">100%</div>
                        <p class="text-muted fw-bold mt-2">Comprometidos</p>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="text-center p-4 bg-white rounded-4 shadow-sm h-100" style="border-top: 5px solid #df6301;">
                        <div style="font-size: 3rem; font-weight: bold; color: #df6301;">50+</div>
                        <p class="text-muted fw-bold mt-2">Ocorrências atendidas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SEÇÃO DE SERVIÇOS -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-down">
                <div class="col">
                    <h2 class="fw-bold mb-3" style="color: #212529;">Nossos Serviços</h2>
                    <p class="text-muted fs-5">Proteção e segurança em todas as situações</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: all 0.3s;">
                        <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-fire" style="font-size: 3rem; color: white;"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Combate a Incêndios</h5>
                            <p class="card-text text-muted">
                                Resposta rápida e eficiente no combate a incêndios em estruturas, veículos e áreas florestais.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: all 0.3s;">
                        <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-ambulance" style="font-size: 3rem; color: white;"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Resgate de Emergência</h5>
                            <p class="card-text text-muted">
                                Operações de resgate em altura, confinamento, acidentes de trânsito e situações críticas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: all 0.3s;">
                        <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-shield-exclamation" style="font-size: 3rem; color: white;"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Prevenção e Educação</h5>
                            <p class="card-text text-muted">
                                Programas educativos, inspeções de segurança e capacitação comunitária.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: all 0.3s;">
                        <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-droplet" style="font-size: 3rem; color: white;"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Controle de Sinistros</h5>
                            <p class="card-text text-muted">
                                Atuação em emergências com produtos perigosos e vazamentos de substâncias tóxicas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: all 0.3s;">
                        <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-search" style="font-size: 3rem; color: white;"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Busca e Localização</h5>
                            <p class="card-text text-muted">
                                Operações de busca e localização de pessoas desaparecidas em áreas urbanas e rurais.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: all 0.3s;">
                        <div style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-heart-pulse" style="font-size: 3rem; color: white;"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Atendimento Pré-Hospitalar</h5>
                            <p class="card-text text-muted">
                                Primeiro atendimento médico de emergência com profissionais altamente treinados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SEÇÃO DE VALORES -->
    <section class="py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <div class="container">
            <div class="row text-center mb-5" data-aos="fade-down">
                <div class="col">
                    <h2 class="fw-bold mb-3" style="color: #212529;">Nossos Valores</h2>
                    <p class="text-muted fs-5">Princípios que guiam nossa atuação</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-3" data-aos="flip-left">
                    <div class="text-center" style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-left: 5px solid #df6301;">
                        <i class="bi bi-heart-fill" style="font-size: 2.5rem; color: #df6301; margin-bottom: 1rem;"></i>
                        <h5 class="fw-bold">Comprometimento</h5>
                        <p class="text-muted small">Dedicação total ao bem estar e segurança da comunidade</p>
                    </div>
                </div>

                <div class="col-md-3" data-aos="flip-left" data-aos-delay="100">
                    <div class="text-center" style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-left: 5px solid #df6301;">
                        <i class="bi bi-shield-check" style="font-size: 2.5rem; color: #df6301; margin-bottom: 1rem;"></i>
                        <h5 class="fw-bold">Profissionalismo</h5>
                        <p class="text-muted small">Excelência técnica e ética em todas as operações</p>
                    </div>
                </div>

                <div class="col-md-3" data-aos="flip-left" data-aos-delay="200">
                    <div class="text-center" style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-left: 5px solid #df6301;">
                        <i class="bi bi-people-fill" style="font-size: 2.5rem; color: #df6301; margin-bottom: 1rem;"></i>
                        <h5 class="fw-bold">Fraternidade</h5>
                        <p class="text-muted small">Trabalho em equipe e solidariedade constante</p>
                    </div>
                </div>

                <div class="col-md-3" data-aos="flip-left" data-aos-delay="300">
                    <div class="text-center" style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-left: 5px solid #df6301;">
                        <i class="bi bi-lightning-fill" style="font-size: 2.5rem; color: #df6301; margin-bottom: 1rem;"></i>
                        <h5 class="fw-bold">Agilidade</h5>
                        <p class="text-muted small">Respostas rápidas e eficazes em emergências</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SEÇÃO DE CTA (CALL TO ACTION) -->
    <section class="py-5" style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%); color: white;">
        <div class="container text-center" data-aos="zoom-in">
            <h2 class="fw-bold mb-4 display-5">Precisa de Ajuda?</h2>
            <p class="fs-5 mb-4">O 2º Subgrupamento de Bombeiros Militar está pronto para atendê-lo. Entre em contato!</p>
            <div class="d-flex flex-wrap justify-content-center gap-3 cta-actions">
                <a href="tel:193" class="btn btn-light btn-sm fw-bold">
                    <i class="bi bi-telephone me-2"></i>Ligue 193
                </a>
                <a href="<?= BASE_URL ?>contact" class="btn btn-outline-light btn-sm fw-bold">
                    <i class="bi bi-envelope me-2"></i>Envie uma Mensagem
                </a>
            </div>
        </div>
    </section>

</div>
