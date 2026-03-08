<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;
use App\Helpers\PaginationHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\FuncaoModel;

class FuncoesController extends Controller {
    private FuncaoModel $model;

    public function __construct() {
        $this->model = new FuncaoModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $q = $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;
        
        $result = $this->model->paginar($page, $perPage, $q ?: null);
        $funcoes = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/funcoes',
            'query' => array_filter([
                'q' => $q,
                'per_page' => $perPageQueryValue,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $perPageOptions = PaginationHelper::options($defaultPerPage);

        $this->renderTwig('admin/funcoes/index', array_merge(
            compact('funcoes', 'q', 'pagination', 'perPageOptions', 'perPageSelection'),
            AdminHelper::getUserData('funcoes')
        ));
    }

    public function cadastrar(): void {
        PermissionMiddleware::authorize('funcoes:create');
        $staff_id = $this->model->gerarProximoStaffIdAore();
        $this->renderTwig('admin/funcoes/cadastrar', array_merge(
            compact('staff_id'),
            AdminHelper::getUserData('funcoes')
        ));
    }

    public function salvar(): void {
        PermissionMiddleware::authorize('funcoes:create');
        $request = Request::capture();

        $staffIdInput = strtoupper(trim((string) $request->post('staff_id', '')));
        $staffIdValido = preg_match('/^FUNC-AORE-\d{3,}$/', $staffIdInput) === 1;
        $staffId = $staffIdValido ? $staffIdInput : $this->model->gerarProximoStaffIdAore();

        if ($this->model->staffIdExiste($staffId)) {
            $staffId = $this->model->gerarProximoStaffIdAore();
        }
        
        $dados = [
            'staff_id' => $staffId,
            'nome'     => trim($request->post('nome', ''))
        ];

        $this->model->salvar($dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Função cadastrada com sucesso!'];
        header("Location: " . BASE_URL . "admin/funcoes");
        exit;
    }

    public function editar(int $id): void {
        PermissionMiddleware::authorize('funcoes:edit');
        $registro = $this->model->buscar($id);
        $this->renderTwig('admin/funcoes/editar', array_merge(compact('registro'), AdminHelper::getUserData('funcoes')));
    }

    public function atualizar(int $id): void {
        PermissionMiddleware::authorize('funcoes:edit');
        $request = Request::capture();

        $ok = $this->model->atualizar($id, [
            'nome' => trim($request->post('nome', ''))
        ]);

        $_SESSION['toast'] = $ok
            ? ['type' => 'success', 'message' => 'Função atualizada com sucesso!']
            : ['type' => 'danger', 'message' => 'Não foi possível atualizar a função no momento.'];
        header("Location: " . BASE_URL . "admin/funcoes");
        exit;
    }

    public function deletar(int $id): void {
        PermissionMiddleware::authorize('funcoes:delete');
        if ($this->model->possuiBombeiros($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não é possível excluir: existem associados vinculados a esta função.'];
            header("Location: " . BASE_URL . "admin/funcoes");
            exit;
        }

        $this->model->deletar($id);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Função excluída.'];
        header("Location: " . BASE_URL . "admin/funcoes");
        exit;
    }
}
