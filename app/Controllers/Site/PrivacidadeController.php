<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class PrivacidadeController extends Controller
{
    public function index(): void
    {
        $this->renderTwig('site/pages/privacidade', [
            'last_updated' => '02 de março de 2026',
        ]);
    }
}
