<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\ObraModel;

class ObrasController extends Controller {
    private ObraModel $model;

    public function __construct() {
        $this->model = new ObraModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $q = $request->query('q', '');
        
        $obras = $q
            ? $this->model->buscarPorDescricao($q)
            : $this->model->listar();

        $this->renderTwig('admin/obras/index', array_merge(compact('obras', 'q'), AdminHelper::getUserData('obras')));
    }

    public function cadastrar(): void {
        PermissionMiddleware::authorize('obras:create');
        $this->renderTwig('admin/obras/cadastrar', AdminHelper::getUserData('obras'));
    }

    public function salvar(): void {
        PermissionMiddleware::authorize('obras:create');
        $request = Request::capture();
        $dados = $request->post();
        $dados['valor_estimado'] = str_replace(',', '.', $dados['valor_estimado'] ?? 0.0);

        $this->model->salvar($dados);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Obra cadastrada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/obras');
        exit;
    }

    public function editar(int $id): void {
        PermissionMiddleware::authorize('obras:edit');
        $obra = $this->model->buscar($id);
        if (!$obra) die("Obra não encontrada.");

        $this->renderTwig('admin/obras/editar', array_merge(compact('obra'), AdminHelper::getUserData('obras')));
    }

    public function atualizar(int $id): void {
        PermissionMiddleware::authorize('obras:edit');
        $request = Request::capture();
        $dados = $request->post();
        $dados['valor_estimado'] = str_replace(',', '.', $dados['valor_estimado'] ?? 0.0);

        $this->model->atualizar($id, $dados);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Obra atualizada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/obras');
        exit;
    }

    public function deletar(int $id): void {
        PermissionMiddleware::authorize('obras:delete');
        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Obra removida com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/obras');
        exit;
    }
}
