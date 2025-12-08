<div class="container py-5">

    <!-- TÍTULO -->
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="text-primary">
            <i class="bi bi-diagram-3 me-2"></i>Estrutura do Projeto (MVC)
        </h1>
        <p class="lead text-muted">
            Entenda como o padrão <strong>MVC (Model-View-Controller)</strong> organiza o sistema com clareza, escalabilidade e separação de responsabilidades.
        </p>
    </div>

    <!-- O QUE É MVC -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary">
            <i class="bi bi-vector-pen me-2"></i>O que é MVC?
        </h4>
        <p>
            O <strong>Modelo–Visão–Controlador (MVC)</strong> é um dos padrões mais utilizados no desenvolvimento de software moderno.
            Ele divide a aplicação em três responsabilidades:
        </p>
        <ul>
            <li><i class="bi bi-database-check me-1 text-success"></i><strong>Model:</strong> dados e lógica de negócio (ex: banco, regras, validações)</li>
            <li><i class="bi bi-eye me-1 text-info"></i><strong>View:</strong> exibe a interface visual (HTML, layouts, páginas)</li>
            <li><i class="bi bi-gear-fill me-1 text-warning"></i><strong>Controller:</strong> recebe requisições, aciona o Model e decide qual View renderizar</li>
        </ul>
        <p>
            Essa separação facilita manutenção, testes, segurança e evolução do sistema.
        </p>
    </div>

    <!-- ESTRUTURA DE PASTAS -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary">
            <i class="bi bi-folder-fill me-2"></i>Estrutura Atual do Projeto
        </h4>

        <div class="position-relative bg-dark text-light rounded p-4 shadow-sm border border-primary">
            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copiarEstrutura()">
                <i class="bi bi-clipboard"></i> Copiar
            </button>

            <pre id="estruturaProjeto" class="mb-0"><code>mvc/
├── app/
│   ├── Controllers/
│   │   ├── Admin
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DocsController.php
│   │   │   ├── EquipamentosController.php
│   │   │   ├── FuncoesController.php
│   │   │   ├── ObrasController.php
│   │   │   ├── OpusController.php
│   │   │   ├── PessoalController.php
│   │   │   ├── PostsController.php
│   │   │   ├── StatusController.php
│   │   │   └── SystemController.php
│   │   └── Site
│   │       ├── AboutController.php
│   │       ├── BlogController.php
│   │       ├── ContactController.php
│   │       ├── CoverageController.php
│   │       ├── HomeController.php
│   │       └── ReadmeController.php
│   ├── Core
│   │   ├── App.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Router.php
│   │   └── View.php
│   ├── Helpers
│   │   ├── FormatHelper.php
│   │   ├── RouteHelper.php
│   │   └── SystemVersions.php
│   ├── Models
│   │   ├── EquipamentoModel.php
│   │   ├── FuncaoModel.php
│   │   ├── ObraModel.php
│   │   ├── PessoalModel.php
│   │   ├── Post.php
│   │   └── User.php
│   └── Views
│       ├── 404.php
│       ├── about.php
│       ├── admin
│       │   ├── dashboard.php
│       │   ├── documents
│       │   │   ├── blog.php
│       │   │   ├── caracteristicas.php
│       │   │   ├── composer.php
│       │   │   ├── diagrama.php
│       │   │   ├── elements.php
│       │   │   ├── estrutura.php
│       │   │   ├── fluxomvc.php
│       │   │   ├── fluxopost.php
│       │   │   ├── index.php
│       │   │   ├── novofluxomvc.php
│       │   │   └── virtualhost.php
│       │   ├── equipamentos
│       │   │   ├── _form.php
│       │   │   ├── cadastrar.php
│       │   │   ├── editar.php
│       │   │   ├── index.php
│       │   │   └── indice.php
│       │   ├── ferramenta.php
│       │   ├── funcoes
│       │   │   ├── _form.php
│       │   │   ├── cadastrar.php
│       │   │   ├── editar.php
│       │   │   └── index.php
│       │   ├── login.php
│       │   ├── obras
│       │   │   ├── _form.php
│       │   │   ├── cadastrar.php
│       │   │   ├── editar.php
│       │   │   └── index.php
│       │   ├── opus
│       │   │   ├── cad_cat_eqp.php
│       │   │   ├── cad_eqp.php
│       │   │   ├── cad_opus.php
│       │   │   ├── cad_user.php
│       │   │   ├── cadastrar_eqp.php
│       │   │   ├── index.php
│       │   │   ├── list_user.php
│       │   │   ├── lista_cat_eqp.php
│       │   │   ├── lista_eqp.php
│       │   │   ├── obras_andamento.php
│       │   │   ├── opus_manager.php
│       │   │   ├── tools
│       │   │   │   ├── cadastrar_cat_eqp.php
│       │   │   │   ├── ferramentas.php
│       │   │   │   ├── listar_cat_eqp.php
│       │   │   │   └── listar_eqp.php
│       │   │   └── user_manager.php
│       │   ├── pessoal
│       │   │   ├── _form.php
│       │   │   ├── cadastrar.php
│       │   │   ├── editar.php
│       │   │   └── index.php
│       │   ├── posts
│       │   │   ├── create.php
│       │   │   ├── edit.php
│       │   │   └── index.php
│       │   ├── status.php
│       │   └── system
│       │       ├── info.php
│       │       └── versions.php
│       ├── blog
│       │   ├── index.php
│       │   └── ver.php
│       ├── blog.php
│       ├── contact.php
│       ├── coverage.php
│       ├── dash.php
│       ├── docs
│       │   ├── blog.php
│       │   ├── caracteristicas.php
│       │   ├── composer.php
│       │   ├── diagrama.php
│       │   ├── estrutura.php
│       │   ├── fluxomvc.php
│       │   ├── fluxopost.php
│       │   ├── novofluxomvc.php
│       │   └── virtualhost.php
│       ├── home.php
│       ├── layouts
│       │   ├── admin.php
│       │   ├── admin_footer.php
│       │   ├── admin_header.php
│       │   ├── admin_sidebar.php
│       │   ├── auth.php
│       │   ├── footer.php
│       │   ├── header.php
│       │   ├── main.php
│       │   └── old_admin_sidebar.php
│       ├── post.php
│       ├── posts.sql
│       ├── readme.php
│       └── templates
│           └── main.php
├── config/
│   └── config.php
├── public/
│   └── assets
│       ├── css
│       │   ├── about.css
│       │   ├── admin.css
│       │   ├── blog.css
│       │   ├── contact.css
│       │   ├── footer.css
│       │   ├── header.css
│       │   ├── home.css
│       │   ├── main.css
│       │   ├── nav.css
│       │   ├── new_admin.css
│       │   └── style.css
│       ├── images
│       │   ├── Full stack1.png
│       │   ├── Full stack2.png
│       │   ├── favicon
│       │   │   ├── favicon-32x32-1.png
│       │   │   ├── favicon-32x32-2.png
│       │   │   ├── favicon-32x32-3.png
│       │   │   ├── favicon-32x32-4.png
│       │   │   ├── favicon-32x32-5.png
│       │   │   ├── favicon-32x32.png
│       │   │   └── php
│       │   │       ├── Full stack1.png
│       │   │       ├── android-chrome-192x192.png
│       │   │       ├── android-chrome-512x512.png
│       │   │       ├── favicon-16x16.png
│       │   │       ├── favicon-32x32.png
│       │   │       ├── favicon.ico
│       │   │       └── site.webmanifest
│       │   └── team
│       │       ├── avatar1.png
│       │       └── silhueta_150x200.png
│       └── js
│           ├── admin.js
│           ├── main.min.js
│           └── script.js
├── vendor/
│   └── autoload.php                  # PSR-4 via Composer
├── composer.json
├── composer.lock
└── .env
</code></pre>
        </div>
    </div>

    <!-- EXPLICAÇÃO DOS DIRETÓRIOS -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary">
            <i class="bi bi-signpost-2-fill me-2"></i>Explicação dos Diretórios
        </h4>
        <ul>
            <li><strong><code>/app</code>:</strong> Lógica da aplicação</li>
            <ul>
                <li><code>Controllers/</code> – Valida dados e decide o que carregar</li>
                <li><code>Models/</code> – Dados e lógica de negócio</li>
                <li><code>Views/</code> – HTML/PHP das interfaces</li>
                <li><code>Core/</code> – Engine MVC, roteador e base Controller</li>
            </ul>
            <li><strong><code>/config</code>:</strong> Configurações da aplicação</li>
            <li><strong><code>/public</code>:</strong> Pasta acessada pelo navegador (com index.php e assets)</li>
            <li><strong><code>/vendor</code>:</strong> Pacotes do Composer</li>
            <li><strong><code>composer.json</code>:</strong> Lista dependências PHP</li>
            <li><strong><code>.env</code>:</strong> Variáveis de ambiente (.gitignore ativado)</li>
        </ul>
    </div>

</div>

<!-- SCRIPT COPIA -->
<script>
    function copiarEstrutura() {
        const texto = document.getElementById("estruturaProjeto").innerText;
        navigator.clipboard.writeText(texto)
            .then(() => alert("📦 Estrutura copiada para a área de transferência!"))
            .catch(err => alert("Erro ao copiar: " + err));
    }
</script>
