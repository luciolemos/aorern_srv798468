<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\LivroTipoModel;

class LivroTiposController extends Controller
{
    private LivroTipoModel $model;

    private const STATUS_OPTIONS = [
        'todos' => 'Todos',
        'ativos' => 'Somente ativos',
        'inativos' => 'Somente inativos',
    ];

    public function __construct()
    {
        $this->model = new LivroTipoModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('livro_tipos:list');
        $request = Request::capture();
        $page = max(1, (int) $request->query('page', 1));
        $filters = [
            'q' => trim($request->query('q', '')),
            'status' => $request->query('status', 'todos'),
        ];

        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $statusValue = match ($filters['status']) {
            'ativos' => 1,
            'inativos' => 0,
            default => null,
        };

        $result = $this->model->paginar($page, $perPage, $filters['q'] ?: null, $statusValue);
        $tipos = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/livro-tipos',
            'query' => array_filter([
                'q' => $filters['q'],
                'status' => $filters['status'] !== 'todos' ? $filters['status'] : null,
                'per_page' => $perPageQueryValue,
            ], fn ($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/livro_tipos/index', array_merge([
            'tipos' => $tipos,
            'filters' => $filters,
            'statusOptions' => self::STATUS_OPTIONS,
            'pagination' => $pagination,
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
        ], AdminHelper::getUserData('livro-tipos')));
    }

    public function create(): void
    {
        PermissionMiddleware::authorize('livro_tipos:create');
        $old = $_SESSION['old_input'] ?? null;
        unset($_SESSION['old_input']);

        $this->renderTwig('admin/livro_tipos/create', array_merge([
            'csrf_token' => CsrfHelper::generateToken(),
            'old' => $old,
        ], AdminHelper::getUserData('livro-tipos')));
    }

    public function store(): void
    {
        PermissionMiddleware::authorize('livro_tipos:create');
        $request = Request::capture();
        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/livro-tipos/create');
            exit;
        }

        CsrfHelper::verifyOrDie();
        $input = $request->post();
        $payload = $this->validatePayload($input);

        if ($payload === null) {
            $_SESSION['old_input'] = $input;
            $_SESSION['toast'] = ['type' => 'danger', 'message' => $_SESSION['form_error'] ?? 'Verifique os dados informados.'];
            unset($_SESSION['form_error']);
            header('Location: ' . BASE_URL . 'admin/livro-tipos/create');
            exit;
        }

        $this->model->criar($payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Tipo de ocorrência cadastrado com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/livro-tipos');
        exit;
    }

    public function edit(int $id): void
    {
        PermissionMiddleware::authorize('livro_tipos:edit');
        $registro = $this->model->buscar($id);

        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Tipo não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/livro-tipos');
            exit;
        }

        $old = $_SESSION['old_input'] ?? null;
        unset($_SESSION['old_input']);

        $this->renderTwig('admin/livro_tipos/edit', array_merge([
            'csrf_token' => CsrfHelper::generateToken(),
            'registro' => $registro,
            'old' => $old,
        ], AdminHelper::getUserData('livro-tipos')));
    }

    public function update(int $id): void
    {
        PermissionMiddleware::authorize('livro_tipos:edit');
        $request = Request::capture();
        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/livro-tipos/edit/' . $id);
            exit;
        }

        CsrfHelper::verifyOrDie();
        if (!$this->model->buscar($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Tipo não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/livro-tipos');
            exit;
        }

        $input = $request->post();
        $payload = $this->validatePayload($input, $id);

        if ($payload === null) {
            $_SESSION['old_input'] = $input;
            $_SESSION['toast'] = ['type' => 'danger', 'message' => $_SESSION['form_error'] ?? 'Verifique os dados informados.'];
            unset($_SESSION['form_error']);
            header('Location: ' . BASE_URL . 'admin/livro-tipos/edit/' . $id);
            exit;
        }

        $this->model->atualizar($id, $payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Tipo atualizado com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/livro-tipos');
        exit;
    }

    public function destroy(int $id): void
    {
        PermissionMiddleware::authorize('livro_tipos:delete');
        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Tipo não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/livro-tipos');
            exit;
        }

        if ($this->model->possuiOcorrencias($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Remova ou reatribua as ocorrências antes de excluir este tipo.'];
            header('Location: ' . BASE_URL . 'admin/livro-tipos');
            exit;
        }

        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Tipo removido com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/livro-tipos');
        exit;
    }

    private function validatePayload(array $input, ?int $ignoreId = null): ?array
    {
        $nome = trim($input['nome'] ?? '');
        $slugBase = trim($input['slug'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $badgeColor = trim($input['badge_color'] ?? '');
        $ativo = isset($input['ativo']) && (string) $input['ativo'] === '0' ? 0 : 1;

        $slug = $this->slugify($slugBase !== '' ? $slugBase : $nome);
        $data = [
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => $descricao,
            'badge_color' => $badgeColor,
            'ativo' => $ativo,
        ];

        $validator = Validator::make($data, [
            'nome' => 'required|min:3|max:150',
            'slug' => 'required|min:3|max:180',
        ]);

        $validator->custom('badge_color', function ($value) {
            if ($value === '' || $value === null) {
                return true;
            }
            return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value);
        }, 'Informe uma cor hexadecimal válida (#RRGGBB).');

        $validator->custom('slug', function ($value) use ($ignoreId) {
            $existing = $this->model->buscarPorSlug($value);
            if (!$existing) {
                return true;
            }
            if ($ignoreId !== null && (int) $existing['id'] === $ignoreId) {
                return true;
            }
            return false;
        }, 'Já existe um tipo com este identificador.');

        if ($validator->fails()) {
            $_SESSION['form_error'] = $this->firstError($validator->errors());
            return null;
        }

        return [
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => $descricao !== '' ? $descricao : null,
            'badge_color' => $badgeColor !== '' ? strtoupper($badgeColor) : null,
            'ativo' => $ativo,
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

        return $normalized !== '' ? $normalized : 'tipo-' . substr(md5((string) microtime(true)), 0, 6);
    }

    private function firstError(array $errors): ?string
    {
        foreach ($errors as $fieldErrors) {
            if (is_array($fieldErrors) && isset($fieldErrors[0])) {
                return $fieldErrors[0];
            }
        }

        return null;
    }
}
