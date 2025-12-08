<div class="container-fluid px-3 px-md-4 py-4">
    <!-- 🧩 TÍTULO -->
    <div data-aos="fade-down">
        <h2 class="text-primary">
            <i class="bi bi-diagram-3-fill me-2"></i>Diagrama de Classes
        </h2>
        <p class="lead text-muted">
            Representação visual da estrutura principal da aplicação MVC com namespaces e responsabilidades técnicas.
        </p>
    </div>
    <hr class="my-4">

    <!-- 🔍 Descrição técnica -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-info-circle me-2"></i>Contexto</h4>
        <p>
            O sistema é modularizado seguindo o padrão MVC. Abaixo, visualizamos as classes do diretório <code>app/</code> e como elas se relacionam entre si.
            O núcleo (core) define os mecanismos centrais: roteamento, carregamento de controllers e renderização de views.
        </p>
    </div>

    <!-- 🧠 Diagrama em Mermaid -->
    <div class="mb-5" data-aos="fade-up">
        <div class="bg-light border rounded p-4">
            <div class="mermaid">
                classDiagram
                class App {
                +__construct()
                }

                class Router {
                +parseUrl() : array
                }

                class Controller {
                +view()
                +model()
                }

                class HomeController
                class AboutController
                class ContactController
                class AdminController
                class DocsController

                class View
                class Model

                App --> Router
                App --> Controller
                Controller <|-- HomeController
                Controller <|-- AboutController
                Controller <|-- ContactController
                Controller <|-- AdminController
                Controller <|-- DocsController
            </div>
        </div>
    </div>

    <!-- 🧱 Legenda das camadas -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-layers me-2"></i>Responsabilidade por classe</h4>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>App</strong>: Inicia o processo de roteamento e delega para controllers</li>
            <li class="list-group-item"><strong>Router</strong>: Quebra a URL e retorna array de rota/método/parâmetros</li>
            <li class="list-group-item"><strong>Controller</strong>: Classe base de todos os controllers; fornece métodos úteis</li>
            <li class="list-group-item"><strong>Views</strong>: Templates organizados por módulos (pastas)</li>
            <li class="list-group-item"><strong>Models</strong>: (Opcional) Conexão com o banco de dados e lógica de negócio</li>
        </ul>
    </div>

    <!-- 📌 Observações -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-pin-map me-2"></i>Observações</h4>
        <ul class="list-unstyled ps-0">
            <li class="mb-2"><span class="text-success"><i class="bi bi-check2-square me-2"></i></span>Diagramas são úteis para onboarding de novos devs</li>
            <li class="mb-2"><span class="text-success"><i class="bi bi-check2-square me-2"></i></span>Usamos <code>classDiagram</code> do <code>mermaid.js</code> com suporte nativo</li>
            <li class="mb-2"><span class="text-success"><i class="bi bi-check2-square me-2"></i></span>Documentação gerada diretamente das rotas públicas do sistema</li>
            <!--<i class="bi bi-check-circle-fill text-success me-2"></i>-->
        </ul>
    </div>

    <!-- 🔗 Navegação -->
<!--    <div class="text-center mt-4" data-aos="fade-up">
        <a href="<?/*= BASE_URL */?>docs/fluxomvc" class="btn btn-outline-primary me-2">
            <i class="bi bi-arrow-left me-1"></i> Fluxo MVC
        </a>
        <a href="<?/*= BASE_URL */?>docs/caracteristicas" class="btn btn-outline-secondary">
            <i class="bi bi-list-check me-1"></i> Ver Características
        </a>
    </div>-->

</div>
