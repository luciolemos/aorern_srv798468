<?php
/**
 * COMPONENTE: Footer Reutilizável
 * 
 * Uso:
 * <?php 
 *   $footer_type = 'public'; // ou 'admin'
 *   include 'components/footer.php';
 * ?>
 */

$footer_type = $footer_type ?? 'public';
?>

<?php if ($footer_type === 'public'): ?>
    <!-- Footer Público -->
    <footer class="text-light py-5" style="background: linear-gradient(135deg, #df6301 0%, #b54f01 100%);">
        <div class="container">
            <!-- Grid de 3 colunas -->
            <div class="row mb-5">
                <!-- Coluna 1: Sobre -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= BASE_URL ?>assets/images/brasao_cbmrn_oficial.png" alt="Brasão CBMRN" style="height: 2.5rem; width: auto; object-fit: contain;">
                        <h5 class="fw-bold mb-0 ms-3">CBMRN</h5>
                    </div>
                    <p style="color: rgba(255,255,255,0.95); line-height: 1.6;">
                        <strong>2º Subgrupamento de Bombeiros Militar</strong><br>
                        <small>"Salvar ou morrer!"</small>
                    </p>
                    <!-- Ícones sociais com animações -->
                    <div class="mt-3 d-flex gap-2">
                        <a href="#" class="social-icon-footer" data-aos="fade-up" data-aos-delay="100" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-icon-footer" data-aos="fade-up" data-aos-delay="150" title="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="social-icon-footer" data-aos="fade-up" data-aos-delay="200" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="social-icon-footer" data-aos="fade-up" data-aos-delay="250" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>

                <!-- Coluna 2: Links Rápidos -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-3 text-uppercase" style="letter-spacing: 1px;">
                        <i class="bi bi-link-45deg me-2"></i>Links Rápidos
                    </h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?= BASE_URL ?>home" class="text-white text-decoration-none" style="transition: all 0.3s;">
                                <i class="bi bi-house-door me-2"></i>Início
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= BASE_URL ?>about" class="text-white text-decoration-none" style="transition: all 0.3s;">
                                <i class="bi bi-info-circle me-2"></i>Sobre
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= BASE_URL ?>blog" class="text-white text-decoration-none" style="transition: all 0.3s;">
                                <i class="bi bi-journal-text me-2"></i>Blog
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>contact" class="text-white text-decoration-none" style="transition: all 0.3s;">
                                <i class="bi bi-chat-left-text me-2"></i>Contato
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Coluna 3: Contato -->
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3 text-uppercase" style="letter-spacing: 1px;">
                        <i class="bi bi-telephone me-2"></i>Contato
                    </h6>
                    <div style="color: rgba(255,255,255,0.95); line-height: 2;">
                        <small class="d-block">
                            <i class="bi bi-geo-alt me-2"></i>
                            <strong>Endereço:</strong> Natal - RN, Brasil
                        </small>
                        <small class="d-block">
                            <i class="bi bi-telephone me-2"></i>
                            <strong>Telefone:</strong> (84) 3456-7890
                        </small>
                        <small class="d-block">
                            <i class="bi bi-envelope me-2"></i>
                            <strong>Email:</strong> 
                            <a href="mailto:contato@cbmrn.com" class="text-white text-decoration-none fw-bold">contato@cbmrn.com</a>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Divisor -->
            <hr style="background-color: rgba(255,255,255,0.2); opacity: 1; margin: 2rem 0;">

            <!-- Seção inferior: Copyright e Info -->
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <small style="color: rgba(255,255,255,0.9);">
                        &copy; <?= date('Y') ?> <strong>2º Subgrupamento de Bombeiros Militar</strong> - Todos os direitos reservados
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small style="color: rgba(255,255,255,0.85);">
                        Desenvolvido por 
                        <a href="http://lattes.cnpq.br/6156274538172427" class="text-white fw-bold text-decoration-none" style="border-bottom: 1px solid rgba(255,255,255,0.5);" title="Fulll Stack PHP Developer">
                            Lúcio Lemos
                        </a>
                    </small>
                </div>
            </div>

            <!-- Badge de status (opcional) -->
            <div class="text-center mt-4">
                <small style="color: rgba(255,255,255,0.7);">
                    <span class="badge bg-success me-2">
                        <i class="bi bi-check-circle me-1"></i>Sistema Operacional
                    </span>
                </small>
            </div>
        </div>
    </footer>

<?php else: ?>
    <!-- Footer Admin -->
    <footer class="bg-dark text-light py-3 admin-footer">
        <div class="container text-center">
            <p class="mb-0 small">
                &copy; PHP Full-Stack <?= date('Y') ?> —
                <a href="http://lattes.cnpq.br/6156274538172427" class="text-white text-decoration-none">
                    <i>by</i> Lúcio Lemos
                </a>
            </p>
        </div>
    </footer>
<?php endif; ?>
