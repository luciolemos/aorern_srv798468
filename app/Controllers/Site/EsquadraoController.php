<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class EsquadraoController extends Controller
{
    public function index()
    {
        $this->renderTwig('site/content/esquadrao');
    }
}
