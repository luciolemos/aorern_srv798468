<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function forbidden(): void
    {
        http_response_code(403);
        $this->renderTwig('site/pages/403');
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->renderTwig('site/pages/404');
    }

    public function server(): void
    {
        throw new \RuntimeException('Teste interno de erro 500 na area publica.');
    }
}
