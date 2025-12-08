<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\PessoalModel;

class HomeController extends Controller {
    public function index() {
        $pessoalModel = new PessoalModel();
        $totalProfissionais = $pessoalModel->contar();
        
        $this->renderTwig('site/pages/home', [
            'totalProfissionais' => $totalProfissionais
        ]);
    }
}
