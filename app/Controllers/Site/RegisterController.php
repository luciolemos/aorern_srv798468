<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\MembershipApplicationModel;
use App\Models\MunicipioModel;
use App\Models\User;

class RegisterController extends Controller {
    private const USER_AVATAR_DIR = 'uploads/users/';
    private const MEMBERSHIP_DOCUMENTS_DIR = 'uploads/filiacao/';
    private const DEFAULT_POSTO_GRADUACAO = 'Aspirante a Oficial';
    private const DEFAULT_ARMA_QUADRO = 'Infantaria';
    private const UF_OPTIONS = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
        'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];
    private const ARMA_QUADRO_OPTIONS = [
        'Artilharia',
        'Comunicações',
        'Engenharia',
        'Infantaria',
        'Cavalaria',
        'Aviação',
        'Quadro de Material Bélico',
        'Quadro de Engenheiros Militares',
        'Serviço de Saúde',
        'Serviço de Intendência',
        'Quadro Complementar de Oficiais',
        'Serviço de Assistência Religiosa',
    ];
    private const ANO_NPOR_MIN = 1900;
    private const ANO_NPOR_LOOKBACK = 90;

    public function index() {
        $errorKey = $_GET['error'] ?? null;
        $successKey = $_GET['success'] ?? null;
        $error = null;
        $success = null;

        $messages = [
            'empty_fields' => 'Preencha todos os campos obrigatórios da solicitação.',
            'empty_username' => 'Usuário é obrigatório.',
            'invalid_username' => 'Usuário inválido. Use somente letras sem acento seguidas de 4 números (ex.: csilva1968).',
            'passwords_dont_match' => 'As senhas não conferem.',
            'invalid_email' => 'Email inválido.',
            'password_too_short' => 'A senha deve ter pelo menos 6 caracteres.',
            'username_already_exists' => 'Usuário já existe.',
            'email_already_exists' => 'Email já cadastrado.',
            'invalid_cpf' => 'CPF inválido.',
            'invalid_numero_militar' => 'Campo Nº inválido. Informe exatamente 2 dígitos.',
            'invalid_ano_npor' => 'Ano NPOR inválido.',
            'invalid_location' => 'Selecione UF e cidade válidas (ou deixe ambos em branco).',
            'missing_acceptance' => 'É necessário aceitar o termo de solicitação de filiação.',
            'application_already_exists' => 'Já existe uma solicitação ativa com esses dados.',
            'invalid_documents' => 'Envie apenas PDFs ou imagens JPG, PNG ou WEBP nos comprovantes.',
            'documents_upload_failed' => 'Não foi possível salvar os documentos comprobatórios enviados.',
        ];

        $successMessages = [
            'request_submitted' => 'Solicitação enviada com sucesso. A diretoria analisará seu pedido de filiação e, após aprovação, sua conta de acesso será liberada.',
        ];

        if ($errorKey && isset($messages[$errorKey])) {
            $error = $messages[$errorKey];
        }

        if ($successKey && isset($successMessages[$successKey])) {
            $success = $successMessages[$successKey];
        }
        
        $this->renderTwig('auth/register', [
            'error' => $error,
            'success' => $success,
            'ufs' => self::UF_OPTIONS,
            'arma_quadro_options' => self::ARMA_QUADRO_OPTIONS,
            'ano_npor_options' => $this->buildAnoNporOptions(),
        ]);
    }

    public function store() {
        $username = User::normalizeUsername((string) ($_POST['username'] ?? ''));
        $nome = trim($_POST['nome'] ?? '');
        $nomeMae = trim($_POST['nome_mae'] ?? '');
        $nomePai = trim($_POST['nome_pai'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
        $cam = trim($_POST['cam'] ?? '');
        $rg = trim($_POST['rg'] ?? '');
        $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
        $anoNpor = trim($_POST['ano_npor'] ?? '');
        $postoGraduacao = trim($_POST['posto_graduacao'] ?? '');
        $numeroMilitar = trim($_POST['numero_militar'] ?? '');
        $nomeGuerra = trim($_POST['nome_guerra'] ?? '');
        $turmaNpor = trim($_POST['turma_npor'] ?? '');
        $armaQuadro = trim($_POST['arma_quadro'] ?? '');
        $uf = strtoupper(trim($_POST['uf'] ?? ''));
        $cidade = trim($_POST['cidade'] ?? '');
        $dataNascimento = trim($_POST['data_nascimento'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $aceite = isset($_POST['aceite_termo']) ? 1 : 0;
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Defaults institucionais para primeira carga do formulário
        if ($postoGraduacao === '') {
            $postoGraduacao = self::DEFAULT_POSTO_GRADUACAO;
        }
        if ($armaQuadro === '') {
            $armaQuadro = self::DEFAULT_ARMA_QUADRO;
        }
        // Validations
        if (empty($nome) || empty($email) || empty($password) || empty($password_confirm) || empty($cpf) || empty($cam) || empty($rg) || empty($telefone) || empty($anoNpor) || empty($postoGraduacao) || empty($numeroMilitar) || empty($nomeGuerra) || empty($armaQuadro) || empty($dataNascimento) || empty($uf) || empty($cidade)) {
            header("Location: " . BASE_URL . "register?error=empty_fields");
            exit;
        }
        if (empty($username)) {
            header("Location: " . BASE_URL . "register?error=empty_username");
            exit;
        }

        if (!User::isValidUsernameFormat($username)) {
            header("Location: " . BASE_URL . "register?error=invalid_username");
            exit;
        }

        if ($password !== $password_confirm) {
            header("Location: " . BASE_URL . "register?error=passwords_dont_match");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: " . BASE_URL . "register?error=invalid_email");
            exit;
        }

        if (!in_array($armaQuadro, self::ARMA_QUADRO_OPTIONS, true)) {
            header("Location: " . BASE_URL . "register?error=empty_fields");
            exit;
        }

        if (strlen($cpf) !== 11) {
            header("Location: " . BASE_URL . "register?error=invalid_cpf");
            exit;
        }

        if (!preg_match('/^\d{2}$/', $numeroMilitar)) {
            header("Location: " . BASE_URL . "register?error=invalid_numero_militar");
            exit;
        }

        $currentYear = (int) date('Y');
        if (!ctype_digit($anoNpor) || (int) $anoNpor < self::ANO_NPOR_MIN || (int) $anoNpor > $currentYear) {
            header("Location: " . BASE_URL . "register?error=invalid_ano_npor");
            exit;
        }

        if (strlen($password) < 6) {
            header("Location: " . BASE_URL . "register?error=password_too_short");
            exit;
        }

        if ($aceite !== 1) {
            header("Location: " . BASE_URL . "register?error=missing_acceptance");
            exit;
        }

        if ($uf !== '' && !in_array($uf, self::UF_OPTIONS, true)) {
            header("Location: " . BASE_URL . "register?error=invalid_location");
            exit;
        }

        if (($uf !== '' && $cidade === '') || ($uf === '' && $cidade !== '')) {
            header("Location: " . BASE_URL . "register?error=invalid_location");
            exit;
        }

        $cidadeNormalizada = $cidade !== '' ? $cidade : null;

        // Check if email already exists
        $userModel = new User();
        if ($userModel->usernameExiste($username)) {
            header("Location: " . BASE_URL . "register?error=username_already_exists");
            exit;
        }
        if ($userModel->emailExiste($email)) {
            header("Location: " . BASE_URL . "register?error=email_already_exists");
            exit;
        }

        $applicationModel = new MembershipApplicationModel();
        if ($applicationModel->existeSolicitacaoAtivaPorIdentidade($username, $email, $cpf)) {
            header("Location: " . BASE_URL . "register?error=application_already_exists");
            exit;
        }

        // Avatar opcional
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = $this->processAvatar($_FILES['avatar']);
        }

        $documentosJson = null;
        if (isset($_FILES['documentos'])) {
            try {
                $documentos = $this->processSupportingDocuments($_FILES['documentos']);
                $documentosJson = $documentos !== [] ? json_encode($documentos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
            } catch (\InvalidArgumentException $exception) {
                $errorCode = $exception->getMessage() === 'upload_failed' ? 'documents_upload_failed' : 'invalid_documents';
                header("Location: " . BASE_URL . "register?error={$errorCode}");
                exit;
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $applicationModel->salvar([
            'nome_completo' => $nome,
            'nome_mae' => $nomeMae !== '' ? $nomeMae : null,
            'nome_pai' => $nomePai !== '' ? $nomePai : null,
            'username_desejado' => $username,
            'email' => $email,
            'password_hash' => $hashedPassword,
            'cpf' => $cpf,
            'cam' => $cam,
            'rg' => $rg,
            'data_nascimento' => $dataNascimento ?: null,
            'telefone' => $telefone,
            'cidade' => $cidadeNormalizada,
            'uf' => $uf ?: null,
            'ano_npor' => $anoNpor,
            'posto_graduacao' => $postoGraduacao,
            'numero_militar' => $numeroMilitar,
            'nome_guerra' => $nomeGuerra,
            'turma_npor' => $turmaNpor,
            'arma_quadro' => $armaQuadro,
            'avatar' => $avatarPath,
            'documentos_json' => $documentosJson,
            'observacoes' => $observacoes,
            'aceite_termo' => $aceite,
            'status' => 'pendente',
            'status_associativo' => 'efetivo',
            'observacoes_admin' => null,
        ]);

        header("Location: " . BASE_URL . "register?success=request_submitted");
        exit;
    }

    public function cidadesPorUf(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $uf = strtoupper(trim((string) ($_GET['uf'] ?? '')));
        if (!in_array($uf, self::UF_OPTIONS, true)) {
            echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $municipios = (new MunicipioModel())->listarPorUf($uf, 1200);
            $items = array_map(static function (array $row): array {
                return [
                    'codigo' => $row['codigo'] ?? '',
                    'nome' => $row['nome'] ?? '',
                    'uf' => $row['uf'] ?? '',
                ];
            }, $municipios);

            echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $exception) {
            error_log('Erro ao listar cidades por UF no cadastro: ' . $exception->getMessage());
            echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Salva avatar em uploads/users e retorna o caminho relativo
     */
    private function processAvatar(array $file): ?string {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed, true)) {
            return null;
        }

        $publicRoot = $this->resolvePublicRoot();
        $uploadsDir = $publicRoot . '/' . self::USER_AVATAR_DIR;
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $filename = uniqid('avatar_', true) . '.' . $ext;
        $destPath = $uploadsDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            // retorna caminho relativo para servir via BASE_URL
            return self::USER_AVATAR_DIR . $filename;
        }

        return null;
    }

    /**
     * Salva documentos comprobatórios da solicitação e retorna metadados em array.
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

    private function resolvePublicRoot(): string
    {
        $projectPublic = rtrim(dirname(__DIR__, 3) . '/public', '/');
        if (is_dir($projectPublic)) {
            return $projectPublic;
        }

        return rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? $projectPublic), '/');
    }

    private function buildAnoNporOptions(): array
    {
        $currentYear = (int) date('Y');
        $startYear = max(self::ANO_NPOR_MIN, $currentYear - self::ANO_NPOR_LOOKBACK);
        $years = [];

        for ($year = $currentYear; $year >= $startYear; $year--) {
            $years[] = (string) $year;
        }

        return $years;
    }
}
