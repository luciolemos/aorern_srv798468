<?php

namespace App\Controllers\Admin;


use App\Config\Permissions;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\BoardMembershipModel;
use App\Models\PessoalModel;
use App\Models\FuncaoModel;
use App\Models\MembershipApplicationModel;
use App\Models\User;
use App\Helpers\EmailTemplate;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class PessoalController extends Controller {

    private PessoalModel $model;
    private BoardMembershipModel $boardMemberships;
    private User $users;
    private MembershipApplicationModel $applications;
    private const PESSOAL_FOTO_DIR = 'uploads/pessoal/';
    private const STATUS_OPTIONS = ['Ativo', 'Afastado', 'Férias', 'Demitido'];
    private const STATUS_ASSOCIATIVO_OPTIONS = ['provisorio', 'efetivo', 'honorario', 'fundador', 'benemerito', 'veterano', 'aluno'];

    public function __construct() {
        $this->model = new PessoalModel();
        $this->boardMemberships = new BoardMembershipModel();
        $this->users = new User();
        $this->applications = new MembershipApplicationModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $q = $request->query('q', '');
        $status = $this->sanitizeStatus($request->query('status'));
        $statusAssociativo = $this->sanitizeStatusAssociativo($request->query('status_associativo'));
        $funcaoId = $this->sanitizeInteger($request->query('funcao_id'));
        $admissaoInicio = $this->sanitizeDate($request->query('admissao_inicio'));
        $admissaoFim = $this->sanitizeDate($request->query('admissao_fim'));
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $filters = [
            'q' => $q ?: null,
            'status' => $status,
            'status_associativo' => $statusAssociativo,
            'funcao_id' => $funcaoId,
            'admissao_inicio' => $admissaoInicio,
            'admissao_fim' => $admissaoFim,
        ];
        $result = $this->model->paginar($page, $perPage, $filters);
        $pessoal = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/pessoal',
            'query' => array_filter([
                'q' => $q,
                'status' => $status,
                'status_associativo' => $statusAssociativo,
                'funcao_id' => $funcaoId,
                'admissao_inicio' => $admissaoInicio,
                'admissao_fim' => $admissaoFim,
                'per_page' => $perPageQueryValue,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $perPageOptions = PaginationHelper::options($defaultPerPage);
        $funcoes = (new FuncaoModel())->listar();
        $statusOptions = self::STATUS_OPTIONS;
        $statusAssociativoOptions = self::STATUS_ASSOCIATIVO_OPTIONS;
        $csrf_token = CsrfHelper::generateToken();
        $roleLabels = $this->getRoleLabels();

        $this->renderTwig('admin/pessoal/index', array_merge(
            compact(
                'pessoal',
                'q',
                'status',
                'statusAssociativo',
                'funcaoId',
                'admissaoInicio',
                'admissaoFim',
                'pagination',
                'perPageOptions',
                'perPageSelection',
                'funcoes',
                'statusOptions',
                'statusAssociativoOptions',
                'csrf_token',
                'roleLabels'
            ),
            AdminHelper::getUserData('pessoal')
        ));
    }

    public function cadastrar(): void {
        PermissionMiddleware::authorize('pessoal:create');
        $funcoes = (new FuncaoModel())->listar();
        $staff_id = $this->generateAssociadoStaffId();
        $statusAssociativoOptions = self::STATUS_ASSOCIATIVO_OPTIONS;

        $csrf_token = CsrfHelper::generateToken();
        $this->renderTwig('admin/pessoal/cadastrar', array_merge(compact('funcoes', 'csrf_token', 'staff_id', 'statusAssociativoOptions'), AdminHelper::getUserData('pessoal')));
    }

    public function salvar(): void {
        PermissionMiddleware::authorize('pessoal:create');
        CsrfHelper::verifyOrDie();
        $request = Request::capture();
        $cpf = preg_replace('/\D/', '', $request->post('cpf', ''));
        
        if (strlen($cpf) !== 11) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'CPF inválido!'];
            header("Location: " . BASE_URL . "admin/pessoal/cadastrar");
            exit;
        }

        $dados = $this->coletarDados($request->post(), $cpf);
        $dados['foto'] = null;
        $dados['user_id'] = null;

        if (empty($dados['funcao_id'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione uma função válida.'];
            header("Location: " . BASE_URL . "admin/pessoal/cadastrar");
            exit;
        }
        
        // Processa foto se enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = $this->processarFotoAssociado($_FILES['foto']);
            if ($foto) {
                $dados['foto'] = $foto;
            }
        }
        
        $this->model->salvar($dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Associado cadastrado com sucesso!'];
        header("Location: " . BASE_URL . "admin/pessoal");
        exit;
    }

    public function editar(int $id): void {
        PermissionMiddleware::authorize('pessoal:edit');
        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Associado não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/pessoal');
            exit;
        }

        $funcoes = (new FuncaoModel())->listar();
        $statusAssociativoOptions = self::STATUS_ASSOCIATIVO_OPTIONS;
        $csrf_token = CsrfHelper::generateToken();
        $roleLabels = $this->getRoleLabels();
        $accessDefaults = $this->buildAccessDefaults($registro);

        $this->renderTwig('admin/pessoal/editar', array_merge(compact('registro', 'funcoes', 'csrf_token', 'statusAssociativoOptions', 'roleLabels', 'accessDefaults'), AdminHelper::getUserData('pessoal')));
    }

    public function visualizar(int $id): void
    {
        PermissionMiddleware::authorize('pessoal:list');
        $registro = $this->model->buscar($id);

        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Associado não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/pessoal');
            exit;
        }

        $roleLabels = $this->getRoleLabels();
        $accessDefaults = $this->buildAccessDefaults($registro);
        $filiacao = null;
        if (!empty($registro['cpf'])) {
            $filiacao = $this->applications->buscarMaisRecentePorCpf((string) $registro['cpf']);
        }
        $csrf_token = CsrfHelper::generateToken();
        $this->renderTwig('admin/pessoal/visualizar', array_merge(
            compact('registro', 'roleLabels', 'accessDefaults', 'csrf_token', 'filiacao'),
            AdminHelper::getUserData('pessoal')
        ));
    }

    public function atualizar(int $id): void {
        PermissionMiddleware::authorize('pessoal:edit');
        CsrfHelper::verifyOrDie();
        $request = Request::capture();
        $cpf = preg_replace('/\D/', '', $request->post('cpf', ''));
        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Associado não encontrado.'];
            header("Location: " . BASE_URL . "admin/pessoal");
            exit;
        }
        
        if (strlen($cpf) !== 11) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'CPF inválido!'];
            header("Location: " . BASE_URL . "admin/pessoal/editar/$id");
            exit;
        }

        $dados = $this->coletarDados($request->post(), $cpf);
        $dados['foto'] = $registro['foto'] ?? null;
        $dados['user_id'] = $registro['user_id'] ?? null;
        $dados['funcao_id'] = $dados['funcao_id'] ?: ($registro['funcao_id'] ?? null);

        if (empty($dados['funcao_id'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione uma função válida.'];
            header("Location: " . BASE_URL . "admin/pessoal/editar/$id");
            exit;
        }
        
        // Processa foto se enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = $this->processarFotoAssociado($_FILES['foto']);
            if ($foto) {
                // Remove foto antiga se existir
                if (!empty($registro['foto'])) {
                    $oldFoto = $this->resolvePublicRoot() . '/' . ltrim((string) $registro['foto'], '/');
                    if (file_exists($oldFoto)) {
                        @unlink($oldFoto);
                    }
                }
                $dados['foto'] = $foto;
            }
        }
        
        $this->model->atualizar($id, $dados);
        $this->syncLinkedUserAvatar($registro, $dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Associado atualizado com sucesso!'];
        header("Location: " . BASE_URL . "admin/pessoal");
        exit;
    }

    public function deletar(int $id): void {
        PermissionMiddleware::authorize('pessoal:delete');
        CsrfHelper::verifyOrDie();

        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Associado não encontrado.'];
            header("Location: " . BASE_URL . "admin/pessoal");
            exit;
        }

        if ($this->boardMemberships->existeVinculoPorPessoal($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não é possível excluir este associado porque ele está vinculado à Diretoria. Remova primeiro o vínculo no módulo de Diretoria.'];
            header("Location: " . BASE_URL . "admin/pessoal");
            exit;
        }

        $this->model->deletar($id);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Associado excluído com sucesso!'];
        header("Location: " . BASE_URL . "admin/pessoal");
        exit;
    }

    public function acesso(int $id): void
    {
        PermissionMiddleware::authorize('users:edit');
        CsrfHelper::verifyOrDie();
        $request = Request::capture();
        $redirectTo = (string) $request->post('redirect_to', 'editar');
        $redirectTo = in_array($redirectTo, ['editar', 'visualizar'], true) ? $redirectTo : 'editar';
        $returnPath = BASE_URL . 'admin/pessoal/' . $redirectTo . '/' . $id;

        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Associado não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/pessoal');
            exit;
        }

        if (($registro['status'] ?? '') !== 'Ativo' || ($registro['status_associativo'] ?? '') !== 'efetivo') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Somente associados Ativos e Sócios Efetivos podem receber acesso institucional.'];
            header('Location: ' . $returnPath);
            exit;
        }

        $linkedUser = !empty($registro['user_id']) ? $this->users->buscarPorId((int) $registro['user_id']) : null;
        if ($linkedUser) {
            $_SESSION['toast'] = ['type' => 'info', 'message' => 'Este associado já possui credencial básica liberada.'];
            header('Location: ' . $returnPath);
            exit;
        }

        $application = $this->applications->buscarMaisRecentePorCpf((string) ($registro['cpf'] ?? ''));
        if (!$application || ($application['status'] ?? null) !== 'aprovada') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não existe solicitação aprovada com credencial válida vinculada a este associado.'];
            header('Location: ' . $returnPath);
            exit;
        }

        $username = User::normalizeUsername((string) ($application['username_desejado'] ?? ''));
        $email = trim((string) ($application['email'] ?? ''));
        $passwordHash = (string) ($application['password_hash'] ?? '');

        if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $passwordHash === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'A solicitação aprovada não contém credencial válida para liberação de acesso.'];
            header('Location: ' . $returnPath);
            exit;
        }

        if (!User::isValidUsernameFormat($username)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'O usuário informado na inscrição não atende ao padrão exigido (letras sem acento + 4 números no final).'];
            header('Location: ' . $returnPath);
            exit;
        }

        if ($this->users->usernameExiste($username)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'O usuário definido na inscrição já está em uso por outra conta.'];
            header('Location: ' . $returnPath);
            exit;
        }

        if ($this->users->emailExiste($email)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'O e-mail definido na inscrição já está em uso por outra conta.'];
            header('Location: ' . $returnPath);
            exit;
        }

        $userId = $this->users->criarERetornarId([
            'username' => $username,
            'email' => $email,
            'password' => $passwordHash,
            'avatar' => $registro['foto'] ?: null,
            'role' => 'usuario',
            'ativo' => 1,
            'status' => 'ativo',
        ]);

        if (!$userId) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Não foi possível liberar a credencial do associado.'];
            header('Location: ' . $returnPath);
            exit;
        }

        $this->model->vincularUsuario($id, $userId);
        $this->applications->atualizar((int) $application['id'], ['user_id' => $userId, 'pessoal_id' => $id]);
        $this->enviarEmailLiberacaoAcesso($application);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Credencial básica liberada com sucesso para o associado.'];

        header('Location: ' . $returnPath);
        exit;
    }

    private function coletarDados(array $post, string $cpf): array {
        return [
            'staff_id'      => !empty($post['staff_id']) ? trim((string) $post['staff_id']) : $this->generateAssociadoStaffId(),
            'nome'          => trim($post['nome'] ?? ''),
            'cpf'           => $cpf,
            'nascimento'    => trim($post['nascimento'] ?? '') ?: null,
            'telefone'      => preg_replace('/\D/', '', $post['telefone'] ?? ''),
            'funcao_id'     => isset($post['funcao_id']) ? (int) $post['funcao_id'] : (isset($post['workRole']) ? (int) $post['workRole'] : null),
            'obra_id'       => null,
            'data_admissao' => trim($post['data_admissao'] ?? ''),
            'status'        => $post['status'] ?? 'Ativo',
            'status_associativo' => $this->sanitizeStatusAssociativo($post['status_associativo'] ?? 'provisorio') ?? 'provisorio',
            'jornada'       => null,
            'observacoes'   => trim($post['observacoes'] ?? '')
        ];
    }

    /**
     * Processa upload de foto de associado
     */
    private function processarFotoAssociado(array $file): ?string
    {
        // Validações básicas
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Tamanho máximo: 50MB
        $max_size = 50 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return null;
        }

        // Tipos permitidos
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $tipos_permitidos)) {
            return null;
        }

        // Verifica se arquivo temporário existe
        if (!file_exists($file['tmp_name'])) {
            return null;
        }

        // Caminho absoluto
        $publicRoot = $this->resolvePublicRoot();
        $upload_dir = $publicRoot . '/' . self::PESSOAL_FOTO_DIR;

        // Garante que o diretório existe
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0755, true)) {
                return null;
            }
        }

        // Verifica permissões de escrita
        if (!is_writable($upload_dir)) {
            @chmod($upload_dir, 0777);
        }

        // Extensão do arquivo
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Valida extensão
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $ext_permitidas)) {
            return null;
        }

        // Nome único do arquivo
        $filename = 'foto_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $filepath = $upload_dir . $filename;

        // Move o arquivo
        if (!@move_uploaded_file($file['tmp_name'], $filepath)) {
            return null;
        }

        // Ajusta permissões do arquivo
        @chmod($filepath, 0644);

        return self::PESSOAL_FOTO_DIR . $filename;
    }

    private function resolvePublicRoot(): string
    {
        $projectPublic = rtrim(dirname(__DIR__, 3) . '/public', '/');
        if (is_dir($projectPublic)) {
            return $projectPublic;
        }

        return rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? $projectPublic), '/');
    }

    private function sanitizeInteger(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return ctype_digit((string) $value) ? (int) $value : null;
    }

    private function sanitizeStatus(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        return in_array($status, self::STATUS_OPTIONS, true) ? $status : null;
    }

    private function sanitizeStatusAssociativo(?string $statusAssociativo): ?string
    {
        if ($statusAssociativo === null || $statusAssociativo === '') {
            return null;
        }

        return in_array($statusAssociativo, self::STATUS_ASSOCIATIVO_OPTIONS, true) ? $statusAssociativo : null;
    }

    private function sanitizeDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private function getRoleLabels(): array
    {
        return [
            'admin' => Permissions::getRoleLabel('admin'),
            'gerente' => Permissions::getRoleLabel('gerente'),
            'operador' => Permissions::getRoleLabel('operador'),
            'usuario' => Permissions::getRoleLabel('usuario'),
        ];
    }

    private function buildAccessDefaults(array $registro): array
    {
        $application = null;
        if (!empty($registro['cpf'])) {
            $application = $this->applications->buscarMaisRecentePorCpf((string) $registro['cpf']);
        }

        $username = (string) ($registro['user_username']
            ?? $application['username_desejado']
            ?? $this->suggestUsername((string) ($registro['nome'] ?? 'associado')));

        $email = (string) ($registro['user_email']
            ?? $application['email']
            ?? '');

        return [
            'username' => $username,
            'email' => $email,
            'source' => $application ? 'filiacao' : 'manual',
            'application_status' => $application['status'] ?? null,
            'has_application' => (bool) $application,
        ];
    }

    private function suggestUsername(string $name): string
    {
        $value = trim($name);
        if ($value === '') {
            return 'associado';
        }

        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if (is_string($converted)) {
                $value = $converted;
            }
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z]+/', '', $value) ?? 'associado';
        $value = trim($value);
        if ($value === '') {
            $value = 'associado';
        }

        return $value . date('Y');
    }

    private function enviarEmailLiberacaoAcesso(array $application): void
    {
        if (empty($application['email'])) {
            return;
        }

        try {
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
            $mail->addAddress($application['email'], $application['nome_completo'] ?? 'Associado');
            $mail->Subject = 'Acesso liberado ao portal da AORE/RN';
            $mail->isHTML(true);
            $mail->Body = EmailTemplate::render(
                'Acesso liberado ao portal',
                'Sua credencial básica de associado foi liberada pela administração da AORE/RN.',
                sprintf(
                    '<p>Prezado(a) %s, sua credencial de associado foi liberada.</p><p>Você já pode acessar o portal com o usuário <strong>%s</strong> e a senha definida no momento da inscrição.</p><p>Eventuais funções administrativas continuam dependentes de ato posterior da administração da associação.</p>',
                    htmlspecialchars((string) ($application['nome_completo'] ?? 'Associado'), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string) ($application['username_desejado'] ?? ''), ENT_QUOTES, 'UTF-8')
                ),
                [[
                    'label' => 'Entrar no portal',
                    'url' => BASE_URL . 'login/admin',
                    'background' => '#0b5cab',
                ]]
            );
            $mail->AltBody = "Sua credencial básica de associado foi liberada. Acesse o portal em " . BASE_URL . "login/admin com o usuário {$application['username_desejado']}.";
            $mail->send();
        } catch (Exception $exception) {
            error_log('Erro ao enviar email de liberação de acesso: ' . $mail->ErrorInfo);
        }
    }

    private function generateAssociadoStaffId(): string
    {
        $prefix = 'ASSOC-' . date('Ymd');
        for ($sequence = 1; $sequence <= 999; $sequence++) {
            $staffId = sprintf('%s-%03d', $prefix, $sequence);
            if (!$this->model->buscarPorStaffId($staffId)) {
                return $staffId;
            }
        }

        return $prefix . '-' . substr((string) time(), -3);
    }

    private function syncLinkedUserAvatar(array $registroAtual, array $dadosAtualizados): void
    {
        $userId = (int) ($registroAtual['user_id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        $avatarPath = $dadosAtualizados['foto'] ?? null;
        $this->users->atualizar($userId, ['avatar' => $avatarPath]);
    }
}
