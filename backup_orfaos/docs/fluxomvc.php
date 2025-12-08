<!-- 📄 docs/fluxomvc.php -->
<div class="container py-5">

    <!-- 🧭 Título -->
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="text-primary"><i class="bi bi-diagram-3-fill me-2"></i>Fluxo MVC da Aplicação</h1>
        <p class="lead text-muted">Entenda como sua aplicação MVC processa uma requisição do início ao fim</p>
    </div>

    <!-- 🔄 Passo a Passo -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3"><i class="bi bi-lightbulb-fill me-2"></i>Como funciona o ciclo de execução</h4>
        <ol class="ps-3">
            <li><strong>Usuário acessa uma URL</strong> – Ex: <code>/contact/send</code></li>
            <li><code>.htaccess</code> redireciona tudo para <code>public/index.php</code></li>
            <li><code>Router</code> interpreta a URL: <code>['contact', 'send']</code></li>
            <li><code>App.php</code> instancia o <code>ContactController</code></li>
            <li>Método <code>send()</code> é executado</li>
            <li><code>Controller</code> carrega a <code>view</code></li>
            <li>View é renderizada dentro do layout <code>main.php</code></li>
        </ol>
    </div>

    <!-- 🔁 Fluxo simplificado -->
    <div class="mb-5 text-center" data-aos="zoom-in">
        <pre class="bg-dark text-light p-4 rounded shadow-sm mb-0">
URL → .htaccess → index.php → App.php → Router
                   ↓
          Controller → Método → View
        </pre>
    </div>

    <!-- 🧱 Responsabilidades por camada -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3"><i class="bi bi-layers-fill me-2"></i>Responsabilidades por Camada</h4>
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
            <tr>
                <th>Camada</th>
                <th>Responsabilidade</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><code>Router.php</code></td>
                <td>Interpretar e segmentar a URL para controller e método</td>
            </tr>
            <tr>
                <td><code>App.php</code></td>
                <td>Motor principal que instancia controllers e executa os métodos</td>
            </tr>
            <tr>
                <td><code>Controller</code></td>
                <td>Processa a lógica da requisição e invoca a view</td>
            </tr>
            <tr>
                <td><code>View</code></td>
                <td>Exibe o conteúdo ao usuário dentro do layout principal</td>
            </tr>
            <tr>
                <td><code>Model</code></td>
                <td>Acessa banco de dados (futuramente)</td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- 🧪 Exemplo prático -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3"><i class="bi bi-flask me-2"></i>Exemplo prático</h4>
        <div class="card shadow-sm">
            <div class="card-body">
                <p><strong>URL:</strong> <code>/contact/send</code></p>
                <ul class="mb-0">
                    <li><strong>Controller:</strong> <code>ContactController.php</code></li>
                    <li><strong>Método:</strong> <code>send()</code></li>
                    <li><strong>Responsabilidade:</strong> processar dados e enviar email</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 🔍 Estrutura core -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3"><i class="bi bi-box me-2"></i>Classes centrais da arquitetura</h4>
        <ul>
            <li><code>Core/Router.php</code>: Quebra e interpreta a URL</li>
            <li><code>Core/App.php</code>: Instancia o controller e método</li>
            <li><code>Core/Controller.php</code>: Base herdada por todos os controllers</li>
        </ul>
    </div>

    <!-- 📆 Benefícios -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3"><i class="bi bi-check-circle-fill me-2"></i>Benefícios da Arquitetura</h4>
        <ul class="timeline list-unstyled ps-0 mt-3">
            <li class="mb-2"><i class="bi bi-check2-square me-2 text-info"></i> Separação de responsabilidades</li>
            <li class="mb-2"><i class="bi bi-check2-square me-2 text-info"></i> Facilidade de manutenção</li>
            <li class="mb-2"><i class="bi bi-check2-square me-2 text-info"></i> Reutilização de código</li>
            <li class="mb-2"><i class="bi bi-check2-square me-2 text-info"></i> Testabilidade e escalabilidade</li>
        </ul>
    </div>

    <!-- 🔁 Diagrama de fluxo (Mermaid) -->
    <div class="bg-light border rounded p-4 mb-5" data-aos="fade-up">
        <h5 class="text-primary mb-3"><i class="bi bi-diagram-2-fill me-2"></i>Fluxo visual (Mermaid)</h5>
        <div class="mermaid">
            flowchart TD
            U[<i class="bi bi-people-fill me-2"></i> Usuário] -->|Interação| V1[<i class="bi bi-display me-2 text-danger"></i> View]
            V1 -->|Aciona| C[<i class="bi bi-shield-lock me-2 text-primary"></i> Controller]
            C -->|Solicita dados| M[<i class="bi bi-check2-square me-2 text-info"></i> Model]
            M -->|Retorna dados| C
            C -->|Seleciona e atualiza| V2[<i class="bi bi-list-check me-2 text-success"></i> View atualizada]
            V2 -->|Exibe resposta| U
        </div>
    </div>

    <!-- 📌 Explicação rápida -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-lightning-fill me-2"></i>Explicação rápida:</h4>
        <ul class="ps-3">
            <li><code>U</code>: Usuário inicia uma ação</li>
            <li><code>V</code>: View trata a interface e repassa a requisição</li>
            <li><code>C</code>: Controller lida com lógica da aplicação</li>
            <li><code>M</code>: Model acessa banco e regra de negócio</li>
            <li>Controller renderiza a nova View</li>
        </ul>
    </div>

    <!-- 🧬 Diagrama sequência -->
    <div class="bg-light border rounded p-4" data-aos="fade-up">
        <h5 class="text-primary mb-3"><i class="bi bi-graph-up me-2"></i>Diagrama de sequência</h5>
        <div class="mermaid">
            sequenceDiagram
            participant Usuário
            participant View
            participant Controller
            participant Model
            participant BancoDeDados

            Usuário->>View: Acessa (GET /posts)
            View->>Controller: Envia requisição
            Controller->>Model: getAllPosts()
            Model->>BancoDeDados: SELECT * FROM posts
            BancoDeDados-->>Model: Lista de posts
            Model-->>Controller: Retorna dados
            Controller-->>View: Envia conteúdo
            View-->>Usuário: Exibe HTML com dados
        </div>
    </div>

</div>
