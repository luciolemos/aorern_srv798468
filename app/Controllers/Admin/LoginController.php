<?php

namespace App\Controllers\Admin;

/**
 * Alias controller para manter compatibilidade com /admin/login.
 * Reutiliza toda a lógica existente em AuthController.
 */
class LoginController extends AuthController
{
    public function __construct()
    {
        parent::__construct();
    }
}

