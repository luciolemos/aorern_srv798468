<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class CoverageController extends Controller
{
    private function isLocalRequest(): bool
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($remoteAddr, ['127.0.0.1', '::1'], true);
    }

    public function index()
    {
        if (empty($_SESSION['admin'])) {
            header('Location: ' . BASE_URL . 'admin');
            exit;
        }

        if (!$this->isLocalRequest()) {
            http_response_code(404);
            exit('Página não encontrada.');
        }

        // View coverage.php será renderizada
        $this->renderTwig('coverage');
    }

    public function relatorio()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login/admin');
            exit;
        }

        if (!$this->isLocalRequest()) {
            http_response_code(404);
            exit('Página não encontrada.');
        }

        header('Location: ' . BASE_URL . 'coverage/render/index.html');
        exit;
    }


}
