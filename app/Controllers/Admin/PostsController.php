<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\Validator;
use App\Models\Post;
use App\Models\PostCategoryModel;

class PostsController extends Controller {

    private Post $post;
    private PostCategoryModel $categories;

    public function __construct() {
        $this->post = new Post();
        $this->categories = new PostCategoryModel();
        
        // Protege todas as rotas do controller
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $q = $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;

        $result = $this->post->paginar($page, $perPage, $q ?: null);
        $posts = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/posts',
            'query' => array_filter([
                'q' => $q,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/posts/index', array_merge([
            'posts' => $posts,
            'q' => $q,
            'pagination' => $pagination
        ], AdminHelper::getUserData('posts')));
    }

    public function create(): void {
        // Valida permissão
        PermissionMiddleware::authorize('posts:create');

        $categorias = $this->categories->listar();
        $csrf = CsrfHelper::generateToken();
        $old = $_SESSION['old_input'] ?? [];
        
        $this->renderTwig('admin/posts/create', array_merge([
            'categorias' => $categorias,
            'csrf_token' => $csrf,
            'old' => $old
        ], AdminHelper::getUserData('posts')));
    }

    public function store(): void {
        // Valida permissão
        PermissionMiddleware::authorize('posts:create');

        $request = Request::capture();
        
        if (!$request->isPost()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => '⚠️ Método inválido para criar post!'];
            header('Location: ' . BASE_URL . 'admin/posts/create');
            exit;
        }
        
        // Valida CSRF
        CsrfHelper::verifyOrDie();
        
        $payload = $request->post();
        if (isset($payload['capa_url']) && trim($payload['capa_url']) === '') {
            unset($payload['capa_url']);
        }

        // Valida dados
        $validator = Validator::make($payload, [
            'titulo' => 'required|min:5|max:200',
            'slug' => 'required|min:3|max:200',
            'conteudo' => 'required|min:10',
            'categoria_id' => 'required|integer',
            'capa_url' => 'max:512'
        ]);
        
        if ($validator->fails()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro de validação: ' . implode(', ', array_map(fn($e) => $e[0], $validator->errors()))];
            $_SESSION['old_input'] = $request->post();
            header('Location: ' . BASE_URL . 'admin/posts/create');
            exit;
        }
        
        $validated = $validator->validated();
        $rawConteudo = $request->post('conteudo', '');

        $data = [
            'titulo' => $validated['titulo'] ?? '',
            'slug' => $validated['slug'] ?? '',
            'conteudo' => $rawConteudo,
            'categoria_id' => isset($validated['categoria_id']) ? (int)$validated['categoria_id'] : (int)$request->post('categoria_id', 0),
        ];
        $data['capa_url'] = $validated['capa_url'] ?? null;
        $data['autor'] = AuthMiddleware::getUserData('name') ?? $_SESSION['user_name'] ?? 'admin';

        $this->post->criar($data);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post criado com sucesso!'];
        unset($_SESSION['old_input']);

        $action = $request->post('action', 'save_exit');
        $redirect = $action === 'save_continue'
            ? BASE_URL . 'admin/posts/create'
            : BASE_URL . 'admin/posts';

        header('Location: ' . $redirect);
        exit;
    }

    public function edit(int $id): void {
        // Valida permissão
        PermissionMiddleware::authorize('posts:edit');

        $post = $this->post->encontrarPorId($id);
        $categorias = $this->categories->listar();
        $csrf = CsrfHelper::generateToken();
        $old = $_SESSION['old_input'] ?? [];
        
        $this->renderTwig('admin/posts/edit', array_merge([
            'post' => $post,
            'categorias' => $categorias,
            'csrf_token' => $csrf,
            'old' => $old
        ], AdminHelper::getUserData('posts')));
    }

    public function update(int $id): void {
        $request = Request::capture();
        
        if (!$request->isPost()) {
            return;
        }
        
        // Valida CSRF
        CsrfHelper::verifyOrDie();
        
        $payload = $request->post();
        if (isset($payload['capa_url']) && trim($payload['capa_url']) === '') {
            unset($payload['capa_url']);
        }

        // Valida dados
        $validator = Validator::make($payload, [
            'titulo' => 'required|min:5|max:200',
            'slug' => 'required|min:3|max:200',
            'conteudo' => 'required|min:10',
            'categoria_id' => 'required|integer',
            'capa_url' => 'max:512'
        ]);
        
        if ($validator->fails()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro de validação: ' . implode(', ', array_map(fn($e) => $e[0], $validator->errors()))];
            $_SESSION['old_input'] = $request->post();
            header('Location: ' . BASE_URL . 'admin/posts/edit/' . $id);
            exit;
        }
        
        $validated = $validator->validated();
        $rawConteudo = $request->post('conteudo', '');

        $data = [
            'titulo' => $validated['titulo'] ?? '',
            'slug' => $validated['slug'] ?? '',
            'conteudo' => $rawConteudo,
            'categoria_id' => isset($validated['categoria_id']) ? (int)$validated['categoria_id'] : (int)$request->post('categoria_id', 0),
        ];
        $data['capa_url'] = $validated['capa_url'] ?? null;

        $this->post->atualizar($id, $data);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post atualizado com sucesso!'];
        unset($_SESSION['old_input']);
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function delete(int $id): void {
        // Valida permissão
        PermissionMiddleware::authorize('posts:delete');

        $this->post->excluir($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post excluído com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }
}
