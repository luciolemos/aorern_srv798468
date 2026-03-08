<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class TermosController extends Controller
{
    public function index(): void
    {
        $this->renderTwig('site/pages/termos', [
            'last_updated' => '02 de março de 2026',
        ]);
    }
}
