<?php use App\Helpers\FormatHelper; ?>

<form method="post" action="<?= BASE_URL ?>admin/pessoal/<?= isset($registro) ? 'atualizar/' . $registro['id'] : 'salvar' ?>" enctype="multipart/form-data">
    
    <div class="row">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Card Dados Pessoais -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-person-vcard me-2"></i>Dados Pessoais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Código do Bombeiro</label>
                            <input type="text" class="form-control" name="staff_id" readonly
                                   value="<?= $registro['staff_id'] ?? 'FIREMAN-' . date('YmdHis') ?>">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome" required
                                   value="<?= $registro['nome'] ?? '' ?>" placeholder="Digite o nome completo">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">CPF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control input-cpf" name="cpf" required
                                   maxlength="14"
                                   value="<?= FormatHelper::cpf($registro['cpf'] ?? '') ?>" placeholder="000.000.000-00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Data Nascimento</label>
                            <input type="date" class="form-control" name="nascimento"
                                   value="<?= $registro['nascimento'] ?? '' ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Celular</label>
                            <input type="text" class="form-control input-phone" name="telefone"
                                   maxlength="16"
                                   value="<?= FormatHelper::telefone($registro['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">
                                <i class="bi bi-image me-1"></i>Foto do Bombeiro
                            </label>
                            <input type="file" class="form-control" name="avatar" 
                                   accept="image/jpeg,image/png,image/webp" 
                                   onchange="previewAvatar(this)">
                            <small class="text-muted">JPG, PNG ou WebP • Máximo 50MB (opcional)</small>
                            
                            <!-- Preview -->
                            <div class="mt-2" id="avatarPreviewContainer" style="<?= isset($registro['avatar']) && $registro['avatar'] ? '' : 'display: none;' ?>">
                                <img id="avatarPreview" 
                                     src="<?= isset($registro['avatar']) && $registro['avatar'] ? BASE_URL . htmlspecialchars($registro['avatar']) : '' ?>" 
                                     alt="Preview" 
                                     class="rounded shadow-sm" 
                                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #df6301;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Atuação na Obra -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-cone-striped me-2"></i>Atuação na Obra
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Função <span class="text-danger">*</span></label>
                            <select name="workRole" class="form-select" required>
                                <option value="" disabled <?= isset($registro) ? '' : 'selected' ?>>Selecione a função</option>
                                <?php foreach ($funcoes as $funcao): ?>
                                    <option value="<?= $funcao['id'] ?>"
                                        <?= (isset($registro['funcao_id']) && (int)$registro['funcao_id'] === (int)$funcao['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($funcao['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Obra <span class="text-danger">*</span></label>
                            <select class="form-select" name="obra_id" required>
                                <option value="" disabled selected>Selecione a obra</option>
                                <?php foreach ($obras as $obra): ?>
                                    <option value="<?= $obra['id'] ?>"
                                        <?= isset($registro['obra_id']) && $registro['obra_id'] == $obra['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($obra['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jornada</label>
                            <select name="jornada" class="form-select">
                                <option value="8h"     <?= ($registro['jornada'] ?? '') === '8h' ? 'selected' : '' ?>>8h/dia</option>
                                <option value="6h"     <?= ($registro['jornada'] ?? '') === '6h' ? 'selected' : '' ?>>6h/dia</option>
                                <option value="4h"     <?= ($registro['jornada'] ?? '') === '4h' ? 'selected' : '' ?>>4h/dia</option>
                                <option value="Outros" <?= ($registro['jornada'] ?? '') === 'Outros' ? 'selected' : '' ?>>Outros</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Data Admissão <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="data_admissao" required
                                   value="<?= $registro['data_admissao'] ?? '' ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="Ativo"     <?= ($registro['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Afastado"  <?= ($registro['status'] ?? '') === 'Afastado' ? 'selected' : '' ?>>Afastado</option>
                                <option value="Férias"    <?= ($registro['status'] ?? '') === 'Férias' ? 'selected' : '' ?>>Férias</option>
                                <option value="Demitido"  <?= ($registro['status'] ?? '') === 'Demitido' ? 'selected' : '' ?>>Demitido</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Observações -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-list-columns-reverse me-2"></i>Observações
                    </h5>
                </div>
                <div class="card-body">
                    <textarea name="observacoes" rows="4" class="form-control" placeholder="Informações adicionais sobre o bombeiro..."><?= $registro['observacoes'] ?? '' ?></textarea>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary" style="background-color: #df6301; border-color: #df6301;">
                    <i class="bi bi-check-circle me-2"></i>Salvar
                </button>
                <a href="<?= BASE_URL ?>admin/pessoal" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </a>
            </div>
        </div>

        <!-- Sidebar Informações -->
        <div class="col-lg-4">
            <!-- Card de Ajuda -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-info-circle me-2"></i>Informações
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        Campos com <span class="text-danger">*</span> são obrigatórios
                    </p>
                    <p class="small text-muted mb-2">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        O código é gerado automaticamente
                    </p>
                    <p class="small text-muted mb-2">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        A foto é opcional mas recomendada
                    </p>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        Todos os dados podem ser editados posteriormente
                    </p>
                </div>
            </div>

            <?php if (isset($registro)): ?>
            <!-- Card de Estatísticas -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: #df6301;">
                        <i class="bi bi-graph-up me-2"></i>Registro
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">ID</small>
                        <strong><?= $registro['id'] ?? '-' ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Última Atualização</small>
                        <strong><?= isset($registro['updated_at']) ? date('d/m/Y H:i', strtotime($registro['updated_at'])) : '-' ?></strong>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
function previewAvatar(input) {
    const preview = document.getElementById('avatarPreview');
    const container = document.getElementById('avatarPreviewContainer');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
.form-control:focus,
.form-select:focus {
    border-color: #df6301;
    box-shadow: 0 0 0 0.2rem rgba(223, 99, 1, 0.25);
}

.btn-primary {
    background-color: #df6301;
    border-color: #df6301;
}

.btn-primary:hover {
    background-color: #b54f01;
    border-color: #b54f01;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(223, 99, 1, 0.3);
}

.card {
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}
</style>
