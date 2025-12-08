<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Cabeçalho -->
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-box-seam me-2"></i>Gerenciador de Dependências: Composer</h2>
        <p class="lead text-muted">
            O Composer é essencial no ecossistema PHP, gerenciando dependências e carregamento automático com padrão PSR-4.
        </p>
    </div>
    <hr class="my-4">

    <!-- O que é o Composer -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-question-circle-fill me-2"></i>O que é o Composer?</h4>
        <p>
            O <strong><a href="https://getcomposer.org" target="_blank">Composer</a></strong> é um gerenciador de pacotes para PHP.
            Permite declarar as bibliotecas que seu projeto usa e ele cuida da instalação, atualização e carregamento automático (autoload).
        </p>
        <p>
            As dependências são distribuídas via <a href="https://packagist.org" target="_blank">Packagist</a>, repositório oficial.
        </p>
    </div>

    <!-- Arquivo composer.json -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-filetype-json me-2"></i>Arquivo <code>composer.json</code></h4>

        <div class="position-relative bg-dark text-light rounded p-4 shadow-sm border border-primary">
            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2" onclick="copiarComposer()">
                <i class="bi bi-clipboard"></i> Copiar
            </button>
            <pre id="composerContent" class="mb-0"><code>{
  "name": "luciolemos/mvc",
  "description": "Projeto MVC com PHP e MySQL",
  "type": "project",
  "license": "MIT",
  "version": "1.0.0",
  "homepage": "https://github.com/luciolemos/mvc",
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
    "vlucas/phpdotenv": "^5.5",
    "phpmailer/phpmailer": "^6.8"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0"
  },
  "scripts": {
    "test": "phpunit --coverage-html coverage"
  }
}
</code></pre>
        </div>
    </div>

    <!-- Explicação das Chaves -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-diagram-3 me-2"></i>Explicação das Chaves</h4>
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <div class="col">
                <ul class="list-group shadow-sm">
                    <li class="list-group-item"><i class="bi bi-tag me-2 text-primary"></i><strong>name</strong>: <code>luciolemos/mvc</code></li>
                    <li class="list-group-item"><i class="bi bi-card-text me-2 text-primary"></i><strong>description</strong>: Descrição sumária do projeto</li>
                    <li class="list-group-item"><i class="bi bi-diagram-3-fill me-2 text-primary"></i><strong>type</strong>: Tipo do pacote (<code>project</code>)</li>
                    <li class="list-group-item"><i class="bi bi-shield-check me-2 text-primary"></i><strong>license</strong>: Licença de uso (<code>MIT</code>)</li>
                    <li class="list-group-item"><i class="bi bi-hash me-2 text-primary"></i><strong>version</strong>: Versão atual</li>
                    <li class="list-group-item"><i class="bi bi-globe me-2 text-primary"></i><strong>homepage</strong>: Link para o GitHub</li>
                </ul>
            </div>
            <div class="col">
                <ul class="list-group shadow-sm">
                    <li class="list-group-item"><i class="bi bi-exclamation-circle me-2 text-warning"></i><strong>minimum-stability</strong>: Somente pacotes <code>stable</code></li>
                    <li class="list-group-item"><i class="bi bi-people-fill me-2 text-primary"></i><strong>authors</strong>: Informações do(s) autor(es)</li>
                    <li class="list-group-item"><i class="bi bi-boxes me-2 text-primary"></i><strong>autoload</strong>: Define carregamento automático via <code>PSR-4</code></li>
                    <li class="list-group-item"><i class="bi bi-plug-fill me-2 text-success"></i><strong>require</strong>: Dependências obrigatórias</li>
                    <li class="list-group-item"><i class="bi bi-tools me-2 text-warning"></i><strong>require-dev</strong>: Dependências para desenvolvimento</li>
                    <li class="list-group-item"><i class="bi bi-terminal me-2 text-info"></i><strong>scripts</strong>: Comandos como <code>composer run test</code></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Dica final -->
    <div class="alert alert-info mt-4" data-aos="fade-in">
        <i class="bi bi-lightbulb-fill text-danger me-2"></i>
       Após clonar o projeto, execute no terminal:
        <div class="mt-2">
            <code>$ composer install</code><br>
            <code>$ composer dump-autoload</code>
        </div>
    </div>
</div>

<!-- Script para copiar -->
<script>
    function copiarComposer() {
        const texto = document.getElementById("composerContent").innerText;
        navigator.clipboard.writeText(texto)
            .then(() => alert("📋 composer.json copiado com sucesso!"))
            .catch(err => alert("Erro ao copiar: " + err));
    }
</script>
