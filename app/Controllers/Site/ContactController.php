<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Helpers\EmailTemplate;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactController extends Controller {
    public function index() {
        $status = [
            'ok' => isset($_GET['ok']),
            'error' => $_GET['erro'] ?? null,
        ];

        $this->renderTwig('site/pages/contact', [
            'contact_status' => $status,
        ]);
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

        if (empty($captcha)) {
            header("Location: " . BASE_URL . "contact?erro=recaptcha");
            exit;
        }

        // 🔐 Verificação do reCAPTCHA
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query([
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $captcha,
        ]);
        $verify = @file_get_contents($verifyUrl);
        $response = $verify !== false ? json_decode($verify) : null;

        if (!$response || empty($response->success)) {
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
            $replyUrl = sprintf(
                'mailto:%s?subject=%s',
                rawurlencode($email),
                rawurlencode('Re: contato recebido pelo portal AORE/RN')
            );
            $whatsappUrl = WHATSAPP_PHONE_E164 !== ''
                ? sprintf(
                    'https://wa.me/%s?text=%s',
                    rawurlencode(WHATSAPP_PHONE_E164),
                    rawurlencode("Olá Sr(a). {$nome}, recebemos seu e-mail e vamos dar início ao seu atendimento por este canal.")
                )
                : '';

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

            $mail->Subject = "📩 Mensagem de $nome via formulário do site AORE/RN";
            $mail->isHTML(true);
            $mail->Body = EmailTemplate::render(
                'Nova mensagem recebida pelo portal',
                'O formulário de contato do site da AORE/RN registrou uma nova mensagem para análise.',
                sprintf(
                    '<p><strong>Nome:</strong> %s</p><p><strong>E-mail:</strong> %s</p><p><strong>Mensagem:</strong><br>%s</p>',
                    htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
                    nl2br(htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'))
                ),
                array_values(array_filter([
                    [
                        'label' => 'Responder por e-mail',
                        'url' => $replyUrl,
                        'background' => '#0b5cab',
                    ],
                    $whatsappUrl !== '' ? [
                        'label' => 'Abrir WhatsApp',
                        'url' => $whatsappUrl,
                        'background' => '#128c7e',
                    ] : null,
                ]))
            );
            $mail->AltBody = "Nova mensagem recebida pelo portal AORE/RN\n\nNome: $nome\nE-mail: $email\nMensagem:\n$mensagem";

            $mail->send();
            header("Location: " . BASE_URL . "contact?ok=1");

        } catch (Exception $e) {
            error_log('Erro ao enviar email: ' . $mail->ErrorInfo);
            header("Location: " . BASE_URL . "contact?erro=1");
        }

        exit;
    }
}
