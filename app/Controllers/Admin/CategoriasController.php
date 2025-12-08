<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\CategoriaModel;

class CategoriasController extends Controller {
    private CategoriaModel $model;

    public function __construct() {
        $this->model = new CategoriaModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $termo = $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        
        $result = $this->model->paginar($page, $perPage, $termo ?: null);
        $categorias = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/categorias',
            'query' => array_filter([
                'q' => $termo,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/categorias/index', array_merge([
            'categorias' => $categorias,
            'q' => $termo,
            'pagination' => $pagination
        ], AdminHelper::getUserData('categorias')));
    }

    public function cadastrar(): void {
        PermissionMiddleware::authorize('categorias:create');
        $this->renderTwig('admin/categorias/cadastrar', AdminHelper::getUserData('categorias'));
    }

    public function salvar(): void {
        PermissionMiddleware::authorize('categorias:create');
        $request = Request::capture();
        
        $dados = [
            'staff_id' => $request->post('staff_id', 'CAT-' . date('YmdHis')),
            'nome'     => trim($request->post('nome', ''))
        ];

        $this->model->salvar($dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria cadastrada com sucesso!'];
        header("Location: " . BASE_URL . "admin/categorias");
        exit;
    }

    public function editar(int $id): void {
        PermissionMiddleware::authorize('categorias:edit');
        $registro = $this->model->buscar($id);

        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Categoria não encontrada.'];
            header("Location: " . BASE_URL . "admin/categorias");
            exit;
        }

        $this->renderTwig('admin/categorias/editar', array_merge(compact('registro'), AdminHelper::getUserData('categorias')));
    }

    public function atualizar(int $id): void {
        PermissionMiddleware::authorize('categorias:edit');
        $request = Request::capture();
        
        $dados = [
            'nome' => trim($request->post('nome', ''))
        ];

        $this->model->atualizar($id, $dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria atualizada com sucesso!'];
        header("Location: " . BASE_URL . "admin/categorias");
        exit;
    }

    public function deletar(int $id): void {
        PermissionMiddleware::authorize('categorias:delete');
        if ($this->model->possuiEquipamentos($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não é possível excluir esta categoria pois existem equipamentos vinculados.'];
            header("Location: " . BASE_URL . "admin/categorias");
            exit;
        }

        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Categoria removida com sucesso!'];
        header("Location: " . BASE_URL . "admin/categorias");
        exit;
    }
}
