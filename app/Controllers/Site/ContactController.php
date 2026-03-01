<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactController extends Controller {
    public function index() {
        $this->renderTwig('site/pages/contact');
    }

    public function send() {
        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');
        $captcha  = $_POST['g-recaptcha-response'] ?? '';

        if (empty($nome) || empty($email) || empty($mensagem)) {
            header("Location: " . BASE_URL . "contact?erro=1");
            exit;
        }

        // 🔐 Verificação do reCAPTCHA
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET_KEY . "&response=" . $captcha);
        $response = json_decode($verify);
        if (!$response->success) {
            header("Location: " . BASE_URL . "contact?erro=recaptcha");
            exit;
        }

        // 📬 Envio com PHPMailer
        $mail = new PHPMailer(true);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: " . BASE_URL . "contact?erro=invalid_email");
            exit;
        }

        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->addCustomHeader('X-Mailer', 'JakeMailer/1.0');
            $mail->addCustomHeader('X-Originating-IP', $_SERVER['REMOTE_ADDR'] ?? 'localhost');

            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress(INSTITUTIONAL_EMAIL_PRIMARY ?: SMTP_FROM);
            $mail->addReplyTo($email, $nome);

            $mail->Subject = "📩 Mensagem de $nome via formulário do site";
            $mail->Body = "Nome: $nome\nE-mail: $email\nMensagem:\n$mensagem";

            $mail->send();
            header("Location: " . BASE_URL . "contact?ok=1");

        } catch (Exception $e) {
            error_log('Erro ao enviar email: ' . $mail->ErrorInfo);
            header("Location: " . BASE_URL . "contact?erro=1");
        }

        exit;
    }
}
