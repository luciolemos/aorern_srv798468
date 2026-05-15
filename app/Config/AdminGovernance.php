<?php

namespace App\Config;

class AdminGovernance
{
    private const LEGACY_SUB_ROUTES = [
        'equipamentos',
        'categorias',
        'dog-breeds',
        'dogs',
        'livro-ocorrencias',
        'livro-tipos',
        'obras',
    ];

    private const LEGACY_NAV_ITEMS = [
        [
            'sub_route' => 'equipamentos',
            'url' => 'admin/equipamentos',
            'icon' => 'bi bi-tools',
            'label' => 'Equipamentos',
            'permission' => 'equipamentos:list',
        ],
        [
            'sub_route' => 'categorias',
            'url' => 'admin/categorias',
            'icon' => 'bi bi-tags',
            'label' => 'Categorias de equipamentos',
            'permission' => 'categorias:list',
        ],
        [
            'sub_route' => 'obras',
            'url' => 'admin/obras',
            'icon' => 'bi bi-cone-striped',
            'label' => 'Obras',
            'permission' => 'obras:list',
        ],
        [
            'sub_route' => 'livro-ocorrencias',
            'url' => 'admin/livro-ocorrencias',
            'icon' => 'bi bi-journal-medical',
            'label' => 'Livro de ocorrencias',
            'permission' => 'livro_ocorrencias:list',
        ],
        [
            'sub_route' => 'livro-tipos',
            'url' => 'admin/livro-tipos',
            'icon' => 'bi bi-card-checklist',
            'label' => 'Tipos de ocorrencia',
            'permission' => 'livro_tipos:list',
        ],
        [
            'sub_route' => 'dog-breeds',
            'url' => 'admin/dog-breeds',
            'icon' => 'bi bi-shield-check',
            'label' => 'Racas K9',
            'permission' => 'dog_breeds:list',
        ],
        [
            'sub_route' => 'dogs',
            'url' => 'admin/dogs',
            'icon' => 'bi bi-shield-plus',
            'label' => 'Caes K9',
            'permission' => 'dogs:list',
        ],
    ];

    public static function isLegacyModulesEnabled(): bool
    {
        $raw = strtolower(trim((string) ($_ENV['FEATURE_LEGACY_ADMIN_MODULES'] ?? '0')));
        return in_array($raw, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return string[]
     */
    public static function legacySubRoutes(): array
    {
        return self::LEGACY_SUB_ROUTES;
    }

    /**
     * @return array<int, array{sub_route:string,url:string,icon:string,label:string,permission:string}>
     */
    public static function legacyNavItemsForRole(string $role): array
    {
        if (!self::isLegacyModulesEnabled()) {
            return [];
        }

        $items = [];
        foreach (self::LEGACY_NAV_ITEMS as $item) {
            if (Permissions::has($role, $item['permission'])) {
                $items[] = $item;
            }
        }

        return $items;
    }
}

