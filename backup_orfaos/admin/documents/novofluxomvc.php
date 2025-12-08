<!-- 📄 docs/fluxomvc.php -->
<div class="container-fluid px-3 px-md-4 py-4">

    <!-- CABEÇALHO -->
    <div data-aos="fade-down">
        <h2 class="text-primary">🔄 Fluxo MVC da Aplicação</h2>
        <p class="lead text-muted">Como sua aplicação processa requisições passo a passo com arquitetura MVC moderna</p>
    </div>
    <hr class="my-4">

    <!-- ETAPAS DO FLUXO -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3">🧠 Etapas da Execução</h4>
        <ol class="ps-3">
            <li><strong>Usuário acessa:</strong> uma URL, como <code>/contact/send</code></li>
            <li><strong>.htaccess:</strong> redireciona para <code>public/index.php</code></li>
            <li><strong>Router:</strong> interpreta a URL como <code>['contact', 'send']</code></li>
            <li><strong>App.php:</strong> instancia <code>ContactController</code></li>
            <li><strong>Método:</strong> <code>send()</code> é executado</li>
            <li><strong>Controller:</strong> chama a <code>view()</code> correspondente</li>
            <li><strong>View:</strong> é renderizada dentro do layout <code>main.php</code></li>
        </ol>
    </div>

    <!-- FLUXO SIMPLIFICADO -->
    <div class="mb-5 text-center" data-aos="zoom-in">
        <pre class="bg-dark text-light p-4 rounded shadow mb-0">
URL → .htaccess → index.php → App.php → Router
              ↓
     Controller → Método → View
        </pre>
    </div>

    <!-- RESPONSABILIDADES POR CAMADA -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3">📦 Camadas e Responsabilidades</h4>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Camada</th>
                    <th>Responsabilidade</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><code>Router.php</code></td>
                    <td>Segmenta a URL e identifica controller e método</td>
                </tr>
                <tr>
                    <td><code>App.php</code></td>
                    <td>Core da aplicação: instancia e executa o controller</td>
                </tr>
                <tr>
                    <td><code>Controller</code></td>
                    <td>Lógica de negócio, manipulação de dados e chamadas de view</td>
                </tr>
                <tr>
                    <td><code>View</code></td>
                    <td>Exibição de dados ao usuário, render dentro do layout</td>
                </tr>
                <tr>
                    <td><code>Model</code></td>
                    <td>Acesso a banco, validações e regras da aplicação</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- EXEMPLO PRÁTICO -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3">🧪 Exemplo prático</h4>
        <div class="card shadow border-0">
            <div class="card-body">
                <p><strong>URL acessada:</strong> <code>/contact/send</code></p>
                <ul class="mb-0">
                    <li><strong>Controller:</strong> ContactController</li>
                    <li><strong>Método:</strong> send()</li>
                    <li><strong>Função:</strong> Processar formulário de contato e enviar email via PHPMailer</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- CAMADA CORE -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3">🧱 Núcleo da Aplicação (Core)</h4>
        <ul class="ps-3">
            <li><strong><code>Router.php</code>:</strong> Analisa e interpreta a URL</li>
            <li><strong><code>App.php</code>:</strong> Carrega controller e executa método</li>
            <li><strong><code>Controller.php</code>:</strong> Classe-mãe de todos os controllers</li>
        </ul>
    </div>

    <!-- VANTAGENS -->
    <div class="mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3">🚀 Vantagens da Arquitetura MVC</h4>
        <ul class="timeline list-unstyled ps-0 mt-3">
            <li class="mb-2">✅ Código organizado em responsabilidades claras</li>
            <li class="mb-2">✅ Facilidade para manutenção e testes isolados</li>
            <li class="mb-2">✅ Alta escalabilidade e reuso de componentes</li>
            <li class="mb-2">✅ Boas práticas adotadas desde a fundação</li>
        </ul>
    </div>

    <!-- DIAGRAMA MERMAID -->
    <div class="bg-light border rounded p-4 mb-5" data-aos="fade-up">
        <h4 class="text-primary mb-3 text-center">📊 Diagrama Visual (Mermaid)</h4>
        <div class="mermaid text-start">
            flowchart TD
            A[👤 Usuário] -->|Ação| B[🖥️ View]
            B -->|Dispara| C[🧠 Controller]
            C -->|Consulta| D[💾 Model]
            D -->|Retorna dados| C
            C -->|Renderiza| E[🖥️ View Atualizada]
            E -->|Resposta| A
        </div>
    </div>

    <!-- LEGENDA -->
    <div class="mt-4" data-aos="fade-up">
        <h4 class="text-primary mb-3">📌 Legenda do Diagrama</h4>
        <ul class="ps-3">
            <li><strong>Usuário:</strong> dispara uma ação via interface</li>
            <li><strong>View:</strong> recebe evento e encaminha</li>
            <li><strong>Controller:</strong> gerencia lógica e dados</li>
            <li><strong>Model:</strong> lida com persistência</li>
            <li><strong>View Atualizada:</strong> exibe retorno ao usuário</li>
        </ul>
    </div>
</div>
