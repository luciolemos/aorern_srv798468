<div class="container px-3 px-md-4 py-4">
    <!-- 🧭 TÍTULO PRINCIPAL -->
    <div data-aos="fade-down" class="mb-4">
        <h2 class="text-primary"><i class="bi bi-tags me-2"></i> Editar categoria de post</h2>
        <p class="text-muted mb-0">Altere as características de suas categorias de posts</p>
    </div>

    <!-- 📝 FORMULÁRIO DE EDIÇÃO -->
    <!-- <div class="card border-0 shadow-sm"> -->
        <!-- <div class="card-body"> -->
            <form action="<?= BASE_URL ?>admin/post-categories/update/<?= $categoria['id'] ?>" method="post">
                <?php use App\Helpers\CsrfHelper; echo CsrfHelper::inputField(); ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Código da categoria</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($categoria['staff_id']) ?>" readonly>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nome da categoria</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($categoria['nome']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Descrição</label>
                        <textarea name="descricao" rows="4" class="form-control"><?= htmlspecialchars($categoria['descricao'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end mt-4">
                    <a href="<?= BASE_URL ?>admin/post-categories" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Atualizar
                    </button>
                </div>
            </form>
        <!-- </div> -->
    <!-- </div> -->
</div>
