<!-- CONTAINER PRINCIPAL DO README -->
<div class="container py-4">
    <!-- TÍTULO PRINCIPAL -->
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="text-primary">README – MVC com PHP</h1>
        <p class="lead text-muted">Um guia prático</p>
    </div>

    <!-- SEÇÃO: Descrição -->
    <div class="mb-5">
        <h4>🔎 Descrição</h4>
        <p>
            Este projeto é um sistema MVC (Model-View-Controller) moderno construído em PHP 8.4 com MySQL, Apache e Bootstrap 5.
            Ele foi arquitetado para ser limpo, modular, escalável e seguro desde sua fundação. Utiliza roteamento amigável,
            login com sessão, painel administrativo, testes de conexão com banco e exibição de informações em tempo real
            sobre o ambiente do servidor e ferramentas de desenvolvimento.
        </p>
        <p>
            A estrutura segue boas práticas como:
        <ul>
            <li>✅ Autoload via Composer (PSR-4)</li>
            <li>✅ Separação clara entre camada de lógica e apresentação</li>
            <li>✅ Reutilização de layouts e componentes</li>
            <li>✅ Interface responsiva e 100% funcional em mobile e desktop</li>
        </ul>
        </p>
    </div>


    <!-- SEÇÃO: Fases de Construção -->
    <div class="mb-5">
        <h4>🚀 Fases de Construção</h4>
        <ol>
            <li>Criação da estrutura MVC base (Controllers, Views, Core, Models)</li>
            <li>Configuração do <code>.htaccess</code> para URLs amigáveis com Apache + mod_rewrite</li>
            <li>Implementação do sistema de rotas com fallback inteligente</li>
            <li>Construção da página de login com Bootstrap 5 e validação de sessão</li>
            <li>Dashboard com cards responsivos mostrando informações em tempo real do ambiente</li>
            <li>Criação de modal de teste de conexão com PDO (visual estilo formulário)</li>
            <li>Adição de leitura de ferramentas via shell_exec: PHP, MySQL, Composer, Node.js, npm, Git, GitHub CLI, Xdebug</li>
            <li>Aplicação de segurança no VirtualHost com headers HTTP e SSL</li>
            <li>Documentação interativa via rota pública <code>/readme</code> com explicações de estrutura, composer e Apache</li>
            <li>Padronização visual com Bootstrap 5 (sem jQuery), incluindo menus responsivos e página ativa</li>
        </ol>
    </div>

    <!-- SEÇÃO: Estrutura do Projeto -->
    <div class="mb-5">
        <h4>📁 Estrutura Completa do Projeto</h4>
        <pre class="bg-light p-3 rounded"><code>mvcsite/
    ├── app/
    │   ├── Controllers/
    │   │   ├── AboutController.php
    │   │   ├── AdminController.php
    │   │   ├── ContactController.php
    │   │   ├── HomeController.php
    │   │   └── ReadmeController.php
    │   ├── Core/
    │   │   ├── App.php
    │   │   ├── Controller.php
    │   │   └── Router.php
    │   ├── Models/
    │   │   └── (vazio ou User.php futuramente)
    │   └── Views/
    │       ├── about.php
    │       ├── contact.php
    │       ├── home.php
    │       ├── readme.php
    │       ├── layouts/
    │       │   └── main.php
    │       └── admin/
    │           ├── login.php
    │           └── dashboard.php
    ├── config/
    │   └── config.php
    ├── public/
    │   ├── .htaccess
    │   └── index.php
    ├── composer.json
    ├── composer.lock
    ├── README.md (opcional se quiser em Markdown real)
    └── vendor/
    └── autoload.php e arquivos do Composer
    </code></pre>
    </div>

    <!-- SEÇÃO: composer.json -->
    <div class="mb-5">
        <h4>📦 Configuração do Composer</h4>
        <p>O arquivo <code>composer.json</code> define o autoload do projeto, dependências e scripts de teste
            automatizado.</p>



        <pre class="bg-light p-3 rounded"><code>{
    "name": "luciolemos/mvcsite",
    "description": "Projeto MVC com PHP e MySQL",
    "type": "project",
    "license": "MIT",
    "version": "1.0.0",
    "homepage": "https://github.com/luciolemos/mvcsite",
    "minimum-stability": "stable",
    "authors": [
    {
    "name": "Lucio Flavio Lemos",
    "email": "luciolemos.j5@gmail.com",
    "role": "developer",
    "homepage": "https://luciolemos.com"
    }
    ],
    "autoload": {
    "psr-4": {
    "App\\": "app/"
    }
    },
    "require": {
    "vlucas/phpdotenv": "^5.5"
    },
    "require-dev": {
    "phpunit/phpunit": "^10.0"
    },
    "scripts": {
    "test": "phpunit --coverage-html coverage"
    }
    }</code></pre>

        <h5>📖 Explicação dos campos principais:</h5>
        <ul>
            <li><strong>name:</strong> Identificador do pacote (vendor/projeto)</li>
            <li><strong>description:</strong> Resumo do projeto</li>
            <li><strong>type:</strong> Tipo do projeto (aqui é um projeto PHP completo)</li>
            <li><strong>license:</strong> Licença de código aberto (MIT)</li>
            <li><strong>authors:</strong> Dados do autor (nome, e-mail, site, função)</li>
            <li><strong>autoload:</strong> Define o autoload das classes via <code>PSR-4</code> apontando para a pasta
                <code>app/</code></li>
            <li><strong>require:</strong> Dependências obrigatórias (ex: <code>vlucas/phpdotenv</code> para variáveis de
                ambiente)
            </li>
            <li><strong>require-dev:</strong> Dependências apenas para desenvolvimento (ex: <code>phpunit</code>)</li>
            <li><strong>scripts:</strong> Scripts customizados, como rodar testes com cobertura de código</li>
        </ul>

        <h5>💡 Dica de uso:</h5>
        <p>Após clonar o projeto, rode:</p>
        <div class="position-relative bg-light border rounded p-3 mb-4">
            <button class="btn btn-sm btn-outline-secondary position-absolute top-50 translate-middle-y end-0 me-2"
                    onclick="copiarComandos()">📋
            </button>

            <pre class="mb-0" id="blocoComandos"><code>$ composer install</code></pre>
        </div>
        <!--        <pre class="bg-light p-3 rounded border"><code>$ composer install</code></pre>-->

        <p>Para carregar o autoload:</p>
        <div class="position-relative bg-light border rounded p-3 mb-4">
            <button class="btn btn-sm btn-outline-secondary position-absolute top-50 translate-middle-y end-0 me-2"
                    onclick="copiarComandos()">📋
            </button>
            <pre class="mb-0" id="blocoComandos"><code>$ composer dump-autoload</code></pre>
        </div>
        <!--        <pre class="bg-light p-3 rounded border"><code>$ composer dump-autoload</code></pre>-->
    </div>

    <!-- SEÇÃO: Configuração do Apache -->
    <div class="mb-5">
        <h4>⚙️ Configuração do Apache (VirtualHost)</h4>
        <p>Exemplo de configuração segura e otimizada usada neste projeto:</p>

        <pre class="bg-light border p-3 rounded"><code><?= htmlspecialchars('
    <VirtualHost *:80>
    ServerName srv798468.hstgr.cloud
    ServerAlias www.srv798468.hstgr.cloud
    DocumentRoot /var/www/mvcsite/public
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    </VirtualHost>
    
    <VirtualHost *:443>
    ServerName srv798468.hstgr.cloud
    DocumentRoot /var/www/mvcsite/public
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/srv798468.hstgr.cloud/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/srv798468.hstgr.cloud/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/srv798468.hstgr.cloud/chain.pem
    Include /etc/letsencrypt/options-ssl-apache.conf
    
    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256
    SSLHonorCipherOrder on
    SSLCompression off
    SSLSessionTickets off
    
    <Directory /var/www/mvcsite/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "no-referrer-when-downgrade"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    </Directory>
    
    <Directory /var/www/mvcsite/public/uploads>
    php_admin_flag engine off
    Options -ExecCGI -Indexes
    <FilesMatch "\.(php|php5|php7|phtml)$">
    Require all denied
    </FilesMatch>
    </Directory>
    
    <IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 30 days"
    ExpiresByType image/jpeg "access plus 30 days"
    ExpiresByType image/png "access plus 30 days"
    ExpiresByType text/css "access plus 7 days"
    ExpiresByType application/javascript "access plus 7 days"
    </IfModule>
    </VirtualHost>
    ') ?></code></pre>
    </div>


    <!-- SEÇÃO: Funcionalidades atuais -->
    <div class="mb-5">
        <h4 class="text-primary">⚙️ Funcionalidades Implementadas</h4>
        <ul class="mb-0">
            <li>✅ Autoload de classes com padrão <strong>PSR-4</strong> via <code>Composer</code></li>
            <li>✅ Sistema de <strong>roteamento customizado</strong> com URLs amigáveis e mapeamento dinâmico de controllers</li>
            <li>✅ <strong>Autenticação de usuário</strong> com controle de sessão e proteção de rota administrativa</li>
            <li>✅ <strong>Painel administrativo</strong> com informações dinâmicas do ambiente (PHP, OS, MySQL, Node.js, Composer, etc.)</li>
            <li>✅ <strong>Modal interativo</strong> para testar a conexão com o banco de dados em tempo real (PDO)</li>
            <li>✅ <strong>Formulário de contato</strong> com PHPMailer + SMTP autenticado, <code>Reply-To</code>, reCAPTCHA e validação robusta</li>
            <li>✅ Suporte a variáveis de ambiente via <strong>.env</strong> (com <code>vlucas/phpdotenv</code>)</li>
            <li>✅ <strong>Layout responsivo</strong>, limpo e adaptável com <code>Bootstrap 5</code> e componentes reutilizáveis</li>
            <li>✅ Página <code>README</code> com documentação técnica renderizada diretamente da estrutura real do projeto</li>
            <li>✅ Organização modular com pastas claras: <code>Controllers</code>, <code>Views</code>, <code>Models</code>, <code>Core</code>, <code>config</code></li>
        </ul>
    </div>


    <!-- SEÇÃO: Requisitos -->
    <div class="mb-5">
        <h4>🔐 Requisitos</h4>
        <ul>
            <li>✅ PHP 8.4+</li>
            <li>✅ MySQL</li>
            <li>✅ Apache com <code>mod_rewrite</code></li>
            <li>✅ Composer</li>
        </ul>
    </div>

    <!-- SEÇÃO: Roadmap futuro -->
    <div class="mb-5">
        <h4>🧱 Próximas Etapas / Escalabilidade</h4>
        <ul>
            <li>✅ Login via banco de dados (com hash de senha)</li>
            <li>✅ CRUD de usuários</li>
            <li>✅ Permissões por perfil</li>
            <li>✅ API RESTful (JSON)</li>
            <li>✅ Proteção CSRF</li>
            <li>✅ Gráficos no dashboard (Chart.js)</li>
            <li>✅ Logs de acesso e atividades</li>
        </ul>
    </div>
</div>







