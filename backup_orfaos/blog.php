<!-- app/Views/blog.php -->
<div class="container mt-5 py-5">
    <!-- Título -->
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="text-primary">
            <i class="bi bi-journal-text me-2"></i>Blog do Dev
        </h1>
        <p class="lead text-muted">Postagens técnicas, dicas e novidades do projeto</p>
    </div>

    <!-- Listagem de posts -->
    <div class="row g-4" data-aos="fade-up">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                <?= htmlspecialchars($post['titulo']) ?>
                            </h5>
                            <p class="card-text text-muted small">
                                <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($post['autor'] ?: 'Anônimo') ?> •
                                <i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y', strtotime($post['criado_em'])) ?>
                            </p>
                            <p class="card-text">
                                <?= substr(strip_tags($post['conteudo']), 0, 120) ?>...
                            </p>
                            <a href="<?= BASE_URL ?>blog/post/<?= $post['slug'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Ler mais
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="text-muted">Nenhum post encontrado.</p>
            </div>
        <?php endif; ?>
    </div>
</div>