<?php if (!empty($pagination)): ?>
    <?php
    $path = $pagination['path'] ?? (strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '');
    $query = $pagination['query'] ?? [];
    $current = (int) ($pagination['current_page'] ?? 1);
    $last = (int) ($pagination['last_page'] ?? 1);
    $range = 2;

    $buildParams = function(array $params): array {
        $filtered = [];
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $filtered[$key] = $value;
        }
        return $filtered;
    };

    $buildUrl = function(int $page) use ($path, $query, $buildParams): string {
        $allParams = $buildParams(array_merge($query, ['page' => $page]));
        $qs = http_build_query($allParams);
        $separator = str_contains($path, '?') ? '&' : '?';
        return rtrim($path, '?&') . ($qs ? $separator . $qs : '');
    };

    $start = max(1, $current - $range);
    $end = min($last, $current + $range);
    ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
        <p class="text-muted mb-0 small">
            Exibindo <?= $pagination['from'] ?? 0 ?>-<?= $pagination['to'] ?? 0 ?> de <?= $pagination['total'] ?? 0 ?> registros
        </p>
        <?php if ($last > 1): ?>
            <nav aria-label="Paginação">
                <ul class="pagination mb-0">
                    <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $current <= 1 ? '#' : $buildUrl($current - 1) ?>" tabindex="-1" aria-disabled="<?= $current <= 1 ? 'true' : 'false' ?>">
                            &laquo;
                        </a>
                    </li>

                    <?php if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $buildUrl(1) ?>">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>
    
                    <?php for ($page = $start; $page <= $end; $page++): ?>
                        <li class="page-item <?= $page === $current ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $buildUrl($page) ?>"><?= $page ?></a>
                        </li>
                    <?php endfor; ?>
    
                    <?php if ($end < $last): ?>
                        <?php if ($end < $last - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $buildUrl($last) ?>"><?= $last ?></a>
                        </li>
                    <?php endif; ?>
    
                    <li class="page-item <?= $current >= $last ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $current >= $last ? '#' : $buildUrl($current + 1) ?>" aria-disabled="<?= $current >= $last ? 'true' : 'false' ?>">
                            &raquo;
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
<?php endif; ?>
