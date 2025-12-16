<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\GaleriaCategoriaModel;
use App\Models\GaleriaImagemModel;

class GaleriaController extends Controller
{
    private GaleriaImagemModel $imagemModel;
    private GaleriaCategoriaModel $categoriaModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->imagemModel = new GaleriaImagemModel();
        $this->categoriaModel = new GaleriaCategoriaModel();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('gallery:list');
        $request = Request::capture();
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 15;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);

        $busca = trim((string) $request->query('q', ''));
        $categoriaRaw = $request->query('categoria');
        $categoriaId = $this->extractCategoriaId($categoriaRaw);

        $result = $this->imagemModel->paginar($page, $perPage, $busca !== '' ? $busca : null, $categoriaId);
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/galeria',
            'query' => array_filter([
                'q' => $busca,
                'categoria' => $categoriaId,
                'per_page' => ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $perPageOptions = PaginationHelper::options($defaultPerPage);

        $this->renderTwig('admin/galeria/index', array_merge([
            'imagens' => $result['data'],
            'categorias' => $this->categoriaModel->listar(),
            'filters' => [
                'q' => $busca,
                'categoria' => $categoriaId,
            ],
            'pagination' => $pagination,
            'perPageOptions' => $perPageOptions,
            'perPageSelection' => $perPageSelection,
        ], AdminHelper::getUserData('galeria')));
    }

    public function cadastrar(): void
    {
        PermissionMiddleware::authorize('gallery:create');
        $this->renderTwig('admin/galeria/cadastrar', array_merge([
            'categorias' => $this->categoriaModel->listar(),
        ], AdminHelper::getUserData('galeria')));
    }

    public function salvar(): void
    {
        PermissionMiddleware::authorize('gallery:create');
        $request = Request::capture();
        $payload = $this->validateImagemPayload($request);

        if ($payload === null) {
            $this->redirect('admin/galeria/cadastrar');
        }

        $this->imagemModel->salvar($payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Imagem adicionada com sucesso!'];
        $this->redirect('admin/galeria');
    }

    public function editar(int $id): void
    {
        PermissionMiddleware::authorize('gallery:edit');
        $imagem = $this->imagemModel->buscar($id);

        if (!$imagem) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Imagem não encontrada.'];
            $this->redirect('admin/galeria');
        }

        $this->renderTwig('admin/galeria/editar', array_merge([
            'imagem' => $imagem,
            'categorias' => $this->categoriaModel->listar(),
        ], AdminHelper::getUserData('galeria')));
    }

    public function atualizar(int $id): void
    {
        PermissionMiddleware::authorize('gallery:edit');
        $request = Request::capture();
        $payload = $this->validateImagemPayload($request);

        if ($payload === null) {
            $this->redirect("admin/galeria/editar/{$id}");
        }

        if (!$this->imagemModel->buscar($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Imagem não encontrada.'];
            $this->redirect('admin/galeria');
        }

        $this->imagemModel->atualizar($id, $payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Imagem atualizada com sucesso!'];
        $this->redirect('admin/galeria');
    }

    public function deletar(int $id): void
    {
        PermissionMiddleware::authorize('gallery:delete');
        $imagem = $this->imagemModel->buscar($id);

        if (!$imagem) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Imagem não encontrada.'];
            $this->redirect('admin/galeria');
        }

        $this->imagemModel->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Imagem removida com sucesso.'];
        $this->redirect('admin/galeria');
    }

    private function validateImagemPayload(Request $request): ?array
    {
        $titulo = trim((string) $request->post('titulo', ''));
        $descricao = trim((string) $request->post('descricao', ''));
        $url = trim((string) $request->post('url', ''));
        $categoriaId = (int) $request->post('categoria_id', 0);

        if ($titulo === '' || $url === '' || $categoriaId <= 0) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Preencha os campos obrigatórios (categoria, título e URL).'];
            return null;
        }

        if (!$this->categoriaModel->buscar($categoriaId)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Categoria selecionada não é válida.'];
            return null;
        }

        return [
            'category_id' => $categoriaId,
            'titulo' => $titulo,
            'descricao' => $descricao !== '' ? $descricao : null,
            'url' => $url,
        ];
    }

    private function extractCategoriaId($raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            $id = (int) $raw;
            return $id > 0 ? $id : null;
        }

        $categoria = $this->categoriaModel->buscarPorSlug((string) $raw);
        return $categoria ? (int) $categoria['id'] : null;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
