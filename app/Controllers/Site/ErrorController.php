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

    public function temporariamenteIndisponivel(): void
    {
        http_response_code(503);
        header('Retry-After: 86400');
        header('X-Robots-Tag: noindex, nofollow', true);

        $this->renderTwig('site/pages/503');
    }
}
