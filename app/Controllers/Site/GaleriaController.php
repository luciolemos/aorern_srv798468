<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Models\GaleriaCategoriaModel;
use App\Models\GaleriaImagemModel;

class GaleriaController extends Controller
{
    private GaleriaImagemModel $imagemModel;
    private GaleriaCategoriaModel $categoriaModel;

    public function __construct()
    {
        $this->imagemModel = new GaleriaImagemModel();
        $this->categoriaModel = new GaleriaCategoriaModel();
    }

    public function index(): void
    {
        $request = Request::capture();
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 8;
        $busca = trim((string) $request->query('q', ''));
        $categoriaParam = trim((string) $request->query('categoria', ''));
        [$categoriaId, $categoriaSelecionada] = $this->resolveCategoria($categoriaParam);

        $filtroBusca = $busca !== '' ? $busca : null;
        $result = $this->imagemModel->paginar($page, $perPage, $filtroBusca, $categoriaId);
        $query = array_filter([
            'q' => $busca,
            'categoria' => $categoriaParam !== '' ? $categoriaParam : null,
        ], fn($value) => $value !== null && $value !== '');

        $this->renderTwig('site/pages/galeria', [
            'imagens' => $result['data'],
            'categorias' => $this->categoriaModel->listar(),
            'pagination' => array_merge($result['meta'], [
                'path' => BASE_URL . 'galeria',
                'query' => $query,
            ]),
            'filters' => [
                'q' => $busca,
                'categoria' => $categoriaParam,
            ],
            'categoriaSelecionada' => $categoriaSelecionada,
            'perPage' => $perPage,
        ]);
    }

    private function resolveCategoria(string $raw): array
    {
        if ($raw === '') {
            return [null, null];
        }

        if (ctype_digit($raw)) {
            $id = (int) $raw;
            if ($id > 0) {
                $categoria = $this->categoriaModel->buscar($id);
                return $categoria ? [$id, $categoria] : [null, null];
            }
            return [null, null];
        }

        $categoria = $this->categoriaModel->buscarPorSlug($raw);
        if ($categoria) {
            return [(int) $categoria['id'], $categoria];
        }

        return [null, null];
    }
}
