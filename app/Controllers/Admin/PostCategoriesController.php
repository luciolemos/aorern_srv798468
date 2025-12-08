<?php

namespace App\Controllers\Admin;

use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\PostCategoryModel;

class PostCategoriesController extends Controller
{
    private PostCategoryModel $model;
    private const DEFAULT_COLOR = '#df6301';

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
        $perPage = 10;

        $result = $this->model->paginar($page, $perPage, $q ?: null);
        $categorias = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/post-categories',
            'query' => array_filter([
                'q' => $q,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/post_categories/index', array_merge(compact('categorias', 'q', 'pagination'), AdminHelper::getUserData('post-categories')));
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
        $color = $this->sanitizeColor($request->post('badge_color'));

        $dados = [
            'staff_id' => $request->post('staff_id', 'POSTCAT-' . date('YmdHis')),
            'nome' => trim($request->post('nome', '')),
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
        $color = $this->sanitizeColor($request->post('badge_color'));

        $dados = [
            'nome' => trim($request->post('nome', '')),
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
