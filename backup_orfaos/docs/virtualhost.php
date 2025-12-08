<div class="container py-5">

    <!-- TÍTULO PRINCIPAL -->
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="text-primary">
            <i class="bi bi-server me-2"></i>Virtual Host (Apache)
        </h1>
        <p class="lead text-muted">
            Configure múltiplos sites com segurança usando Apache, Let's Encrypt e práticas modernas.
        </p>
    </div>

    <!-- O QUE É APACHE -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary">
            <i class="bi bi-book me-2"></i>O que é Apache?
        </h4>
        <p>
            O <strong>Apache HTTP Server</strong> é um dos servidores web mais usados no mundo.
            Ele responde requisições HTTP, serve arquivos estáticos e executa scripts PHP via módulos.
        </p>
        <p>
            Em ambientes Linux, é altamente configurável e permite usar <strong>Virtual Hosts</strong>
            para hospedar múltiplos sites no mesmo servidor.
        </p>
    </div>

    <!-- O QUE É VIRTUAL HOST -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary">
            <i class="bi bi-diagram-3 me-2"></i>O que é um Virtual Host?
        </h4>
        <p>
            Um <strong>VirtualHost</strong> permite hospedar vários sites em um único servidor, com domínios e pastas distintas.
        </p>
        <ul class="mb-0">
            <li><i class="bi bi-circle-fill text-secondary me-1"></i><strong>Porta 80:</strong> tráfego HTTP</li>
            <li><i class="bi bi-circle-fill text-secondary me-1"></i><strong>Porta 443:</strong> tráfego HTTPS (SSL)</li>
            <li><i class="bi bi-folder-fill text-secondary me-1"></i><strong>DocumentRoot:</strong> pasta pública do projeto</li>
            <li><i class="bi bi-shield-lock-fill text-secondary me-1"></i><strong>SSL + Headers:</strong> segurança reforçada</li>
        </ul>
    </div>

    <!-- BLOCO DE CONFIGURAÇÃO -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary">
            <i class="bi bi-terminal me-2"></i>Arquivo <code>mvc.conf</code> (Virtual Host Apache)
        </h4>

        <div class="position-relative bg-dark text-light rounded p-4 shadow-sm border border-primary">
            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2"
                    onclick="copiarVirtualHost()">
                <i class="bi bi-clipboard me-1"></i>Copiar
            </button>
            <pre id="blocoVhost" class="small"><code>
&lt;VirtualHost *:80&gt;
    ServerName srv798468.hstgr.cloud
    ServerAlias www.srv798468.hstgr.cloud
    DocumentRoot /var/www/mvc/public

    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/\.well-known/
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
&lt;/VirtualHost&gt;

&lt;VirtualHost *:443&gt;
    ServerName srv798468.hstgr.cloud
    ServerAlias www.srv798468.hstgr.cloud
    DocumentRoot /var/www/mvc/public

    SSLEngine on
    SSLCertificateFile      /etc/letsencrypt/live/srv798468.hstgr.cloud/fullchain.pem
    SSLCertificateKeyFile   /etc/letsencrypt/live/srv798468.hstgr.cloud/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/srv798468.hstgr.cloud/chain.pem
    Include /etc/letsencrypt/options-ssl-apache.conf

    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on
    SSLCompression off
    SSLSessionTickets off

    &lt;Directory /var/www/mvc/public&gt;
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set Referrer-Policy "no-referrer-when-downgrade"
        Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
        Header always set X-XSS-Protection "1; mode=block"
    &lt;/Directory&gt;
&lt;/VirtualHost&gt;
            </code></pre>
        </div>
    </div>

    <!-- EXPLICAÇÃO -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary">
            <i class="bi bi-search me-2"></i>Explicando os principais blocos
        </h4>
        <ul>
            <li><code>ServerName / ServerAlias</code> – Define o domínio principal e alias</li>
            <li><code>DocumentRoot</code> – Pasta pública do projeto (geralmente <code>/public</code>)</li>
            <li><code>RewriteRule</code> – Redirecionamento automático de HTTP para HTTPS</li>
            <li><code>SSLCertificate*</code> – Caminhos dos certificados SSL Let's Encrypt</li>
            <li><code>Header always set ...</code> – Proteções contra XSS, clickjacking, spoofing</li>
            <li><code>mod_expires</code> – Cache otimizado para arquivos estáticos</li>
            <li><code>php_admin_flag engine off</code> – Bloqueia execução de scripts em uploads</li>
        </ul>
    </div>

    <!-- LINKS ÚTEIS -->
    <div class="mb-5" data-aos="fade-up">
        <h5 class="text-primary">
            <i class="bi bi-link-45deg me-2"></i>Links Úteis
        </h5>
        <ul class="list-unstyled">
            <li><a href="https://httpd.apache.org/docs/" target="_blank"><i class="bi bi-link me-1"></i>Documentação Oficial do Apache</a></li>
            <li><a href="https://letsencrypt.org/" target="_blank"><i class="bi bi-link me-1"></i>Let's Encrypt – Certificados SSL Gratuitos</a></li>
            <li><a href="https://www.digitalocean.com/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-22-04" target="_blank"><i class="bi bi-link me-1"></i>Tutorial: Virtual Hosts no Ubuntu</a></li>
        </ul>
    </div>
</div>

<script>
    function copiarVirtualHost() {
        const texto = document.getElementById("blocoVhost").innerText;
        navigator.clipboard.writeText(texto)
            .then(() => alert("VirtualHost copiado para a área de transferência!"))
            .catch(err => alert("Erro ao copiar: " + err));
    }
</script>
