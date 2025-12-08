<?php
// Variáveis esperadas: $q (string), $placeholder (string)
?>
<form method="GET" class="mb-4">
    <div class="input-group">
        <input
            type="text"
            name="q"
            class="form-control"
            placeholder="<?= $placeholder ?? 'Buscar...' ?>"
            value="<?= htmlspecialchars($q ?? '') ?>"
            autofocus
        >
        <?php if (!empty($q)): ?>
            <a href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i>
            </a>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search me-1"></i> Buscar
        </button>
    </div>
</form>

