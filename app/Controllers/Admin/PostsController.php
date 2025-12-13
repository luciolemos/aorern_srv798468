<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Helpers\Validator;
use App\Models\Post;
use App\Models\PostCategoryModel;

class PostsController extends Controller {

    private Post $post;
    private PostCategoryModel $categories;

    public function __construct() {
        $this->post = new Post();
        $this->categories = new PostCategoryModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $q = $request->query('q', '');
        $status = $request->query('status', '');
        $category = $request->query('category', '');
        $author = $request->query('author', '');
        $visibility = $request->query('visibility', ''); // 'visible' ou 'hidden'
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $userData = AdminHelper::getUserData('posts');
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? 'usuario';

        // Construir query base
        $allowedStatuses = ($userRole === 'usuario') 
            ? [] 
            : ['pending', 'published'];

        // Para usuários, só mostram seus próprios posts
        if ($userRole === 'usuario') {
            $result = $this->post->paginarPorAutor(
                $userId, 
                $page, 
                $perPage, 
                $q ?: null,
                $status ?: null,
                $category ?: null,
                $visibility ?: null
            );
        } else {
            // Admin/Gerente com filtros avançados
            $result = $this->post->paginarComFiltros(
                $page,
                $perPage,
                $q ?: null,
                $status ?: null,
                $category ?: null,
                $author ?: null,
                $visibility ?: null
            );
        }

        $posts = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/posts',
            'query' => array_filter([
                'q' => $q,
                'status' => $status,
                'category' => $category,
                'author' => $author,
                'visibility' => $visibility,
                'per_page' => $perPageQueryValue,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        // Buscar todos os autores (usuários que têm posts)
        $allAuthors = $this->post->listarAutoresUnicos();
        $categories = $this->categories->listar();
        $perPageOptions = PaginationHelper::options($defaultPerPage);

        $this->renderTwig('admin/posts/index', array_merge([
            'posts' => $posts,
            'q' => $q,
            'status' => $status,
            'category' => $category,
            'author' => $author,
            'visibility' => $visibility,
            'pagination' => $pagination,
            'perPageOptions' => $perPageOptions,
            'perPageSelection' => $perPageSelection,
            'userRole' => $userRole,
            'user_id' => $userId,
            'csrf_token' => CsrfHelper::generateToken(),
            'allAuthors' => $allAuthors,
            'categories' => $categories,
            'statusLabels' => [
                'draft' => 'Rascunho',
                'pending' => 'Pendente de Revisão',
                'in_review' => 'Em Revisão',
                'published' => 'Publicado',
                'rejected' => 'Rejeitado'
            ]
        ], $userData));
    }

    public function create(): void {
        PermissionMiddleware::authorize('posts:create');

        $categorias = $this->categories->listar();
        $csrf = CsrfHelper::generateToken();
        $old = $_SESSION['old_input'] ?? [];
        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);
        
        $this->renderTwig('admin/posts/create', array_merge([
            'categorias' => $categorias,
            'csrf_token' => $csrf,
            'old' => $old,
            'validationErrors' => $validationErrors,
        ], AdminHelper::getUserData('posts')));
    }

    public function store(): void {
        PermissionMiddleware::authorize('posts:create');

        $request = Request::capture();
        
        if (!$request->isPost()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Método inválido.'];
            header('Location: ' . BASE_URL . 'admin/posts/create');
            exit;
        }
        
        CsrfHelper::verifyOrDie();
        
        $payload = $request->post();
        if (isset($payload['capa_url']) && trim($payload['capa_url']) === '') {
            unset($payload['capa_url']);
        }
        $rawConteudoInput = $payload['conteudo'] ?? '';
        $payload['conteudo'] = trim(strip_tags($rawConteudoInput));

        $validator = Validator::make($payload, [
            'titulo' => 'required|min:5|max:200',
            'slug' => 'required|min:3|max:200',
            'conteudo' => 'required|min:10',
            'categoria_id' => 'required|integer',
            'capa_url' => 'max:512'
        ]);
        
        if ($validator->fails()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro de validação. Confira os campos destacados.'];
            $_SESSION['old_input'] = $request->post();
            $_SESSION['validation_errors'] = $validator->errors();
            header('Location: ' . BASE_URL . 'admin/posts/create');
            exit;
        }
        
        $validated = $validator->validated();
        $rawConteudo = $rawConteudoInput;
        $action = $request->post('action', 'save_exit');

        $userRole = $_SESSION['user_role'] ?? 'usuario';
        if ($action === 'save_draft') {
            $status = 'draft';
        } elseif ($action === 'save_submit') {
            $status = 'pending';
        } else {
            $status = $userRole === 'admin' ? 'pending' : 'draft';
        }

        $data = [
            'titulo' => $validated['titulo'] ?? '',
            'slug' => $validated['slug'] ?? '',
            'conteudo' => $rawConteudo,
            'categoria_id' => (int)$validated['categoria_id'],
            'capa_url' => $validated['capa_url'] ?? null,
            'user_id' => $_SESSION['user_id'] ?? null,
            'status' => $status,
        ];

        $this->post->criar($data);

        $message = match($status) {
            'draft' => 'Post salvo como rascunho.',
            'pending' => 'Post submetido para revisão.',
            default => 'Post criado com sucesso.',
        };

        $_SESSION['toast'] = ['type' => 'success', 'message' => $message];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        $redirect = $action === 'save_continue'
            ? BASE_URL . 'admin/posts/create'
            : BASE_URL . 'admin/posts';

        header('Location: ' . $redirect);
        exit;
    }

    public function edit(int $id): void {
        PermissionMiddleware::authorize('posts:edit');

        $post = $this->post->encontrarPorId($id);
        
        if (!$post) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Post não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        if (!$this->canEditPost($post)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Você não pode editar este post.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $categorias = $this->categories->listar();
        $csrf = CsrfHelper::generateToken();
        $old = $_SESSION['old_input'] ?? [];
        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);
        
        $this->renderTwig('admin/posts/edit', array_merge([
            'post' => $post,
            'categorias' => $categorias,
            'csrf_token' => $csrf,
            'old' => $old,
            'validationErrors' => $validationErrors,
        ], AdminHelper::getUserData('posts')));
    }

    public function update(int $id): void {
        $request = Request::capture();
        
        if (!$request->isPost()) {
            return;
        }
        
        CsrfHelper::verifyOrDie();
        
        $post = $this->post->encontrarPorId($id);
        if (!$post || !$this->canEditPost($post)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Permissão negada.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $payload = $request->post();
        if (isset($payload['capa_url']) && trim($payload['capa_url']) === '') {
            unset($payload['capa_url']);
        }
        $rawConteudoInput = $payload['conteudo'] ?? '';
        $payload['conteudo'] = trim(strip_tags($rawConteudoInput));

        $validator = Validator::make($payload, [
            'titulo' => 'required|min:5|max:200',
            'slug' => 'required|min:3|max:200',
            'conteudo' => 'required|min:10',
            'categoria_id' => 'required|integer',
            'capa_url' => 'max:512'
        ]);
        
        if ($validator->fails()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Erro de validação. Confira os campos destacados.'];
            $_SESSION['old_input'] = $request->post();
            $_SESSION['validation_errors'] = $validator->errors();
            header('Location: ' . BASE_URL . 'admin/posts/edit/' . $id);
            exit;
        }
        
        $validated = $validator->validated();
        $rawConteudo = $rawConteudoInput;
        $action = $request->post('action', 'save_update');

        $data = [
            'titulo' => $validated['titulo'] ?? '',
            'slug' => $validated['slug'] ?? '',
            'conteudo' => $rawConteudo,
            'categoria_id' => (int)$validated['categoria_id'],
            'capa_url' => $validated['capa_url'] ?? null,
        ];

        // Atualizar status baseado na ação
        if ($action === 'save_draft') {
            $data['status'] = 'draft';
            $data['reject_reason'] = null; // Limpa motivo de rejeição ao salvar como rascunho
        } elseif ($action === 'save_submit') {
            $data['status'] = 'pending';
            $data['reject_reason'] = null; // Limpa motivo de rejeição ao submeter
        }
        // Se action === 'save_update', mantém o status atual (não altera)

        $this->post->atualizar($id, $data);

        $message = match($action) {
            'save_draft' => 'Post salvo como rascunho.',
            'save_submit' => 'Post submetido para revisão.',
            default => 'Post atualizado com sucesso.',
        };

        $_SESSION['toast'] = ['type' => 'success', 'message' => $message];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function submit(int $id): void {
        PermissionMiddleware::authorize('posts:submit');

        $post = $this->post->encontrarPorId($id);
        
        if (!$post || $post['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Permissão negada.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        if ($post['status'] !== 'draft') {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Apenas rascunhos podem ser submetidos.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $this->post->atualizar($id, ['status' => 'pending']);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post submetido para revisão.'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function approve(int $id): void {
        PermissionMiddleware::authorize('posts:approve');

        $post = $this->post->encontrarPorId($id);
        if (!$post || $post['status'] !== 'pending') {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Post inválido para aprovação.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $this->post->atualizar($id, [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post publicado com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function hide(int $id): void {
        PermissionMiddleware::authorize('posts:approve');

        $post = $this->post->encontrarPorId($id);
        if (!$post || $post['status'] !== 'published') {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Post inválido para ocultar.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $this->post->atualizar($id, [
            'is_hidden' => 1,
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post ocultado do blog (continua publicado).'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function show(int $id): void {
        PermissionMiddleware::authorize('posts:approve');

        $post = $this->post->encontrarPorId($id);
        if (!$post || $post['status'] !== 'published') {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Post inválido para exibir.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $this->post->atualizar($id, [
            'is_hidden' => 0,
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post visível novamente no blog.'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function unpublish(int $id): void {
        PermissionMiddleware::authorize('posts:approve');

        $post = $this->post->encontrarPorId($id);
        if (!$post || $post['status'] !== 'published') {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Post inválido para despublicação.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $this->post->atualizar($id, [
            'status' => 'draft',
            'reject_reason' => null,
            'published_at' => null,
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post despublicado e salvo como rascunho.'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function reject(int $id): void {
        PermissionMiddleware::authorize('posts:reject');

        $request = Request::capture();
        
        if (!$request->isPost()) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Método inválido.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        CsrfHelper::verifyOrDie();

        $post = $this->post->encontrarPorId($id);
        if (!$post) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Post não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $reason = trim($request->post('reject_reason', ''));
        if (empty($reason)) {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Informe um motivo para rejeição.'];
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }

        $this->post->atualizar($id, [
            'status' => 'rejected',
            'reject_reason' => $reason
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post rejeitado.'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    public function delete(int $id): void {
        PermissionMiddleware::authorize('posts:delete');

        $this->post->excluir($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Post excluído.'];
        header('Location: ' . BASE_URL . 'admin/posts');
        exit;
    }

    private function canEditPost(array $post): bool {
        $userRole = $_SESSION['user_role'] ?? 'usuario';
        $userId = $_SESSION['user_id'] ?? null;

        if ($userRole === 'admin') {
            return true;
        }

        if ($userRole === 'gerente' && in_array($post['status'], ['draft', 'pending'])) {
            return true;
        }

        // Usuário pode editar seus próprios rascunhos e posts rejeitados
        if ($post['user_id'] == $userId && in_array($post['status'], ['draft', 'rejected'])) {
            return true;
        }

        return false;
    }
}
