<?php

namespace App\Helpers;

class PaginationHelper
{
    private const PAGE_SIZES = [5, 10, 15, 20, 25, 50, 100];

    /**
     * Resolve a querystring per_page value into an integer (or null for "all")
     * and return both the numeric value used by the paginator and the raw
     * selection that should be persisted back to the UI.
     */
    public static function resolve(?string $rawValue, int $defaultPerPage): array
    {
        $normalized = $rawValue !== null ? strtolower(trim($rawValue)) : '';

        if ($normalized === '' || $normalized === '0') {
            return [$defaultPerPage, (string) $defaultPerPage];
        }

        if ($normalized === 'all' || $normalized === 'todos') {
            return [null, 'all'];
        }

        if (ctype_digit($normalized)) {
            $numeric = (int) $normalized;
            if (in_array($numeric, self::PAGE_SIZES, true) || $numeric === $defaultPerPage) {
                return [$numeric, (string) $numeric];
            }
        }

        return [$defaultPerPage, (string) $defaultPerPage];
    }

    /**
     * Build the combo-box option list ensuring the requested default value is available.
     */
    public static function options(int $defaultPerPage): array
    {
        $sizes = self::PAGE_SIZES;
        if (!in_array($defaultPerPage, $sizes, true)) {
            $sizes[] = $defaultPerPage;
            sort($sizes);
        }

        $options = [];
        $seen = [];
        foreach ($sizes as $size) {
            if (isset($seen[$size])) {
                continue;
            }
            $options[] = [
                'value' => (string) $size,
                'label' => (string) $size,
            ];
            $seen[$size] = true;
        }

        $options[] = [
            'value' => 'all',
            'label' => 'Todos',
        ];

        return $options;
    }
}
