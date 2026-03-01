<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function forbidden(): void
    {
        http_response_code(403);
        $this->renderTwig('admin/pages/403');
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->renderTwig('admin/pages/404');
    }

    public function server(): void
    {
        throw new \RuntimeException('Teste interno de erro 500 na area administrativa.');
    }
}
