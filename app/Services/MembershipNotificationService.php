<?php

namespace App\Services;

use App\Helpers\EmailTemplate;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MembershipNotificationService
{
    /**
     * @return array{0:bool,1:?string}
     */
    public function sendApproval(array $solicitacao, string $statusAssociativo): array
    {
        if (empty($solicitacao['email'])) {
            return [false, 'Solicitação sem e-mail cadastrado.'];
        }

        try {
            $mail = $this->buildMailer();
            $mail->addAddress($solicitacao['email'], $solicitacao['nome_completo'] ?? 'Solicitante');
            $mail->Subject = 'Solicitação de filiação aprovada';
            $mail->isHTML(true);
            $mail->Body = EmailTemplate::render(
                'Solicitação de filiação aprovada',
                'Sua filiação à AORE/RN foi aprovada e seu vínculo associativo foi reconhecido pela administração da entidade.',
                sprintf(
                    '<p>Prezado(a) %s, sua solicitação de filiação foi aprovada.</p><p><strong>Status associativo inicial:</strong> %s.</p><p>Como associado da AORE/RN, você passa a integrar formalmente a base institucional da associação e poderá usufruir de informações exclusivas, convênios, benefícios, descontos e condições especiais firmados por meio de parcerias institucionais.</p><p>A eventual liberação de acesso ao portal e a eventual designação para função administrativa são atos posteriores, definidos pela administração da associação conforme a necessidade institucional.</p><p>Para qualquer orientação complementar, nossa secretaria institucional permanece à disposição.</p>',
                    htmlspecialchars((string) ($solicitacao['nome_completo'] ?? 'Associado'), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($this->formatAssociativeStatus($statusAssociativo), ENT_QUOTES, 'UTF-8')
                )
            );
            $mail->AltBody = "Sua solicitação de filiação à AORE/RN foi aprovada. Status associativo inicial: {$this->formatAssociativeStatus($statusAssociativo)}. Seu vínculo associativo foi reconhecido. A eventual liberação de acesso ao portal e a designação para função administrativa são atos posteriores da administração da associação.";
            $mail->send();
            return [true, null];
        } catch (Exception $exception) {
            error_log('Erro ao enviar email de aprovação de filiação: ' . ($mail->ErrorInfo ?? $exception->getMessage()));
            return [false, ($mail->ErrorInfo ?? null) ?: $exception->getMessage()];
        }
    }

    /**
     * @return array{0:bool,1:?string}
     */
    public function sendRejection(array $solicitacao, string $observacao = ''): array
    {
        if (empty($solicitacao['email'])) {
            return [false, 'Solicitação sem e-mail cadastrado.'];
        }

        try {
            $mail = $this->buildMailer();
            $mail->addAddress($solicitacao['email'], $solicitacao['nome_completo'] ?? 'Solicitante');
            $mail->Subject = 'Solicitação de filiação rejeitada';
            $mail->isHTML(true);

            $body = sprintf(
                '<p>Olá %s.</p><p>Informamos que sua solicitação de filiação à AORE/RN foi rejeitada após análise da diretoria.</p>',
                htmlspecialchars((string) ($solicitacao['nome_completo'] ?? 'Associado'), ENT_QUOTES, 'UTF-8')
            );

            if ($observacao !== '') {
                $body .= sprintf(
                    '<p><strong>Observação da diretoria:</strong><br>%s</p>',
                    nl2br(htmlspecialchars($observacao, ENT_QUOTES, 'UTF-8'))
                );
            }

            $body .= '<p>Se necessário, entre em contato pelos canais institucionais para obter orientação complementar.</p>';

            $mail->Body = EmailTemplate::render(
                'Solicitação rejeitada',
                'Sua solicitação de filiação foi encerrada com status de rejeição.',
                $body,
                [[
                    'label' => 'Falar com a secretaria',
                    'url' => BASE_URL . 'contact',
                    'background' => '#0b5cab',
                ]]
            );
            $mail->AltBody = $observacao !== ''
                ? "Sua solicitação de filiação foi rejeitada. Observação da diretoria: {$observacao}."
                : 'Sua solicitação de filiação foi rejeitada.';
            $mail->send();
            return [true, null];
        } catch (Exception $exception) {
            error_log('Erro ao enviar email de rejeição de filiação: ' . ($mail->ErrorInfo ?? $exception->getMessage()));
            return [false, ($mail->ErrorInfo ?? null) ?: $exception->getMessage()];
        }
    }

    /**
     * @return array{0:bool,1:?string}
     */
    public function sendComplementation(array $solicitacao, string $orientacao): array
    {
        if (empty($solicitacao['email'])) {
            return [false, 'Solicitação sem e-mail cadastrado.'];
        }

        try {
            $mail = $this->buildMailer();
            $mail->addAddress($solicitacao['email'], $solicitacao['nome_completo'] ?? 'Solicitante');
            $mail->Subject = 'Complementação documental da solicitação de filiação';
            $mail->isHTML(true);
            $mail->Body = EmailTemplate::render(
                'Complementação documental',
                'Sua solicitação de filiação permanece em análise e precisa de complementação documental.',
                sprintf(
                    '<p>Olá %s.</p><p>A diretoria analisou sua solicitação de filiação e precisa de complementação antes da decisão final.</p><p><strong>Orientação da diretoria:</strong><br>%s</p><p>Envie a complementação pelo canal institucional para prosseguirmos com a análise.</p>',
                    htmlspecialchars((string) ($solicitacao['nome_completo'] ?? 'Associado'), ENT_QUOTES, 'UTF-8'),
                    nl2br(htmlspecialchars($orientacao, ENT_QUOTES, 'UTF-8'))
                ),
                [[
                    'label' => 'Falar com a secretaria',
                    'url' => BASE_URL . 'contact',
                    'background' => '#0b5cab',
                ]]
            );
            $mail->AltBody = "Sua solicitação de filiação precisa de complementação documental. Orientação: {$orientacao}.";
            $mail->send();
            return [true, null];
        } catch (Exception $exception) {
            error_log('Erro ao enviar email de complementação de filiação: ' . ($mail->ErrorInfo ?? $exception->getMessage()));
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

    private function formatAssociativeStatus(string $status): string
    {
        return match ($status) {
            'efetivo' => 'Sócio Efetivo',
            'honorario' => 'Sócio Honorário',
            'fundador' => 'Sócio Fundador',
            'benemerito' => 'Sócio Benemérito',
            'veterano' => 'Sócio Veterano',
            'aluno' => 'Sócio Aluno',
            default => 'Sócio Provisório',
        };
    }
}

