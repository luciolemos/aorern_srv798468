<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Models\BoardTermModel;

class MandatosController extends Controller
{
    private BoardTermModel $model;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->model = new BoardTermModel();
    }

    public function index(): void
    {
        $request = Request::capture();
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);

        $result = $this->model->paginar($page, $perPage, [
            'q' => $q !== '' ? $q : null,
            'status' => $status !== '' ? $status : null,
        ]);

        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/mandatos',
            'query' => array_filter([
                'q' => $q,
                'status' => $status,
                'per_page' => ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/mandatos/index', array_merge([
            'mandatos' => $result['data'],
            'filters' => [
                'q' => $q,
                'status' => $status,
            ],
            'statusOptions' => $this->model->statusOptions(),
            'pagination' => $pagination,
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
        ], AdminHelper::getUserData('mandatos')));
    }

    public function cadastrar(): void
    {
        $this->renderForm('admin/mandatos/cadastrar', [
            'mandato' => null,
        ]);
    }

    public function salvar(): void
    {
        CsrfHelper::verifyOrDie();
        $payload = $this->preparePayload(Request::capture());

        if ($payload === null) {
            header('Location: ' . BASE_URL . 'admin/mandatos/cadastrar');
            exit;
        }

        $this->model->salvar($payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Mandato cadastrado com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/mandatos');
        exit;
    }

    public function editar(int $id): void
    {
        $mandato = $this->model->buscar($id);

        if (!$mandato) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Mandato não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/mandatos');
            exit;
        }

        $this->renderForm('admin/mandatos/editar', [
            'mandato' => $mandato,
        ]);
    }

    public function atualizar(int $id): void
    {
        CsrfHelper::verifyOrDie();

        if (!$this->model->buscar($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Mandato não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/mandatos');
            exit;
        }

        $payload = $this->preparePayload(Request::capture());
        if ($payload === null) {
            header('Location: ' . BASE_URL . 'admin/mandatos/editar/' . $id);
            exit;
        }

        $this->model->atualizar($id, $payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Mandato atualizado com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/mandatos');
        exit;
    }

    public function deletar(int $id): void
    {
        if (!$this->model->buscar($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Mandato não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/mandatos');
            exit;
        }

        if ($this->model->possuiMembros($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não é possível excluir: existem registros de diretoria vinculados a este mandato.'];
            header('Location: ' . BASE_URL . 'admin/mandatos');
            exit;
        }

        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Mandato removido com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/mandatos');
        exit;
    }

    private function renderForm(string $template, array $data): void
    {
        $this->renderTwig($template, array_merge($data, [
            'csrf_token' => CsrfHelper::generateToken(),
            'statusOptions' => $this->model->statusOptions(),
        ], AdminHelper::getUserData('mandatos')));
    }

    private function preparePayload(Request $request): ?array
    {
        $nome = trim((string) $request->post('nome', ''));
        $status = trim((string) $request->post('status', 'planned'));
        $inicio = trim((string) $request->post('data_inicio', ''));
        $fim = trim((string) $request->post('data_fim', ''));
        $observacoes = trim((string) $request->post('observacoes', ''));

        if ($nome === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe o nome do mandato.'];
            return null;
        }

        if (!in_array($status, $this->model->statusOptions(), true)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione um status válido.'];
            return null;
        }

        if ($inicio === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe a data de início do mandato.'];
            return null;
        }

        if ($fim !== '' && $fim < $inicio) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'A data final não pode ser anterior à data inicial.'];
            return null;
        }

        return [
            'nome' => $nome,
            'status' => $status,
            'data_inicio' => $inicio,
            'data_fim' => $fim !== '' ? $fim : null,
            'observacoes' => $observacoes !== '' ? $observacoes : null,
        ];
    }
}
