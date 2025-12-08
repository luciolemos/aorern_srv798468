<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\EquipamentoModel;
use App\Models\CategoriaModel;

class EquipamentosController extends Controller {

    private EquipamentoModel $model;

    public function __construct() {
        $this->model = new EquipamentoModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $q = $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        
        $result = $this->model->paginar($page, $perPage, $q ?: null);
        $equipamentos = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/equipamentos',
            'query' => array_filter([
                'q' => $q,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/equipamentos/index', array_merge(compact('equipamentos', 'q', 'pagination'), AdminHelper::getUserData('equipamentos')));
    }

    public function cadastrar(): void {
        PermissionMiddleware::authorize('equipamentos:create');
        $categorias = (new CategoriaModel())->listar();
        $this->renderTwig('admin/equipamentos/cadastrar', array_merge(compact('categorias'), AdminHelper::getUserData('equipamentos')));
    }

    public function salvar(): void {
        PermissionMiddleware::authorize('equipamentos:create');
        $request = Request::capture();
        
        $dados = [
            'staff_id'           => $request->post('staff_id', 'EQP-' . date('YmdHis')),
            'nome'               => trim($request->post('nome', '')),
            'codigo'             => trim($request->post('codigo', '')),
            'serial_number'      => trim($request->post('serial_number', '')),
            'marca'              => trim($request->post('marca', '')),
            'modelo'             => trim($request->post('modelo', '')),
            'data_fabricacao'    => $request->post('data_fabricacao'),
            'estado'             => trim($request->post('estado', '')),
            'quantidade_estoque' => (int) $request->post('quantidade_estoque', 0),
            'categoria_id'       => (int) $request->post('categoria_id')
        ];

        $this->model->salvar($dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Equipamento cadastrado com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/equipamentos');
        exit;
    }

    public function editar(int $id): void {
        PermissionMiddleware::authorize('equipamentos:edit');
        $registro = $this->model->buscar($id);
        $categorias = (new CategoriaModel())->listar();
        $this->renderTwig('admin/equipamentos/editar', array_merge(compact('registro', 'categorias'), AdminHelper::getUserData('equipamentos')));
    }

    public function atualizar(int $id): void {
        PermissionMiddleware::authorize('equipamentos:edit');
        $request = Request::capture();
        $this->model->atualizar($id, $request->post());

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Equipamento atualizado!'];
        header('Location: ' . BASE_URL . 'admin/equipamentos');
        exit;
    }

    public function deletar(int $id): void {
        PermissionMiddleware::authorize('equipamentos:delete');
        $this->model->deletar($id);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Equipamento removido.'];
        header('Location: ' . BASE_URL . 'admin/equipamentos');
        exit;
    }
}
