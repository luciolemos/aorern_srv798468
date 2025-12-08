<!-- 📄 docs/fluxomvc.php -->
<div class="container py-5">

    <!-- 🧭 Título -->
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="text-primary"><i class="bi bi-diagram-3-fill me-2"></i>Desvendando o fluxo do POST</h1>
        <p class="lead text-muted">Entenda como sua aplicação MVC processa um POST</p>
    </div>

    <!-- 📌 Explicação rápida -->
    <div data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-lightning-fill me-2"></i>Entenda o fluxo do POST ou de qualquer requisição no MVC</h4>
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

    <div class="mt-5" data-aos="fade-up">
        <h4 class="text-primary"><i class="bi bi-arrow-repeat me-2"></i>Detalhamento do fluxo de requisição</h4>
        <ol class="ps-3">
            <li><strong>Usuário</strong> acessa uma página (GET ou POST), como <code>/posts</code>.</li>
            <li>A <strong>View</strong> envia a requisição para o <strong>Controller</strong> responsável.</li>
            <li>O <strong>Controller</strong> chama o <strong>Model</strong> para buscar ou manipular os dados.</li>
            <li>O <strong>Model</strong> executa uma <code>query SQL</code> diretamente no <strong>Banco de Dados</strong>.</li>
            <li>O <strong>Banco</strong> devolve os dados ao <strong>Model</strong>.</li>
            <li>O <strong>Model</strong> retorna os dados prontos ao <strong>Controller</strong>.</li>
            <li>O <strong>Controller</strong> formata e encaminha os dados para a <strong>View</strong>.</li>
            <li>Finalmente, a <strong>View</strong> entrega o conteúdo ao <strong>Usuário</strong> em forma de HTML renderizado.</li>
        </ol>
    </div>


</div>

