<!-- app/Views/post.php -->

<div class="container mt-5 py-5">

    <!-- 🧭 Cabeçalho do Post -->
    <div class="text-center mb-4" data-aos="fade-down">
        <h1 class="text-primary"><?= htmlspecialchars($post['titulo']) ?></h1>
        <p class="text-muted small">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($post['autor'] ?: 'Anônimo') ?>
            •
            <i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y H:i', strtotime($post['criado_em'])) ?>
        </p>
    </div>

    <!-- 📄 Conteúdo -->
    <div class="mb-5" data-aos="fade-up">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <?= nl2br($post['conteudo']) ?>
            </div>
        </div>
    </div>

    <!-- 🔗 Navegação -->
    <div class="text-center" data-aos="fade-up">
        <a href="<?= BASE_URL ?>blog" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar para o Blog
        </a>
    </div>

</div>

<!-- 💬 Comentários Giscus -->
<div class="mt-5" data-aos="fade-up">
    <h5 class="text-primary mb-3"><i class="bi bi-chat-dots-fill me-2"></i>Comentários</h5>
    <div id="giscus-container" class="bg-light p-3 rounded shadow-sm">
        <script src="https://giscus.app/client.js"
                data-repo="luciolemos/mvc"
                data-repo-id="R_kgDOOrqc9g"
                data-category="Show and tell"
                data-category-id="DIC_kwDOOrqc9s4CqRCQ"
                data-mapping="pathname"
                data-strict="0"
                data-reactions-enabled="1"
                data-emit-metadata="0"
                data-input-position="top"
                data-theme="light"
                data-lang="pt"
                crossorigin="anonymous"
                async>
        </script>
    </div>
</div>


