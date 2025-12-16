<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\GaleriaCategoriaModel;

class GaleriaCategoriasController extends Controller
{
    private GaleriaCategoriaModel $model;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->model = new GaleriaCategoriaModel();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('gallery_categories:list');
        $request = Request::capture();
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);

        $busca = trim((string) $request->query('q', ''));
        $result = $this->model->paginar($page, $perPage, $busca !== '' ? $busca : null);

        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/galeria-categorias',
            'query' => array_filter([
                'q' => $busca,
                'per_page' => ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $perPageOptions = PaginationHelper::options($defaultPerPage);

        $this->renderTwig('admin/galeria/categorias/index', array_merge([
            'categorias' => $result['data'],
            'filters' => [
                'q' => $busca,
            ],
            'pagination' => $pagination,
            'perPageOptions' => $perPageOptions,
            'perPageSelection' => $perPageSelection,
        ], AdminHelper::getUserData('galeria-categorias')));
    }

    public function cadastrar(): void
    {
        PermissionMiddleware::authorize('gallery_categories:create');
        $this->renderTwig('admin/galeria/categorias/cadastrar', AdminHelper::getUserData('galeria-categorias'));
    }

    public function salvar(): void
    {
        PermissionMiddleware::authorize('gallery_categories:create');
        $request = Request::capture();
        $payload = $this->prepareCategoriaPayload($request);

        if ($payload === null) {
            $this->redirect('admin/galeria-categorias/cadastrar');
        }

        $this->model->salvar($payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria criada com sucesso!'];
        $this->redirect('admin/galeria-categorias');
    }

    public function editar(int $id): void
    {
        PermissionMiddleware::authorize('gallery_categories:edit');
        $categoria = $this->model->buscar($id);

        if (!$categoria) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Categoria não encontrada.'];
            $this->redirect('admin/galeria-categorias');
        }

        $this->renderTwig('admin/galeria/categorias/editar', array_merge([
            'categoria' => $categoria,
        ], AdminHelper::getUserData('galeria-categorias')));
    }

    public function atualizar(int $id): void
    {
        PermissionMiddleware::authorize('gallery_categories:edit');
        $request = Request::capture();
        $payload = $this->prepareCategoriaPayload($request, $id);

        if ($payload === null) {
            $this->redirect("admin/galeria-categorias/editar/{$id}");
        }

        if (!$this->model->buscar($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Categoria não encontrada.'];
            $this->redirect('admin/galeria-categorias');
        }

        $this->model->atualizar($id, $payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria atualizada com sucesso!'];
        $this->redirect('admin/galeria-categorias');
    }

    public function deletar(int $id): void
    {
        PermissionMiddleware::authorize('gallery_categories:delete');
        $categoria = $this->model->buscar($id);

        if (!$categoria) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Categoria não encontrada.'];
            $this->redirect('admin/galeria-categorias');
        }

        if ($this->model->possuiImagens($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Remova ou reatribua as imagens antes de excluir a categoria.'];
            $this->redirect('admin/galeria-categorias');
        }

        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria removida com sucesso.'];
        $this->redirect('admin/galeria-categorias');
    }

    private function prepareCategoriaPayload(Request $request, ?int $ignoreId = null): ?array
    {
        $nome = trim((string) $request->post('nome', ''));
        $slugInput = trim((string) $request->post('slug', ''));
        $color = trim((string) $request->post('color', ''));

        if ($nome === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe um nome para a categoria.'];
            return null;
        }

        $baseSlug = $slugInput !== '' ? $slugInput : $nome;
        $slug = $this->ensureUniqueSlug($this->slugify($baseSlug), $ignoreId);

        return [
            'nome' => $nome,
            'slug' => $slug,
            'color' => $color !== '' ? $color : null,
        ];
    }

    private function slugify(string $value): string
    {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        if ($transliterated === false) {
            $transliterated = $value;
        }

        $normalized = strtolower(preg_replace('/[^a-z0-9]+/', '-', $transliterated));
        $normalized = trim($normalized, '-');

        return $normalized !== '' ? $normalized : 'categoria-' . substr(md5((string) microtime(true)), 0, 6);
    }

    private function ensureUniqueSlug(string $desired, ?int $ignoreId = null): string
    {
        $slug = $desired;
        $counter = 1;

        while ($existing = $this->model->buscarPorSlug($slug)) {
            if ($ignoreId !== null && (int) $existing['id'] === $ignoreId) {
                break;
            }

            $slug = $desired . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
