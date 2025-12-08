<div class="container-fluid px-3 px-md-4 py-4">
    <!-- 🧭 TÍTULO PRINCIPAL -->
    <div data-aos="fade-down">
        <h2 class="text-primary"><i class="bi bi-journal-richtext me-2"></i> Documentação do Módulo de Blog</h2>
        <p class="lead text-muted">Este módulo adiciona funcionalidades completas de CRUD de posts ao painel administrativo do sistema.</p>
    </div>
    <hr class="my-4">

    <h4 class="mb-3 text-secondary">📌 Objetivo</h4>
    <p>Oferecer uma interface simples e segura para que administradores possam <strong>criar</strong>, <strong>editar</strong>, <strong>pesquisar</strong> e <strong>excluir</strong> publicações do blog diretamente no painel administrativo.</p>

    <h4 class="mt-4 mb-3 text-secondary">🔧 Recursos Implementados</h4>
    <ul>
        <li>CRUD completo de posts com CKEditor integrado</li>
        <li>Proteção de acesso via sessão</li>
        <li>Pesquisa por título</li>
        <li>Toasts de feedback visual</li>
        <li>Integração visual com layout administrativo</li>
        <li>Sidebar com destaque da rota ativa</li>
        <li>Separação limpa de layouts (público/admin)</li>
    </ul>

    <h4 class="mt-4 mb-3 text-secondary">📁 Estrutura dos Arquivos</h4>
    <pre class="bg-dark text-light p-3 rounded small">
app/
├── Controllers/
│   └── Admin/
│       └── PostsController.php     # Controller responsável por toda lógica do blog
├── Models/
│   └── Post.php                    # Classe com métodos para banco de dados
├── Views/
│   └── admin/
│       └── posts/
│           ├── create.php         # Formulário para novo post
│           ├── edit.php           # Formulário de edição
│           └── index.php          # Lista com datatable e pesquisa
    </pre>

    <h4 class="mt-4 mb-3 text-secondary">🧠 Lógica do Controller</h4>
    <p>O `PostsController` gerencia todas as operações. Ele protege as rotas com verificação de sessão e envia os dados para as views.</p>

    <p>Exemplo do método <code>store()</code>:</p>

    <pre class="bg-light p-3 border rounded small"><code>
public function store() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->post->criar(...);

        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => '🎉 Post criado com sucesso!'
        ];

        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }
}
    </code></pre>

    <h4 class="mt-4 mb-3 text-secondary">📝 CKEditor</h4>
    <p>O editor CKEditor é carregado dinamicamente dentro do layout <code>admin.php</code> com o seguinte script:</p>

    <pre class="bg-light p-3 border rounded small"><code>
&lt;script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"&gt;&lt;/script&gt;
&lt;script&gt;
    ClassicEditor
        .create(document.querySelector('#conteudo'))
        .catch(error =&gt; console.error(error));
&lt;/script&gt;
    </code></pre>

    <h4 class="mt-4 mb-3 text-secondary">🔒 Segurança</h4>
    <ul>
        <li>Somente usuários logados podem acessar <code>/admin/posts</code></li>
        <li>Validação mínima nos inputs via <code>required</code></li>
        <li>Escapando conteúdos com <code>htmlspecialchars</code> ao editar</li>
    </ul>

    <h4 class="mt-4 mb-3 text-secondary">🧪 Testes e Debug</h4>
    <p>Utilize o botão <strong>Status</strong> no painel para verificar banco de dados, SMTP e reCAPTCHA. Além disso, testes podem ser adicionados em <code>tests/</code> com PHPUnit.</p>

    <h4 class="mt-4 mb-3 text-secondary">🚀 Próximos passos</h4>
    <ul>
        <li>Validação avançada de formulário</li>
        <li>Paginação real no DataTable</li>
        <li>Slug automático com JavaScript</li>
        <li>Uploads de imagens com media manager</li>
    </ul>
</div>
