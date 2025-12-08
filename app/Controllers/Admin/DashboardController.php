<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\FuncaoModel;
use App\Models\PessoalModel;
use App\Models\CategoriaModel;
use App\Models\EquipamentoModel;
use App\Models\ObraModel;

class DashboardController extends Controller {
    public function index() {
        // Protege a rota admin
        AuthMiddleware::requireAuth();
        
        // Estatísticas principais
        $total_funcoes       = (new FuncaoModel())->contar();
        $total_pessoal       = (new PessoalModel())->contar();
        $total_categoria_eqp = (new CategoriaModel())->contar();
        $total_equipamentos  = (new EquipamentoModel())->contar();
        $total_obras         = (new ObraModel())->contar();
        
        // Atividades recentes
        $pessoalModel = new PessoalModel();
        $equipamentoModel = new EquipamentoModel();
        $obraModel = new ObraModel();
        
        $ultimos_bombeiros = $pessoalModel->all('id DESC', 5);
        $ultimos_equipamentos = $equipamentoModel->all('id DESC', 5);
        $ultimas_obras = $obraModel->all('id DESC', 3);

        $userName = $_SESSION['user_name'] ?? 'Usuário';
        $userEmail = $_SESSION['user_email'] ?? '';
        $userAvatar = $_SESSION['user_avatar'] ?? '';
        $initial = function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1);
        
        $dados = [
            'total_funcoes'       => $total_funcoes,
            'total_pessoal'       => $total_pessoal,
            'total_categoria_eqp' => $total_categoria_eqp,
            'total_equipamentos'  => $total_equipamentos,
            'total_obras'         => $total_obras,
            'ultimos_bombeiros' => $ultimos_bombeiros,
            'ultimos_equipamentos' => $ultimos_equipamentos,
            'ultimas_obras'        => $ultimas_obras,
            'ultimo_login'        => $_SESSION['last_activity'] ?? time(),
            'user' => [
                'name' => $userName,
                'email' => $userEmail ?: 'admin@cbmrn',
                'initial' => strtoupper($initial ?: 'U'),
                'avatar' => $userAvatar
            ],
            'subRoute' => 'dashboard'
        ];

        $this->renderTwig('admin/dashboard', $dados);
    }
}
