<form method="post" action="<?= BASE_URL ?>admin/funcoes/<?= isset($registro) ? 'atualizar/' . $registro['id'] : 'salvar' ?>">
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <label class="form-label fw-bold">Código da função</label>
            <input type="text" name="staff_id" class="form-control"
                   value="<?= htmlspecialchars($registro['staff_id'] ?? 'FUNC-' . date('YmdHis')) ?>" readonly>
        </div>
        <div class="col-md-9">
            <label class="form-label fw-bold">Descrição da função</label>
            <input type="text" name="nome" class="form-control" required value="<?= $registro['nome'] ?? '' ?>">
        </div>
    </div>
    <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-end">
            <a href="<?= BASE_URL ?>admin" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i> Painel</a>
            <a href="<?= BASE_URL ?>admin/funcoes" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Salvar</button>
    </div>
</form>

