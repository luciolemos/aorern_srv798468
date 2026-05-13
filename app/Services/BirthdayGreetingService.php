<?php

namespace App\Services;

use App\Helpers\EmailTemplate;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class BirthdayGreetingService
{
    private const DEFAULT_MESSAGE = "Nesta data especial, desejamos saude, felicidade, realizacoes e continuo sucesso em sua trajetoria pessoal e profissional, mantendo sempre vivos os valores de honra, disciplina, camaradagem e compromisso com a Patria, que marcam a formacao do oficial da reserva.\n\nReceba o reconhecimento e o abraco fraterno de todos que integram a familia AORE/RN.\n\nParabens e muitas felicidades!\n\nAdministracao\nAORE/RN - Associacao dos Oficiais da Reserva do Exercito\n\nPerpetuum Officium erga Patriam\n(Dever permanente para com a Patria)";

    /**
     * @param array{name:string,email:string} $recipient
     * @param array{display_name:string,context_label:string,reply_to_email?:string,reply_to_name?:string} $sender
     * @return array{0:bool,1:?string}
     */
    public function send(array $recipient, array $sender, string $message): array
    {
        $recipientEmail = trim((string) ($recipient['email'] ?? ''));
        $recipientName = trim((string) ($recipient['name'] ?? 'Associado'));

        if ($recipientEmail === '' || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Associado aniversariante sem e-mail válido cadastrado.'];
        }

        $senderName = trim((string) ($sender['display_name'] ?? 'AORE/RN'));
        $contextLabel = trim((string) ($sender['context_label'] ?? 'Portal institucional AORE/RN'));
        $replyToEmail = trim((string) ($sender['reply_to_email'] ?? ''));
        $replyToName = trim((string) ($sender['reply_to_name'] ?? $senderName));
        $cleanMessage = trim($message);

        if ($cleanMessage === '') {
            $cleanMessage = self::DEFAULT_MESSAGE;
        }

        try {
            $mail = $this->buildMailer();
            $mail->addAddress($recipientEmail, $recipientName);

            if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyToEmail, $replyToName !== '' ? $replyToName : $senderName);
            }

            $mail->Subject = 'Saudação de aniversário - AORE/RN';
            $mail->isHTML(true);
            $mail->Body = EmailTemplate::render(
                'Saudação de aniversário',
                'Uma mensagem institucional foi encaminhada pelo portal da AORE/RN em homenagem ao seu aniversário.',
                sprintf(
                    '<p>Prezado(a) %s,</p><p>Receba a mensagem abaixo encaminhada pelo portal institucional da AORE/RN.</p><div style="margin:20px 0;padding:18px;border:1px solid #dbe5dd;border-radius:12px;background:#f7fbf8;">%s</div><p><strong>Remetente identificado:</strong> %s</p><p><strong>Origem do envio:</strong> %s</p><p>Se desejar responder, utilize o recurso de resposta do seu cliente de e-mail quando disponível.</p>',
                    htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8'),
                    nl2br(htmlspecialchars($cleanMessage, ENT_QUOTES, 'UTF-8')),
                    htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($contextLabel, ENT_QUOTES, 'UTF-8')
                )
            );
            $mail->AltBody = sprintf(
                "Saudação de aniversário enviada pelo portal da AORE/RN.\n\nMensagem:\n%s\n\nRemetente identificado: %s\nOrigem do envio: %s",
                $cleanMessage,
                $senderName,
                $contextLabel
            );
            $mail->send();

            return [true, null];
        } catch (Exception $exception) {
            error_log('Erro ao enviar saudação de aniversário: ' . ($mail->ErrorInfo ?? $exception->getMessage()));
            return [false, ($mail->ErrorInfo ?? null) ?: $exception->getMessage()];
        }
    }

    private function buildMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->Timeout = 3;
        $mail->SMTPKeepAlive = false;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);

        return $mail;
    }
}
