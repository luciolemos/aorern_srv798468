<?php


namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use PHPMailer\PHPMailer\PHPMailer;
use Dotenv\Dotenv;

class StatusController extends Controller
{
    public function index()
    {
        // Protege a rota admin
        AuthMiddleware::requireAuth();
        PermissionMiddleware::authorize('status:view');
        
        $dotenvPath = dirname(__DIR__, 3); // volta 3 níveis até a raiz
        if (file_exists($dotenvPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($dotenvPath);
            $dotenv->load(); // ⬅️ importante para popular $_ENV
            $envLoaded = true;
        } else {
            $envLoaded = false;
        }

        $status = [
            'env' => $envLoaded,
            'db' => false,
            'smtp' => false,
            'recaptcha' => false
        ];

        // Banco de Dados
        try {
            Database::connect();
            $status['db'] = true;
        } catch (\Exception $e) {}

        // SMTP com PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->Port = $_ENV['SMTP_PORT'];
            $mail->smtpConnect(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
            $status['smtp'] = true;
        } catch (\Exception $e) {}

        // Google reCAPTCHA (simulação)
        try {
            $status['recaptcha'] = true;
        } catch (\Exception $e) {}

        // ✅ CHAMADA COM LAYOUT ADMIN
        $this->renderTwig('admin/status', array_merge(['status' => $status], AdminHelper::getUserData('status')));
    }

}
