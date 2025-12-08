<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;

class OpusController extends Controller
{
    public function __construct()
    {
        // Protege todas as rotas legacy do Opus
        AuthMiddleware::requireAuth();
    }
    
    public function cad_cat_eqp() {
        $this->view('admin/opus/cad_cat_eqp', [], 'admin');
    }

    public function cad_eqp() {
        $this->view('admin/opus/cad_eqp', [], 'admin');
    }

    public function cad_opus() {
        $this->view('admin/opus/cad_opus', [], 'admin');
    }

    public function cad_user() {
        $this->view('admin/opus/cad_user', [], 'admin');
    }

    public function ferramenta() {
        $this->view('admin/opus/ferramenta', [], 'admin');
    }

    public function index() {
        // Redireciona para uma das páginas documentadas, ou mostra uma visão geral
        $this->view('admin/opus/index', [], 'admin'); // Crie essa view
    }

    public function list_user() {
        $this->view('admin/opus/list_user', [], 'admin'); // Crie essa view
    }

    public function lista_cat_eqp() {
        $this->view('admin/opus/lista_cat_eqp', [], 'admin');
    }

    public function lista_eqp() {
        $this->view('admin/opus/lista_eqp', [], 'admin');
    }

    public function obras_andamento() {
        $this->view('admin/opus/obras_andamento', [], 'admin');
    }

    public function opus_manager() {
        $this->view('admin/opus/opus_manager', [], 'admin');
    }

    public function user_manager() {
        $this->view('admin/opus/user_manager', [], 'admin'); // Crie essa view
    }

    public function funcoes() {
        $this->view('admin/funcoes/user_manager', [], 'admin'); // Crie essa view
    }












}
