<?php use App\Helpers\FormatHelper; ?>
<form method="post" action="<?= BASE_URL ?>admin/equipamentos/<?= isset($registro) ? 'atualizar/' . $registro['id'] : 'salvar' ?>">
    <fieldset class="mb-3">
        <legend class="h5 text-info"><i class="bi bi-tools"></i> Dados do equipamento</legend>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-bold">Código do equipamento</label>
            <input type="text" class="form-control" name="staff_id" readonly
                   value="<?= $registro['staff_id'] ?? 'EQP-' . date('YmdHis') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">Nome da ferramenta ou equipamento *</label>
            <input type="text" name="nome" required class="form-control" placeholder="Furadeira de bancada" value="<?= $registro['nome'] ?? '' ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold">Número de série</label>
            <input type="text" name="serial_number" class="form-control" value="<?= $registro['numero_serie'] ?? '' ?>">
        </div>

<!--        <div class="col-12">
            <label class="form-label fw-bold">Descrição</label>
            <textarea name="descricao" placeholder="Equipamento importado, adquirido com recursos oriundos do programa Minha ferramenta, minha vida. Pot 3000W, 220V, 5,4kg, Mandril 1/2 pol, cor predominante: preta." class="form-control"><?/*= $registro['descricao'] ?? '' */?></textarea>
        </div>-->

        <div class="col-md-4">
            <label class="form-label fw-bold">Categoria *</label>
            <select name="categoria_id" class="form-select" required>
                <option value="" disabled selected>Selecione a categoria</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= isset($registro['categoria_id']) && $registro['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="col-md-4">
            <label class="form-label">Marca</label>
<!--            <input type="text" name="marca" class="form-control" value="--><?//= $registro['marca'] ?? '' ?><!--">-->
            <select name="marca" class="form-select">
                <option value="">-- Selecione uma marca --</option>
                <optgroup label="Marcas Premium">
                    <option value="DeWalt" <?= ($registro['marca'] ?? '') === 'DeWalt' ? 'selected' : '' ?>>DeWalt</option>
                    <option value="Milwaukee" <?= ($registro['marca'] ?? '') === 'Milwaukee' ? 'selected' : '' ?>>Milwaukee</option>
                    <option value="Makita" <?= ($registro['marca'] ?? '') === 'Makita' ? 'selected' : '' ?>>Makita</option>
                    <option value="Bosch Professional" <?= ($registro['marca'] ?? '') === 'Bosch Professional' ? 'selected' : '' ?>>Bosch Professional</option>
                    <option value="Festool" <?= ($registro['marca'] ?? '') === 'Festool' ? 'selected' : '' ?>>Festool</option>
                </optgroup>
                <optgroup label="Marcas de Qualidade">
                    <option value="Stanley" <?= ($registro['marca'] ?? '') === 'Stanley' ? 'selected' : '' ?>>Stanley</option>
                    <option value="Black & Decker" <?= ($registro['marca'] ?? '') === 'Black & Decker' ? 'selected' : '' ?>>Black & Decker</option>
                    <option value="Ryobi" <?= ($registro['marca'] ?? '') === 'Ryobi' ? 'selected' : '' ?>>Ryobi</option>
                    <option value="Hilti" <?= ($registro['marca'] ?? '') === 'Hilti' ? 'selected' : '' ?>>Hilti</option>
                    <option value="Metabo" <?= ($registro['marca'] ?? '') === 'Metabo' ? 'selected' : '' ?>>Metabo</option>
                </optgroup>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Modelo</label>
            <input type="text" name="modelo" class="form-control" value="<?= $registro['modelo'] ?? '' ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Data de aquisição</label>
            <input type="date" name="data_fabricacao" class="form-control" value="<?= $registro['data_fabricacao'] ?? '' ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Estado</label>
            <select name="estado" class="form-select">
                <option value="">-- Selecione um estado --</option>
                <option value="Nova" <?= ($registro['estado'] ?? '') === 'Nova' ? 'selected' : '' ?>>Nova</option>
                <option value="Usada" <?= ($registro['estado'] ?? '') === 'Usada' ? 'selected' : '' ?>>Usada</option>
                <option value="Restaurada" <?= ($registro['estado'] ?? '') === 'Restaurada' ? 'selected' : '' ?>>Restaurada</option>
                <option value="Danificada" <?= ($registro['estado'] ?? '') === 'Danificada' ? 'selected' : '' ?>>Danificada</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Estoque</label>
            <input type="number" name="quantidade_estoque" class="form-control" value="<?= $registro['quantidade_estoque'] ?? 0 ?>" required>
        </div>
    </div>
    </fieldset>

    <div class="d-flex flex-column flex-md-row gap-2 order-md-2 w-100 w-md-auto justify-content-md-end">
        <a href="<?= BASE_URL ?>admin" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i> Painel</a>
        <a href="<?= BASE_URL ?>admin/equipamentos" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Salvar</button>
    </div>
</form>

