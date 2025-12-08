<div class="container-fluid px-3 px-md-4 py-4">
    <!-- 🧭 TÍTULO PRINCIPAL -->
    <div data-aos="fade-down" class="mb-4">
        <h2 class="text-primary"><i class="bi bi-pencil-square me-2"></i>Editar Post</h2>
        <p class="text-muted mb-0">Edite seu post</p>
    </div>
    <hr class="my-4">
    <!-- 📝 FORMULÁRIO DE EDIÇÃO -->   
    <div data-aos="fade-up">
    <form method="POST" action="<?= BASE_URL ?>admin/posts/update/<?= $post['id'] ?>">
        <?php use App\Helpers\CsrfHelper; echo CsrfHelper::inputField(); ?>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-8">
                <label for="titulo" class="form-label fw-bold">
                    Título <span class="text-danger">*</span>
                </label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?= htmlspecialchars($post['titulo']) ?>" required>
             <div class="form-text">Título principal do post</div>
            </div>
            <div class="col-12 col-lg-4">
                <label for="categoria_id" class="form-label fw-bold">
                    Categoria <span class="text-danger">*</span>
                </label>
                <select name="categoria_id" id="categoria_id" class="form-select" required>
                    <option value="" disabled>Selecione a categoria</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?= (int)$post['categoria_id'] === (int)$categoria['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label fw-bold">
                Slug (URL) <span class="text-danger">*</span>
            </label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?= htmlspecialchars($post['slug']) ?>" required>
            <div class="form-text"><?= BASE_URL?>blog/</div>
        </div>

        <div class="mb-3">
            <label for="conteudo" class="form-label fw-bold">
                Conteúdo <span class="text-danger">*</span>
            </label>
            <textarea name="conteudo" id="conteudo" rows="8" class="form-control" required><?= htmlspecialchars($post['conteudo']) ?></textarea>
        </div>

        <div class="col-12 mt-4 pt-3 border-top">
        <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-end">
            <a href="<?= BASE_URL ?>admin/posts" class="btn btn-outline-danger">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Atualizar
            </button>
        </div>
        </div>
    </form>
    </div>
</div>
