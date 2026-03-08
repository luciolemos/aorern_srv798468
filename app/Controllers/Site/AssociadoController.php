<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Helpers\CsrfHelper;
use App\Middleware\AuthMiddleware;
use App\Models\MembershipApplicationModel;
use App\Models\PessoalModel;
use App\Models\User;

class AssociadoController extends Controller
{
    private const USER_AVATAR_DIR = 'uploads/users/';
    private const MEMBERSHIP_DOCUMENTS_DIR = 'uploads/filiacao/';
    private User $users;
    private MembershipApplicationModel $applications;
    private PessoalModel $pessoal;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->users = new User();
        $this->applications = new MembershipApplicationModel();
        $this->pessoal = new PessoalModel();
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

        $this->renderTwig('site/pages/associado', [
            'user' => $user,
            'solicitacao' => $solicitacao,
            'associado' => $associado,
            'documentos' => $documentos,
            'associadoAvatarUrl' => $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png'),
            'statusAssociativoLabel' => $this->formatAssociativeStatus($solicitacao['status_associativo'] ?? $associado['status_associativo'] ?? 'provisorio'),
            'csrf_token' => CsrfHelper::generateToken(),
        ]);
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
}
