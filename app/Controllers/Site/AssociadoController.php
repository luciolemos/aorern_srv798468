<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Helpers\CsrfHelper;
use App\Middleware\AuthMiddleware;
use App\Models\BoardMembershipModel;
use App\Models\BoardTermModel;
use App\Models\DigitalCardModel;
use App\Models\MembershipApplicationModel;
use App\Models\PessoalModel;
use App\Models\Post;
use App\Models\User;
use App\Services\BirthdayGreetingService;

class AssociadoController extends Controller
{
    private const USER_AVATAR_DIR = 'uploads/users/';
    private const MEMBERSHIP_DOCUMENTS_DIR = 'uploads/filiacao/';
    private User $users;
    private MembershipApplicationModel $applications;
    private PessoalModel $pessoal;
    private DigitalCardModel $cards;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->users = new User();
        $this->applications = new MembershipApplicationModel();
        $this->pessoal = new PessoalModel();
        $this->cards = new DigitalCardModel();
    }

    public function index(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: ' . BASE_URL . 'login/admin');
            exit;
        }

        $role = $_SESSION['user_role'] ?? 'usuario';
        if (in_array($role, ['admin', 'gerente', 'operador'], true)) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }

        $user = $this->users->buscarPorId($userId);
        $solicitacao = $this->applications->buscarPorUserId($userId);
        $associado = null;
        $documentos = [];
        $avatarPath = null;

        if ($solicitacao) {
            $documentos = $this->normalizeDocuments($this->decodeDocuments($solicitacao['documentos_json'] ?? null));
            $solicitacao['localizacao_label'] = $this->formatLocationLabel($solicitacao['cidade'] ?? null, $solicitacao['uf'] ?? null);
            if (!empty($solicitacao['pessoal_id'])) {
                $associado = $this->pessoal->buscar((int) $solicitacao['pessoal_id']);
            }
        }

        if (!empty($user['avatar'])) {
            $avatarPath = (string) $user['avatar'];
        } elseif (!empty($associado['foto'])) {
            $avatarPath = (string) $associado['foto'];
        } elseif (!empty($solicitacao['avatar'])) {
            $avatarPath = (string) $solicitacao['avatar'];
        }

        $carteirinha = null;
        if ($associado && $this->cards->isAvailable()) {
            $carteirinha = $this->cards->buscarAtivaPorPessoal((int) $associado['id']);
        }

        $aniversariantes = array_map(
            fn(array $item): array => $this->mapBirthdayMember($item),
            $this->pessoal->listarAniversariantesDoDia(date('m-d'), 8)
        );
        $boardSection = $this->buildBoardSection();
        $institutionalPosts = $this->buildInstitutionalPosts();

        $this->renderTwig('site/pages/associado', [
            'user' => $user,
            'solicitacao' => $solicitacao,
            'associado' => $associado,
            'carteirinha' => $carteirinha,
            'aniversariantes_hoje' => $aniversariantes,
            'diretoria_vigente' => $boardSection['members'],
            'mandato_vigente' => $boardSection['term'],
            'comunicados_institucionais' => $institutionalPosts,
            'documentos' => $documentos,
            'associadoAvatarUrl' => $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png'),
            'statusAssociativoLabel' => $this->formatAssociativeStatus($solicitacao['status_associativo'] ?? $associado['status_associativo'] ?? 'provisorio'),
            'csrf_token' => CsrfHelper::generateToken(),
        ]);
    }

    public function carteirinha(): void
    {
        $data = $this->buildCarteirinhaViewData();
        if ($data === null) {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Carteirinha digital indisponível no momento.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $data['print_mode'] = false;
        $this->renderTwig('site/pages/associado_carteirinha', $data);
    }

    public function carteirinhaImpressao(): void
    {
        $data = $this->buildCarteirinhaViewData();
        if ($data === null) {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Carteirinha digital indisponível no momento.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $this->renderTwig('site/pages/associado_carteirinha_print', $data);
    }

    public function carteirinhaMobile(): void
    {
        $data = $this->buildCarteirinhaViewData();
        if ($data === null) {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Carteirinha digital indisponível no momento.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $this->renderTwig('site/pages/associado_carteirinha_mobile', $data);
    }

    private function buildCarteirinhaViewData(): ?array
    {
        $user = $this->getAuthenticatedMemberUser();
        $solicitacao = $this->applications->buscarPorUserId((int) $user['id']);
        $associado = null;
        if ($solicitacao && !empty($solicitacao['pessoal_id'])) {
            $associado = $this->pessoal->buscar((int) $solicitacao['pessoal_id']);
        }

        if (!$associado || !$this->cards->isAvailable()) {
            return null;
        }

        $card = $this->cards->buscarAtivaPorPessoal((int) $associado['id']);
        if (!$card) {
            return null;
        }

        $snapshot = [];
        if (!empty($card['snapshot_json'])) {
            $decoded = json_decode((string) $card['snapshot_json'], true);
            if (is_array($decoded)) {
                $snapshot = $decoded;
            }
        }

        // Modelo 1 (documento): carteirinha renderiza apenas dados congelados no snapshot da emissão.
        $avatarPath = (string) ($snapshot['foto'] ?? '');
        $avatarUrl = $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png');
        $verificationUrl = BASE_URL . 'carteirinha/validar/' . rawurlencode((string) $card['token']);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . rawurlencode($verificationUrl);
        $issuedAtRaw = (string) ($card['emitida_em'] ?? '');
        $createdAtRaw = (string) ($card['criado_em'] ?? '');
        $signedAtRaw = $issuedAtRaw !== '' ? $issuedAtRaw : $createdAtRaw;
        $signedAtLabel = '-';
        if ($signedAtRaw !== '') {
            $signedAtTs = strtotime($signedAtRaw);
            if ($signedAtTs !== false) {
                $signedAtLabel = date('d/m/Y H:i', $signedAtTs);
            }
        }
        $snapshotRaw = (string) ($card['snapshot_json'] ?? '');
        $signaturePayload = implode('|', [
            (string) ($card['card_code'] ?? ''),
            (string) ($card['token'] ?? ''),
            $snapshotRaw,
            $signedAtRaw,
        ]);
        $signatureHash = strtoupper(substr(hash('sha256', $signaturePayload), 0, 16));

        return [
            'user' => $user,
            'solicitacao' => $solicitacao,
            'associado' => $associado,
            'card' => $card,
            'snapshot' => $snapshot,
            'avatarUrl' => $avatarUrl,
            'verificationUrl' => $verificationUrl,
            'qrUrl' => $qrUrl,
            'signature' => [
                'signer' => 'Presidente AORE/RN',
                'signed_at' => $signedAtRaw,
                'signed_at_label' => $signedAtLabel,
                'hash' => $signatureHash,
            ],
        ];
    }

    public function update(): void
    {
        CsrfHelper::verifyOrDie();
        $user = $this->getAuthenticatedMemberUser();

        $username = User::normalizeUsername((string) ($_POST['username'] ?? ''));
        $email = trim($_POST['email'] ?? '');

        if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe usuário e e-mail válidos.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if (!User::isValidUsernameFormat($username)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Usuário inválido. Use somente letras sem acento seguidas de 4 números (ex.: csilva1968).'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $existingByUsername = $this->users->buscarPorUsername($username);
        if ($existingByUsername && (int) $existingByUsername['id'] !== (int) $user['id']) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Esse nome de usuário já está em uso.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $existingByEmail = $this->users->buscarPorEmail($email);
        if ($existingByEmail && (int) $existingByEmail['id'] !== (int) $user['id']) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Esse e-mail já está em uso por outra conta.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $payload = [
            'username' => $username,
            'email' => $email,
        ];

        if (isset($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $avatarPath = $this->processAvatar($_FILES['avatar']);
            if ($avatarPath) {
                $payload['avatar'] = $avatarPath;
                $_SESSION['user_avatar'] = $avatarPath;

                $registroAssociado = $this->pessoal->buscarPorUserId((int) $user['id']);
                if ($registroAssociado) {
                    $this->pessoal->atualizarFoto((int) $registroAssociado['id'], $avatarPath);
                }

                $solicitacaoVinculada = $this->applications->buscarPorUserId((int) $user['id']);
                if ($solicitacaoVinculada) {
                    $this->applications->atualizar((int) $solicitacaoVinculada['id'], ['avatar' => $avatarPath]);
                }
            } else {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar a foto. Envie um arquivo JPG, PNG ou WebP válido.'];
                header('Location: ' . BASE_URL . 'associado');
                exit;
            }
        }

        if ($this->users->atualizar((int) $user['id'], $payload)) {
            $_SESSION['user_name'] = $username;
            $_SESSION['user_email'] = $email;
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Dados da conta atualizados com sucesso.'];
        } else {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar os dados da conta.'];
        }

        header('Location: ' . BASE_URL . 'associado');
        exit;
    }

    public function changePassword(): void
    {
        CsrfHelper::verifyOrDie();
        $user = $this->getAuthenticatedMemberUser();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Preencha todos os campos da alteração de senha.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if (!password_verify($currentPassword, (string) $user['password'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'A senha atual está incorreta.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'A nova senha deve ter pelo menos 6 caracteres.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'As senhas informadas não coincidem.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if ($this->users->atualizar((int) $user['id'], ['password' => password_hash($newPassword, PASSWORD_BCRYPT)])) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Senha atualizada com sucesso.'];
        } else {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar a senha.'];
        }

        header('Location: ' . BASE_URL . 'associado');
        exit;
    }

    public function complementar(): void
    {
        CsrfHelper::verifyOrDie();
        $user = $this->getAuthenticatedMemberUser();
        $solicitacao = $this->applications->buscarPorUserId((int) $user['id']);

        if (!$solicitacao) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Solicitação não encontrada para complementação.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if (($solicitacao['status'] ?? '') !== 'complementacao') {
            $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Sua solicitação não está em status de complementação no momento.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $updatePayload = [];

        $cam = trim((string) ($_POST['cam'] ?? ''));
        if ($cam !== '') {
            $updatePayload['cam'] = $cam;
        }

        $rg = trim((string) ($_POST['rg'] ?? ''));
        if ($rg !== '') {
            $updatePayload['rg'] = $rg;
        }

        $telefoneRaw = preg_replace('/\D/', '', (string) ($_POST['telefone'] ?? ''));
        if ($telefoneRaw !== '') {
            if (strlen($telefoneRaw) < 10 || strlen($telefoneRaw) > 11) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe um telefone válido com DDD.'];
                header('Location: ' . BASE_URL . 'associado');
                exit;
            }
            $updatePayload['telefone'] = $telefoneRaw;
        }

        $anoNpor = trim((string) ($_POST['ano_npor'] ?? ''));
        if ($anoNpor !== '') {
            $currentYear = (int) date('Y');
            if (!ctype_digit($anoNpor) || (int) $anoNpor < 1900 || (int) $anoNpor > $currentYear) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Ano NPOR inválido.'];
                header('Location: ' . BASE_URL . 'associado');
                exit;
            }
            $updatePayload['ano_npor'] = $anoNpor;
        }

        $numeroMilitar = trim((string) ($_POST['numero_militar'] ?? ''));
        if ($numeroMilitar !== '') {
            if (!preg_match('/^\d{2}$/', $numeroMilitar)) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Campo Nº inválido. Informe exatamente 2 dígitos.'];
                header('Location: ' . BASE_URL . 'associado');
                exit;
            }
            $updatePayload['numero_militar'] = $numeroMilitar;
        }

        $postoGraduacao = trim((string) ($_POST['posto_graduacao'] ?? ''));
        if ($postoGraduacao !== '') {
            $updatePayload['posto_graduacao'] = $postoGraduacao;
        }

        $nomeGuerra = trim((string) ($_POST['nome_guerra'] ?? ''));
        if ($nomeGuerra !== '') {
            if (!preg_match('/^[\p{L}\s\.]+$/u', $nomeGuerra)) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Nome de guerra inválido. Use apenas letras, espaços e ponto.'];
                header('Location: ' . BASE_URL . 'associado');
                exit;
            }
            $updatePayload['nome_guerra'] = $nomeGuerra;
        }

        $turmaNpor = trim((string) ($_POST['turma_npor'] ?? ''));
        if ($turmaNpor !== '') {
            $updatePayload['turma_npor'] = $turmaNpor;
        }

        $armaQuadro = trim((string) ($_POST['arma_quadro'] ?? ''));
        if ($armaQuadro !== '') {
            $updatePayload['arma_quadro'] = $armaQuadro;
        }

        $observacoes = trim((string) ($_POST['observacoes'] ?? ''));
        if ($observacoes !== '') {
            $updatePayload['observacoes'] = $observacoes;
        }

        if (isset($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $avatarPath = $this->processAvatar($_FILES['avatar']);
            if (!$avatarPath) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'A foto enviada é inválida. Use JPG, PNG ou WEBP.'];
                header('Location: ' . BASE_URL . 'associado');
                exit;
            }

            $updatePayload['avatar'] = $avatarPath;
            $this->users->atualizar((int) $user['id'], ['avatar' => $avatarPath]);
            $_SESSION['user_avatar'] = $avatarPath;

            $registroAssociado = $this->pessoal->buscarPorUserId((int) $user['id']);
            if ($registroAssociado) {
                $this->pessoal->atualizarFoto((int) $registroAssociado['id'], $avatarPath);
            }
        }

        if (isset($_FILES['documentos'])) {
            try {
                $newDocuments = $this->processSupportingDocuments($_FILES['documentos']);
                if ($newDocuments !== []) {
                    $currentDocuments = $this->decodeDocuments($solicitacao['documentos_json'] ?? null);
                    $mergedDocuments = array_merge($currentDocuments, $newDocuments);
                    $updatePayload['documentos_json'] = json_encode($mergedDocuments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            } catch (\InvalidArgumentException $exception) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não foi possível processar os anexos. Envie apenas PDF, JPG, PNG ou WEBP válidos.'];
                header('Location: ' . BASE_URL . 'associado');
                exit;
            }
        }

        $updatePayload['status'] = 'pendente';

        if ($this->applications->atualizar((int) $solicitacao['id'], $updatePayload)) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Complementação enviada com sucesso. Sua solicitação voltou para análise da diretoria.'];
        } else {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não foi possível enviar a complementação. Tente novamente.'];
        }

        header('Location: ' . BASE_URL . 'associado');
        exit;
    }

    public function enviarSaudacaoAniversariante(): void
    {
        CsrfHelper::verifyOrDie();
        $user = $this->getAuthenticatedMemberUser();

        $pessoalId = (int) ($_POST['pessoal_id'] ?? 0);
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($pessoalId <= 0) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Associado aniversariante inválido para envio da saudação.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if ($message === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Digite a mensagem de saudação antes de enviar.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        if (strlen($message) > 1000) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'A saudação deve ter no máximo 1000 caracteres.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $recipient = $this->findBirthdayRecipient($pessoalId);
        if (!$recipient) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'O associado selecionado não está na lista de aniversariantes de hoje.'];
            header('Location: ' . BASE_URL . 'associado');
            exit;
        }

        $service = new BirthdayGreetingService();
        [$sent, $error] = $service->send(
            [
                'name' => (string) ($recipient['nome'] ?? 'Associado'),
                'email' => (string) ($recipient['user_email'] ?? ''),
            ],
            [
                'display_name' => $this->resolveMemberSenderName($user),
                'context_label' => 'Area restrita do associado no portal AORE/RN',
                'reply_to_email' => trim((string) ($user['email'] ?? '')),
                'reply_to_name' => $this->resolveMemberSenderName($user),
            ],
            $message
        );

        $_SESSION['toast'] = $sent
            ? ['type' => 'success', 'message' => 'Saudação enviada com sucesso pelo e-mail institucional da AORE/RN.']
            : ['type' => 'danger', 'message' => 'Não foi possível enviar a saudação. ' . ($error ?: 'Verifique a configuração SMTP.')];

        header('Location: ' . BASE_URL . 'associado');
        exit;
    }

    private function decodeDocuments(?string $json): array
    {
        if (!$json) {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
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

    private function formatLocationLabel(?string $cidade, ?string $uf): string
    {
        $city = trim((string) $cidade);
        $state = strtoupper(trim((string) $uf));

        if ($city === '' && $state === '') {
            return '-';
        }

        if ($state !== '') {
            if (preg_match('#/[A-Z]{2}$#', $city)) {
                $city = preg_replace('#/[A-Z]{2}$#', '', $city) ?? $city;
                $city = trim($city);
            }

            return $city !== '' ? "{$city}/{$state}" : $state;
        }

        return $city !== '' ? $city : '-';
    }

    private function normalizeDocuments(array $documents): array
    {
        $normalized = [];
        foreach ($documents as $index => $document) {
            $name = (string) ($document['name'] ?? ('Documento ' . ($index + 1)));
            $mimeType = (string) ($document['mime_type'] ?? '');
            $size = (int) ($document['size'] ?? 0);
            $normalized[] = [
                'name' => $name,
                'path' => $document['path'] ?? '',
                'mime_type' => $mimeType,
                'size' => $size,
                'label' => $this->buildDocumentLabel($name, $mimeType, $size),
            ];
        }

        return $normalized;
    }

    private function buildDocumentLabel(string $name, string $mimeType, int $size): string
    {
        $typeLabel = match ($mimeType) {
            'application/pdf' => 'PDF',
            'image/jpeg' => 'JPG',
            'image/png' => 'PNG',
            'image/webp' => 'WEBP',
            default => 'Arquivo',
        };

        $sizeLabel = $size > 0 ? $this->formatBytes($size) : null;
        return trim($name . ' (' . $typeLabel . ($sizeLabel ? ', ' . $sizeLabel : '') . ')');
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        return number_format(max(1, $bytes / 1024), 0, ',', '.') . ' KB';
    }

    private function getAuthenticatedMemberUser(): array
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $role = $_SESSION['user_role'] ?? 'usuario';

        if ($userId <= 0 || in_array($role, ['admin', 'gerente', 'operador'], true)) {
            header('Location: ' . BASE_URL . 'login/admin');
            exit;
        }

        $user = $this->users->buscarPorId($userId);
        if (!$user) {
            header('Location: ' . BASE_URL . 'login/logout');
            exit;
        }

        return $user;
    }

    private function processAvatar(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mimeType = mime_content_type($file['tmp_name']) ?: ($file['type'] ?? '');
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return null;
        }

        $publicRoot = $this->resolvePublicRoot();
        $uploadsDir = $publicRoot . '/' . self::USER_AVATAR_DIR;
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }

        $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = $mimeType === 'image/png' ? 'png' : ($mimeType === 'image/webp' ? 'webp' : 'jpg');
        }

        $filename = uniqid('avatar_', true) . '.' . $extension;
        $destination = $uploadsDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        return self::USER_AVATAR_DIR . $filename;
    }

    private function resolvePublicRoot(): string
    {
        $projectPublic = rtrim(dirname(__DIR__, 3) . '/public', '/');
        if (is_dir($projectPublic)) {
            return $projectPublic;
        }

        return rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? $projectPublic), '/');
    }

    /**
     * Salva documentos comprobatórios e retorna metadados dos novos arquivos.
     *
     * @throws \InvalidArgumentException
     */
    private function processSupportingDocuments(array $files): array
    {
        $normalizedFiles = $this->normalizeUploadedFiles($files);
        if ($normalizedFiles === []) {
            return [];
        }

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        $publicRoot = $this->resolvePublicRoot();
        $uploadsDir = $publicRoot . '/' . self::MEMBERSHIP_DOCUMENTS_DIR;
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }

        $storedDocuments = [];
        foreach ($normalizedFiles as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                throw new \InvalidArgumentException('upload_failed');
            }

            $mimeType = mime_content_type($file['tmp_name']) ?: ($file['type'] ?? '');
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                throw new \InvalidArgumentException('invalid_type');
            }

            $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
            if ($extension === '') {
                $extension = $mimeType === 'application/pdf' ? 'pdf' : 'bin';
            }

            $filename = uniqid('doc_', true) . '.' . $extension;
            $destination = $uploadsDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new \InvalidArgumentException('upload_failed');
            }

            $storedDocuments[] = [
                'name' => (string) $file['name'],
                'path' => self::MEMBERSHIP_DOCUMENTS_DIR . $filename,
                'mime_type' => $mimeType,
                'size' => (int) ($file['size'] ?? 0),
            ];
        }

        return $storedDocuments;
    }

    private function normalizeUploadedFiles(array $files): array
    {
        if (!isset($files['name']) || !is_array($files['name'])) {
            return $files['name'] ?? null ? [$files] : [];
        }

        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return BASE_URL . ltrim($value, '/');
    }

    private function mapBirthdayMember(array $item): array
    {
        $birthDate = trim((string) ($item['nascimento'] ?? ''));
        $age = null;
        if ($birthDate !== '') {
            try {
                $born = new \DateTimeImmutable($birthDate);
                $age = $born->diff(new \DateTimeImmutable('today'))->y;
            } catch (\Throwable $exception) {
                $age = null;
            }
        }

        return [
            'id' => (int) ($item['id'] ?? 0),
            'nome' => (string) ($item['nome'] ?? ''),
            'nome_guerra' => trim((string) ($item['nome_guerra'] ?? '')) ?: 'Associado',
            'numero_militar' => trim((string) ($item['numero_militar'] ?? '')) ?: '-',
            'ano_npor' => trim((string) ($item['ano_npor'] ?? '')) ?: '-',
            'idade' => $age,
            'foto_url' => $this->resolveAssetUrl((string) (($item['foto'] ?? '') ?: ($item['user_avatar'] ?? ''))) ?: (BASE_URL . 'assets/images/conscrito.png'),
            'email' => trim((string) ($item['user_email'] ?? '')),
            'telefone_formatado' => $this->formatPhone((string) ($item['telefone'] ?? '')),
            'birthday_salutation' => $this->buildBirthdaySalutation($item),
            'whatsapp_link' => $this->buildWhatsappLink(
                (string) ($item['telefone'] ?? ''),
                $this->buildBirthdaySalutation($item)
            ),
        ];
    }

    private function findBirthdayRecipient(int $pessoalId): ?array
    {
        $items = $this->pessoal->listarAniversariantesDoDia(date('m-d'), 50);
        foreach ($items as $item) {
            if ((int) ($item['id'] ?? 0) === $pessoalId) {
                return $item;
            }
        }

        return null;
    }

    private function resolveMemberSenderName(array $user): string
    {
        $associado = $this->pessoal->buscarPorUserId((int) ($user['id'] ?? 0));
        if ($associado && trim((string) ($associado['nome'] ?? '')) !== '') {
            return trim((string) $associado['nome']);
        }

        $solicitacao = $this->applications->buscarPorUserId((int) ($user['id'] ?? 0));
        if ($solicitacao && trim((string) ($solicitacao['nome_completo'] ?? '')) !== '') {
            return trim((string) $solicitacao['nome_completo']);
        }

        return trim((string) ($user['username'] ?? 'Associado AORE/RN'));
    }

    private function formatPhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
        }

        return $value;
    }

    private function buildWhatsappLink(string $value, string $salutation): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return null;
        }

        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        $message = rawurlencode(
            trim($salutation) . ' Nesta data especial, receba os votos de saúde, felicidade e um excelente novo ciclo da comunidade AORE/RN. Parabéns pelo seu aniversário.'
        );
        return 'https://wa.me/' . $digits . '?text=' . $message;
    }

    private function buildBirthdaySalutation(array $item): string
    {
        $numero = trim((string) ($item['numero_militar'] ?? ''));
        $nomeGuerra = trim((string) ($item['nome_guerra'] ?? ''));
        $ano = trim((string) ($item['ano_npor'] ?? ''));

        if ($numero !== '' && $numero !== '-' && $nomeGuerra !== '' && $nomeGuerra !== 'Associado' && $ano !== '' && $ano !== '-') {
            return sprintf('Olá Al %s %s/%s!!!', $numero, $nomeGuerra, $ano);
        }

        $nome = trim((string) ($item['nome'] ?? 'Associado'));
        return 'Olá, ' . $nome . '!';
    }

    private function buildBoardSection(): array
    {
        $termModel = new BoardTermModel();
        $membershipModel = new BoardMembershipModel();

        $activeTerm = $termModel->buscarMandatoAtivo();
        if (!$activeTerm) {
            return ['term' => null, 'members' => []];
        }

        $members = array_map(function (array $item): array {
            $avatarPath = trim((string) ($item['associado_foto'] ?: ($item['associado_user_avatar'] ?: '')));
            $cargo = trim((string) ($item['cargo'] ?? ''));
            $funcao = trim((string) ($item['funcao_nome'] ?? ''));

            return [
                'nome' => trim((string) ($item['associado_nome'] ?? '')) ?: 'Associado não vinculado',
                'cargo' => $cargo !== '' ? $cargo : ($funcao !== '' ? $funcao : 'Função institucional'),
                'foto_url' => $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png'),
            ];
        }, $membershipModel->listarPorMandato((int) $activeTerm['id'], true));

        return [
            'term' => $activeTerm,
            'members' => array_slice($members, 0, 6),
        ];
    }

    private function buildInstitutionalPosts(): array
    {
        $result = (new Post())->listarPublico(null, null, 1, 3);
        $posts = $result['data'] ?? [];

        return array_map(function (array $item): array {
            $summary = trim(strip_tags((string) ($item['resumo'] ?? '')));
            if ($summary === '') {
                $summary = trim(strip_tags((string) ($item['conteudo'] ?? '')));
            }

            if ($summary !== '' && function_exists('mb_substr')) {
                $summary = mb_substr($summary, 0, 140, 'UTF-8');
                if (mb_strlen($summary, 'UTF-8') >= 140) {
                    $summary .= '...';
                }
            }

            return [
                'titulo' => (string) ($item['titulo'] ?? 'Comunicado institucional'),
                'categoria' => (string) ($item['categoria_nome'] ?? 'Institucional'),
                'data' => (string) ($item['published_at'] ?? $item['criado_em'] ?? ''),
                'resumo' => $summary,
                'url' => BASE_URL . 'blog/' . rawurlencode((string) ($item['slug'] ?? '')),
            ];
        }, $posts);
    }
}
