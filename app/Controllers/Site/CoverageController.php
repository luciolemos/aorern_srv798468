<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class CoverageController extends Controller
{
    public function index()
    {
        if (empty($_SESSION['admin'])) {
            header('Location: ' . BASE_URL . 'admin');
            exit;
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

        header('Location: ' . BASE_URL . 'coverage/render/index.html');
        exit;
    }


}
