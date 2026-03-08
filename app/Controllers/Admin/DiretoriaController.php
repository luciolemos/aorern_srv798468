<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Models\BoardMembershipModel;
use App\Models\BoardTermModel;
use App\Models\FuncaoModel;
use App\Models\PessoalModel;
use App\Models\User;
use App\Services\BoardMembershipRequestValidator;
use App\Services\BoardMembershipWorkflowService;

class DiretoriaController extends Controller
{
    private BoardMembershipModel $model;
    private PessoalModel $pessoal;
    private User $users;
    private FuncaoModel $funcoes;
    private BoardMembershipRequestValidator $validator;
    private BoardMembershipWorkflowService $workflow;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->model = new BoardMembershipModel();
        $this->pessoal = new PessoalModel();
        $this->users = new User();
        $this->funcoes = new FuncaoModel();
        $this->validator = new BoardMembershipRequestValidator();
        $this->workflow = new BoardMembershipWorkflowService($this->model, $this->pessoal, $this->users);
    }

    public function index(): void
    {
        $request = Request::capture();
        $q = trim((string) $request->query('q', ''));
        $termId = $this->sanitizeInteger($request->query('term_id'));
        $isActive = $request->query('is_active');
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);

        $result = $this->model->paginar($page, $perPage, [
            'q' => $q !== '' ? $q : null,
            'term_id' => $termId,
            'is_active' => $isActive !== '' && $isActive !== null ? $isActive : null,
        ]);

        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/diretoria',
            'query' => array_filter([
                'q' => $q,
                'term_id' => $termId,
                'is_active' => $isActive,
                'per_page' => ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/diretoria/index', array_merge([
            'membros' => $result['data'],
            'filters' => [
                'q' => $q,
                'term_id' => $termId,
                'is_active' => $isActive,
            ],
            'terms' => (new BoardTermModel())->listar(),
            'pagination' => $pagination,
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
        ], AdminHelper::getUserData('diretoria')));
    }

    public function cadastrar(): void
    {
        $this->renderForm('admin/diretoria/cadastrar', [
            'membro' => null,
        ]);
    }

    public function salvar(): void
    {
        CsrfHelper::verifyOrDie();
        $validation = $this->validator->validate(Request::capture(), $this->model, $this->funcoes, $this->pessoal);
        if (!$validation['ok']) {
            $_SESSION['toast'] = $validation['toast'];
            header('Location: ' . BASE_URL . 'admin/diretoria/cadastrar');
            exit;
        }

        $this->workflow->create($validation['data']);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Composição cadastrada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/diretoria');
        exit;
    }

    public function editar(int $id): void
    {
        $membro = $this->model->buscar($id);

        if (!$membro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Registro de diretoria não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/diretoria');
            exit;
        }

        $this->renderForm('admin/diretoria/editar', [
            'membro' => $membro,
        ]);
    }

    public function visualizar(int $id): void
    {
        $membro = $this->model->buscar($id);

        if (!$membro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Registro de diretoria não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/diretoria');
            exit;
        }

        $this->renderTwig('admin/diretoria/visualizar', array_merge([
            'membro' => $membro,
        ], AdminHelper::getUserData('diretoria')));
    }

    public function atualizar(int $id): void
    {
        CsrfHelper::verifyOrDie();

        if (!$this->model->buscar($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Registro de diretoria não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/diretoria');
            exit;
        }

        $validation = $this->validator->validate(Request::capture(), $this->model, $this->funcoes, $this->pessoal, $id);
        if (!$validation['ok']) {
            $_SESSION['toast'] = $validation['toast'];
            header('Location: ' . BASE_URL . 'admin/diretoria/editar/' . $id);
            exit;
        }

        $this->workflow->update($id, $validation['data']);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Composição atualizada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/diretoria');
        exit;
    }

    public function deletar(int $id): void
    {
        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Registro de diretoria não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/diretoria');
            exit;
        }

        $this->workflow->delete($registro);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Registro removido com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/diretoria');
        exit;
    }

    private function renderForm(string $template, array $data): void
    {
        $this->renderTwig($template, array_merge($data, [
            'csrf_token' => CsrfHelper::generateToken(),
            'terms' => (new BoardTermModel())->listar(),
            'associados' => $this->pessoal->all('p.nome ASC'),
            'funcoes' => $this->funcoes->listar(),
        ], AdminHelper::getUserData('diretoria')));
    }
}
