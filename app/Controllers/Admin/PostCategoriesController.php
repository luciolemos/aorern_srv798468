<?php

namespace App\Controllers\Admin;

use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\PostCategoryModel;

class PostCategoriesController extends Controller
{
    private PostCategoryModel $model;
    private const DEFAULT_COLOR = '#556b2f';

    public function __construct()
    {
        $this->model = new PostCategoryModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void
    {
        $request = Request::capture();
        $q = $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $result = $this->model->paginar($page, $perPage, $q ?: null);
        $categorias = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/post-categories',
            'query' => array_filter([
                'q' => $q,
                'per_page' => $perPageQueryValue,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $perPageOptions = PaginationHelper::options($defaultPerPage);

        $this->renderTwig('admin/post_categories/index', array_merge(
            compact('categorias', 'q', 'pagination', 'perPageOptions', 'perPageSelection'),
            AdminHelper::getUserData('post-categories')
        ));
    }

    public function create(): void
    {
        PermissionMiddleware::authorize('post_categories:create');
        $csrf = CsrfHelper::generateToken();
        $this->renderTwig('admin/post_categories/create', array_merge(['csrf_token' => $csrf], AdminHelper::getUserData('post-categories')));
    }

    public function store(): void
    {
        PermissionMiddleware::authorize('post_categories:create');
        $request = Request::capture();
        CsrfHelper::verifyOrDie();
        $staffId = $request->post('staff_id', 'POSTCAT-' . date('YmdHis'));
        $nome = trim($request->post('nome', ''));
        $slugInput = trim($request->post('slug', ''));
        $color = $this->sanitizeColor($request->post('badge_color'));

        $dados = [
            'staff_id' => $staffId,
            'nome' => $nome,
            'slug' => $this->generateSlug($slugInput !== '' ? $slugInput : $nome, $staffId),
            'badge_color' => $color,
            'descricao' => trim($request->post('descricao', ''))
        ];
        $this->model->salvar($dados);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria criada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/post-categories');
        exit;
    }

    public function edit(int $id): void
    {
        PermissionMiddleware::authorize('post_categories:edit');
        $categoria = $this->model->buscar($id);
        if (!$categoria) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Categoria não encontrada.'];
            header('Location: ' . BASE_URL . 'admin/post-categories');
            exit;
        }
        $csrf = CsrfHelper::generateToken();
        $this->renderTwig('admin/post_categories/edit', array_merge(compact('categoria'), ['csrf_token' => $csrf], AdminHelper::getUserData('post-categories')));
    }

    public function update(int $id): void
    {
        PermissionMiddleware::authorize('post_categories:edit');
        $request = Request::capture();
        CsrfHelper::verifyOrDie();
        $categoria = $this->model->buscar($id);
        if (!$categoria) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Categoria não encontrada.'];
            header('Location: ' . BASE_URL . 'admin/post-categories');
            exit;
        }

        $nome = trim($request->post('nome', ''));
        $slugInput = trim($request->post('slug', ''));
        $color = $this->sanitizeColor($request->post('badge_color'));

        $dados = [
            'nome' => $nome,
            'slug' => $this->generateSlug($slugInput !== '' ? $slugInput : $nome, $categoria['staff_id'] ?? null, $categoria['slug'] ?? null),
            'badge_color' => $color,
            'descricao' => trim($request->post('descricao', ''))
        ];
        $this->model->atualizar($id, $dados);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria atualizada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/post-categories');
        exit;
    }

    public function destroy(int $id): void
    {
        PermissionMiddleware::authorize('post_categories:delete');
        if ($this->model->possuiPosts($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não é possível excluir: existem posts vinculados a esta categoria.'];
            header('Location: ' . BASE_URL . 'admin/post-categories');
            exit;
        }

        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria removida com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/post-categories');
        exit;
}

    private function generateSlug(?string $value, ?string $fallback = null, ?string $preserve = null): string
    {
        $base = trim((string)($value ?? ''));

        if ($base === '' && $preserve) {
            return $preserve;
        }

        if ($base === '') {
            $base = trim((string)($fallback ?? ''));
        }

        if ($base === '') {
            return strtolower('cat-' . uniqid());
        }

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
        if ($transliterated === false || $transliterated === null) {
            $transliterated = $base;
        }

        $slug = strtolower($transliterated);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug ?? '');
        $slug = trim((string)$slug, '-');

        if ($slug === '' && $preserve) {
            return $preserve;
        }

        if ($slug === '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', (string)($fallback ?? '')));
            $slug = trim($slug, '-');
        }

        if ($slug === '') {
            $slug = strtolower('cat-' . uniqid());
        }

        return substr($slug, 0, 140);
    }

    private function sanitizeColor(?string $color): string
    {
        $color = trim((string) $color);
        if ($color === '') {
            return self::DEFAULT_COLOR;
        }
        if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
            return self::DEFAULT_COLOR;
        }
        return strtoupper($color);
    }
}
