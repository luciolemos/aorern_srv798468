<form method="post" action="<?= BASE_URL ?>admin/categorias/<?= isset($registro) ? 'atualizar/' . $registro['id'] : 'salvar' ?>">
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <label class="form-label">CÓD</label>
            <input type="text" name="staff_id" class="form-control"
                   value="<?= htmlspecialchars($registro['staff_id'] ?? 'CAT-' . date('YmdHis')) ?>" readonly>
        </div>
        <div class="col-md-9">
            <label class="form-label">DESCRIÇÃO DA CATEGORIA</label>
            <input type="text" name="nome" class="form-control"
                   value="<?= htmlspecialchars($registro['nome'] ?? '') ?>" required>
        </div>
    </div>
    <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-end">
        <a href="<?= BASE_URL ?>admin" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i> Painel</a>
        <a href="<?= BASE_URL ?>admin/categorias" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Salvar</button>
    </div>
</form>
