<div class="container-fluid px-3 px-md-4 py-4">
    <!-- 🧭 TÍTULO PRINCIPAL -->
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-tools me-2"></i>Características Técnicas</h2>
        <p class="lead text-muted">Componentes, padrões e práticas aplicadas neste projeto PHP MVC</p>
    </div>
    <hr class="my-4">

    <!-- 🔍 Descrição Geral -->
    <div class="mb-5" data-aos="fade-up">
        <h4><i class="bi bi-search me-2 text-primary"></i>Descrição Geral</h4>
        <p>
            Sistema PHP 8.4 com padrão <strong>MVC</strong>, banco <strong>MySQL</strong> e servidor <strong>Apache</strong>,
            focado em arquitetura modular, segurança e escalabilidade.
            Recursos como <code>PSR-4</code>, <code>.env</code>, <strong>reCAPTCHA</strong>, <strong>Bootstrap 5</strong>
            e integração com <strong>GitHub</strong> estão presentes, promovendo organização e modernidade.
        </p>
    </div>

    <!-- 🚀 Fases de Construção -->
    <div class="mb-5" data-aos="zoom-in">
        <h4><i class="bi bi-rocket-takeoff-fill me-2 text-danger"></i>Fases de Construção</h4>
        <ol>
            <li><code>MVC:</code> Controllers, Models, Views e núcleo Core</li>
            <li><code>.htaccess</code> e VirtualHost para URLs limpas</li>
            <li>Classe <code>App</code> dinâmica para carregamento de rotas</li>
            <li>Tela de login com Bootstrap 5</li>
            <li>Dashboard com leitura de versões reais</li>
            <li>Teste visual de conexão via PDO</li>
            <li>Formulário com SMTP + reCAPTCHA</li>
            <li>Documentação acessível por rotas públicas</li>
        </ol>
    </div>

    <!-- ✅ Funcionalidades Atuais -->
    <div class="mb-5" data-aos="zoom-in">
        <h4 class="text-primary"><i class="bi bi-check2-circle me-2"></i>Funcionalidades Atuais</h4>
        <ul class="timeline list-unstyled ps-0 mt-3">
            <li><i class="bi bi-lightning-charge text-warning me-2"></i>Autoload PSR-4 com <code>Composer</code></li>
            <li><i class="bi bi-signpost-split text-info me-2"></i>Roteamento sem framework</li>
            <li><i class="bi bi-shield-lock text-success me-2"></i>Login com sessão segura</li>
            <li><i class="bi bi-hdd-network text-secondary me-2"></i>Leitura do servidor em tempo real</li>
            <li><i class="bi bi-terminal text-dark me-2"></i>Modal com teste de conexão</li>
            <li><i class="bi bi-envelope-at text-danger me-2"></i>Contato com PHPMailer e SMTP autenticado</li>
            <li><i class="bi bi-file-lock2 text-warning me-2"></i>Variáveis de ambiente com <code>.env</code></li>
            <li><i class="bi bi-laptop text-primary me-2"></i>Layout responsivo com Bootstrap 5</li>
            <li><i class="bi bi-github text-dark me-2"></i>Integração com GitHub</li>
            <li><i class="bi bi-journal-code text-info me-2"></i>Documentação pública e interativa</li>
        </ul>
    </div>

    <!-- 🔐 Banco de Dados e Configuração -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3"><i class="bi bi-database-lock me-2"></i>Banco de Dados & Configuração</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                <tr>
                    <th class="text-start"><i class="bi bi-gear me-1"></i> Item</th>
                    <th class="text-start"><i class="bi bi-code me-1"></i> Implementação</th>
                    <th><i class="bi bi-check2-all text-success"></i></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-start"><i class="bi bi-file-lock2-fill me-2 text-primary"></i> .env seguro</td>
                    <td class="text-start"><code>Dotenv::createImmutable(...)</code></td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-gear-wide-connected me-2 text-secondary"></i> Host configurável</td>
                    <td class="text-start"><code>$_ENV['DB_HOST']</code></td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-arrow-repeat me-2 text-info"></i> Singleton de conexão</td>
                    <td class="text-start"><code>private static $instance</code></td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i> Erros visíveis</td>
                    <td class="text-start"><code>PDO::ERRMODE_EXCEPTION</code></td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-fonts me-2 text-danger"></i> Charset moderno</td>
                    <td class="text-start"><code>utf8mb4</code></td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-shield-check me-2 text-dark"></i> Proteção reconexão</td>
                    <td class="text-start">Única por ciclo</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 🔌 Integrações -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3"><i class="bi bi-plug me-2"></i>Integrações e Ferramentas</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                <tr>
                    <th class="text-start">Recurso</th>
                    <th class="text-start">Implementação</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-start"><i class="bi bi-envelope-at me-2 text-danger"></i> PHPMailer</td>
                    <td class="text-start">Envio via SMTP autenticado</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-shield-lock me-2 text-primary"></i> SMTP Seguro</td>
                    <td class="text-start">Credenciais via `.env`</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-check2-square me-2 text-info"></i> Validação</td>
                    <td class="text-start">Campos obrigatórios + HTML5 + CAPTCHA</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-shield-check me-2 text-success"></i> Google reCAPTCHA</td>
                    <td class="text-start">Versão v2 da API</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-bug me-2 text-danger"></i> PHPUnit</td>
                    <td class="text-start">Testes automatizados</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Coverage</td>
                    <td class="text-start">Relatórios com Xdebug + PHPUnit</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-box-seam me-2 text-secondary"></i> Composer</td>
                    <td class="text-start">Gerenciador de dependências</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-braces me-2 text-info"></i> Xdebug</td>
                    <td class="text-start">Cobertura de código via terminal</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-cloud-upload me-2 text-dark"></i> Deploy remoto</td>
                    <td class="text-start">Ubuntu Server com Apache + SSL</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-hdd-network me-2 text-secondary"></i> Virtual Host</td>
                    <td class="text-start">HTTPS + Let’s Encrypt</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                <tr>
                    <td class="text-start"><i class="bi bi-lock me-2 text-dark"></i> Sessão Segura</td>
                    <td class="text-start">Controle de acesso com `$_SESSION`</td>
                    <td><i class="bi bi-check-circle-fill text-success"></i></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 🔐 Requisitos -->
    <div class="mb-5" data-aos="zoom-in">
        <h4><i class="bi bi-list-check me-2"></i>Requisitos Técnicos</h4>
        <ul class="timeline list-unstyled ps-0 mt-3">
            <li><i class="bi bi-terminal-dash me-2 text-primary"></i>PHP 8.4 ou superior</li>
            <li><i class="bi bi-database-fill me-2 text-warning"></i>MySQL ou MariaDB</li>
            <li><i class="bi bi-globe2 me-2 text-info"></i>Apache + mod_rewrite</li>
            <li><i class="bi bi-box-seam me-2 text-danger"></i>Composer instalado</li>
            <li><i class="bi bi-plug-fill me-2 text-success"></i>SSH para deploy (opcional)</li>
        </ul>
    </div>

    <!-- 🧱 Roadmap -->
    <div class="mb-5" data-aos="zoom-in">
        <h4><i class="bi bi-kanban me-2"></i>Próximas Etapas</h4>
        <ul class="timeline list-unstyled ps-0 mt-3">
            <li><i class="bi bi-square me-2 text-info"></i>Login via banco e permissões</li>
            <li><i class="bi bi-square me-2 text-info"></i>CRUD de usuários</li>
            <li><i class="bi bi-square me-2 text-info"></i>Migrations</li>
            <li><i class="bi bi-square me-2 text-info"></i>Middleware de autenticação</li>
            <li><i class="bi bi-square me-2 text-info"></i>Dashboard com gráficos Chart.js</li>
            <li><i class="bi bi-square me-2 text-info"></i>API RESTful</li>
            <li><i class="bi bi-square me-2 text-info"></i>Upload de arquivos</li>
            <li><i class="bi bi-square me-2 text-info"></i>Logs e auditoria</li>
            <li><i class="bi bi-square me-2 text-info"></i>CSRF e XSS Protection</li>
            <li><i class="bi bi-square me-2 text-info"></i>Badges dinâmicos</li>
            <li><i class="bi bi-square me-2 text-info"></i>Paginação de resultados no datatable dos posts</li>
            <li><i class="bi bi-square me-2 text-info"></i>Implementar uma versão com menu recolhível no desktop (menu lateral colapsável) mantendo compatibilidade mobile com offcanvas!</li>
            <li><i class="bi bi-square me-2 text-info"></i>Aprimorar os alertas do formulário de contato.</li>
        </ul>
    </div>
</div>

