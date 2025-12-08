<?php use App\Helpers\FormatHelper; ?>
<form method="post" action="<?= BASE_URL ?>admin/obras/<?= isset($obra) ? 'atualizar/' . $obra['id'] : 'salvar' ?>">
    <fieldset class="mb-3">
        <legend class="h5 text-danger"><i class="bi bi-person-vcard"></i> Dados da obra</legend>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">CÓD OBRA OU SERVIÇO</label>
                <input type="text" class="form-control" name="numero_obra" readonly
                       value="<?= $obra['numero_obra'] ?? 'OPUS-' . date('YmdHis') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">NATUREZA *</label>
                <select class="form-select" name="natureza_obra" required>
                    <?php $naturezaList = [
                        'Construção',
                        'Reforma',
                        'Restauração',
                        'Manutenção preventiva',
                        'Manutenção corretiva',
                        'Infraestrutura',
                        'Serviços especializados',
                        'Residencial',
                        'Comercial',
                        'Industrial',
                        'Predial',
                        'Outra natureza'
                    ]; ?>
                    <?php foreach ($naturezaList as $st): ?>
                        <option value="<?= $st ?>" <?= ($obra['natureza_obra'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">ENDEREÇO DA OBRA OU SERVIÇO*</label>
                <input type="text" name="endereco" class="form-control" required value="<?= $obra['endereco'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" class="form-control input-cep"
                       placeholder="00000-000" value="<?= FormatHelper::cep($obra['cep'] ?? '') ?>">
            </div>
        </div>
    </fieldset>

    <fieldset class="mb-3">
        <legend class="h5 text-danger"><i class="bi bi-person-vcard"></i> Descrição da obra *</legend>
        <div class="row g-3">
            <div class="col-md-12">
                <!--                <label class="form-label">DESCRIÇÃO DA OBRA OU SERVIÇO *</label>-->
                <textarea name="descricao" placeholder="Descrição da obra ou serviço a ser realizado."
                          class="form-control" required><?= $obra['descricao'] ?? '' ?></textarea>
            </div>
        </div>
    </fieldset>

    <fieldset class="mb-3">
        <legend class="h5 text-danger"><i class="bi bi-person-vcard"></i> Acompanhamento da obra</legend>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">INÍCIO *</label>
                <input type="date" name="data_inicio" class="form-control" required
                       value="<?= $obra['data_inicio'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">TÉRMINO</label>
                <input type="date" name="data_termino" class="form-control" value="<?= $obra['data_termino'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">VALOR ESTIMADO (R$)</label>
                <input type="text" name="valor_estimado" class="form-control input-money"
                       value="<?= $obra['valor_estimado'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">PRIORIDADE</label>
                <select name="prioridade" class="form-select">
                    <?php $prio = [
                        'Baixa',
                        'Média',
                        'Alta',
                        'Urgente']; ?>
                    <?php foreach ($prio as $p): ?>
                        <option value="<?= $p ?>" <?= ($obra['prioridade'] ?? 'Média') === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">STATUS</label>
                <select name="status" class="form-select">
                    <?php $statusList = ['Planejamento', 'Em Andamento', 'Pausada', 'Concluída']; ?>
                    <?php foreach ($statusList as $st): ?>
                        <option value="<?= $st ?>" <?= ($obra['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">OBSERVAÇÃO</label>
                <textarea name="observacoes" class="form-control"><?= $obra['observacoes'] ?? '' ?></textarea>
            </div>
        </div>
    </fieldset>

    <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-end">
        <a href="<?= BASE_URL ?>admin" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i> Painel</a>
        <a href="<?= BASE_URL ?>admin/obras" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Salvar</button>
    </div>
</form>