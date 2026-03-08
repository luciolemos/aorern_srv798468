<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\PatrocinadorModel;

class PatrocinadoresController extends Controller
{
    private const LOGO_UPLOAD_DIR = 'uploads/patrocinadores/';
    private PatrocinadorModel $model;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->model = new PatrocinadorModel();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('patrocinadores:list');
        $request = Request::capture();
        $q = trim((string) $request->query('q', ''));
        $ativo = $request->query('ativo');
        $ativo = ($ativo === '1' || $ativo === '0') ? $ativo : '';
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);

        $result = $this->model->paginar($page, $perPage, [
            'q' => $q !== '' ? $q : null,
            'ativo' => $ativo !== '' ? $ativo : null,
        ]);
        $result['data'] = array_map(function (array $item): array {
            $item['telefone'] = $this->formatPhone((string) ($item['telefone'] ?? ''));
            $item['whatsapp'] = $this->formatPhone((string) ($item['whatsapp'] ?? ''));
            return $item;
        }, $result['data']);

        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/patrocinadores',
            'query' => array_filter([
                'q' => $q,
                'ativo' => $ativo,
                'per_page' => ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/patrocinadores/index', array_merge([
            'patrocinadores' => $result['data'],
            'filters' => ['q' => $q, 'ativo' => $ativo],
            'pagination' => $pagination,
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
            'csrf_token' => CsrfHelper::generateToken(),
        ], AdminHelper::getUserData('patrocinadores')));
    }

    public function cadastrar(): void
    {
        PermissionMiddleware::authorize('patrocinadores:create');
        $this->renderForm('admin/patrocinadores/cadastrar', null);
    }

    public function salvar(): void
    {
        PermissionMiddleware::authorize('patrocinadores:create');
        CsrfHelper::verifyOrDie();
        $payload = $this->preparePayload(Request::capture(), null);

        if ($payload === null) {
            header('Location: ' . BASE_URL . 'admin/patrocinadores/cadastrar');
            exit;
        }

        $this->model->salvar($payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Patrocinador cadastrado com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/patrocinadores');
        exit;
    }

    public function editar(int $id): void
    {
        PermissionMiddleware::authorize('patrocinadores:edit');
        $patrocinador = $this->model->buscar($id);
        if (!$patrocinador) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Patrocinador não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/patrocinadores');
            exit;
        }

        $this->renderForm('admin/patrocinadores/editar', $patrocinador);
    }

    public function atualizar(int $id): void
    {
        PermissionMiddleware::authorize('patrocinadores:edit');
        CsrfHelper::verifyOrDie();
        $patrocinador = $this->model->buscar($id);
        if (!$patrocinador) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Patrocinador não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/patrocinadores');
            exit;
        }

        $payload = $this->preparePayload(Request::capture(), $patrocinador);
        if ($payload === null) {
            header('Location: ' . BASE_URL . 'admin/patrocinadores/editar/' . $id);
            exit;
        }
        unset($payload['created_by']);

        $this->model->atualizar($id, $payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Patrocinador atualizado com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/patrocinadores');
        exit;
    }

    public function deletar(int $id): void
    {
        PermissionMiddleware::authorize('patrocinadores:delete');
        CsrfHelper::verifyOrDie();
        $patrocinador = $this->model->buscar($id);
        if (!$patrocinador) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Patrocinador não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/patrocinadores');
            exit;
        }

        $this->model->deletar($id);
        $this->deleteLocalFile((string) ($patrocinador['logo_path'] ?? ''));
        $this->deleteLocalFile((string) ($patrocinador['banner_path'] ?? ''));
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Patrocinador removido com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/patrocinadores');
        exit;
    }

    private function renderForm(string $template, ?array $patrocinador): void
    {
        if ($patrocinador) {
            $patrocinador['telefone'] = $this->formatPhone((string) ($patrocinador['telefone'] ?? ''));
            $patrocinador['whatsapp'] = $this->formatPhone((string) ($patrocinador['whatsapp'] ?? ''));
        }

        $this->renderTwig($template, array_merge([
            'patrocinador' => $patrocinador,
            'csrf_token' => CsrfHelper::generateToken(),
        ], AdminHelper::getUserData('patrocinadores')));
    }

    private function preparePayload(Request $request, ?array $current): ?array
    {
        $nome = trim((string) $request->post('nome', ''));
        $telefone = preg_replace('/\D+/', '', (string) $request->post('telefone', ''));
        $whatsapp = preg_replace('/\D+/', '', (string) $request->post('whatsapp', ''));
        $email = trim((string) $request->post('email', ''));
        $site = trim((string) $request->post('site', ''));
        $instagram = trim((string) $request->post('instagram', ''));
        $descricao = trim((string) $request->post('descricao_curta', ''));
        $ordem = max(0, (int) $request->post('ordem', '0'));
        $ativo = $request->post('ativo', '1') === '1' ? 1 : 0;
        $exibirTextoBanner = $request->post('exibir_texto_banner', '1') === '1' ? 1 : 0;
        $textoCorTitulo = $this->sanitizeHexColor((string) $request->post('texto_cor_titulo', '#FFFFFF'), '#FFFFFF');
        $textoCorDescricao = $this->sanitizeHexColor((string) $request->post('texto_cor_descricao', '#FFFFFF'), '#FFFFFF');
        $iconeCor = $this->sanitizeHexColor((string) $request->post('icone_cor', '#FFFFFF'), '#FFFFFF');

        if ($nome === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe o nome da empresa patrocinadora.'];
            return null;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe um e-mail válido ou deixe o campo em branco.'];
            return null;
        }

        if ($site !== '' && !preg_match('#^https?://#i', $site)) {
            $site = 'https://' . $site;
        }
        $instagram = $this->normalizeInstagram($instagram);

        $logoPath = (string) ($current['logo_path'] ?? '');
        $bannerPath = (string) ($current['banner_path'] ?? '');
        if (isset($_FILES['logo']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $novoLogo = $this->processLogoUpload($_FILES['logo']);
            if ($novoLogo === null) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Logo inválida. Use JPG, PNG ou WEBP até 5MB.'];
                return null;
            }

            if ($logoPath !== '' && $logoPath !== $novoLogo) {
                $this->deleteLocalFile($logoPath);
            }

            $logoPath = $novoLogo;
        }

        if (isset($_FILES['banner']) && ($_FILES['banner']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $novoBanner = $this->processLogoUpload($_FILES['banner']);
            if ($novoBanner === null) {
                $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Banner inválido. Use JPG, PNG ou WEBP até 5MB.'];
                return null;
            }

            if ($bannerPath !== '' && $bannerPath !== $novoBanner) {
                $this->deleteLocalFile($bannerPath);
            }

            $bannerPath = $novoBanner;
        }

        if ($logoPath === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Envie a logo do patrocinador.'];
            return null;
        }

        return [
            'nome' => $nome,
            'telefone' => $telefone !== '' ? $telefone : null,
            'whatsapp' => $whatsapp !== '' ? $whatsapp : null,
            'email' => $email !== '' ? $email : null,
            'site' => $site !== '' ? $site : null,
            'instagram' => $instagram !== '' ? $instagram : null,
            'descricao_curta' => $descricao !== '' ? $descricao : null,
            'logo_path' => $logoPath,
            'banner_path' => $bannerPath !== '' ? $bannerPath : null,
            'exibir_texto_banner' => $exibirTextoBanner,
            'texto_cor_titulo' => $textoCorTitulo,
            'texto_cor_descricao' => $textoCorDescricao,
            'icone_cor' => $iconeCor,
            'ordem' => $ordem,
            'ativo' => $ativo,
            'created_by' => (int) ($_SESSION['user_id'] ?? 0) ?: null,
        ];
    }

    private function processLogoUpload(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return null;
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmp) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($mimeToExt[$mime])) {
            return null;
        }

        $publicRoot = $this->resolvePublicRoot();
        $uploadDir = $publicRoot . '/' . self::LOGO_UPLOAD_DIR;
        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
            return null;
        }

        try {
            $token = bin2hex(random_bytes(4));
        } catch (\Throwable $exception) {
            $token = substr(sha1((string) microtime(true)), 0, 8);
        }

        $filename = 'patrocinador_' . date('YmdHis') . '_' . $token . '.' . $mimeToExt[$mime];
        $dest = $uploadDir . $filename;
        if (!@move_uploaded_file($tmp, $dest)) {
            return null;
        }

        @chmod($dest, 0644);
        return self::LOGO_UPLOAD_DIR . $filename;
    }

    private function deleteLocalFile(string $relativePath): void
    {
        if ($relativePath === '') {
            return;
        }

        $path = $this->resolvePublicRoot() . '/' . ltrim($relativePath, '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function resolvePublicRoot(): string
    {
        $projectPublic = rtrim(dirname(__DIR__, 3) . '/public', '/');
        if (is_dir($projectPublic)) {
            return $projectPublic;
        }

        return rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? $projectPublic), '/');
    }

    private function formatPhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 13 && str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
        }

        return $value;
    }

    private function sanitizeHexColor(string $value, string $fallback): string
    {
        $value = trim($value);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return strtoupper($value);
        }

        return strtoupper($fallback);
    }

    private function normalizeInstagram(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $username = ltrim($value, '@');
        $username = preg_replace('/[^a-zA-Z0-9._]/', '', $username) ?? '';
        if ($username === '') {
            return '';
        }

        return 'https://www.instagram.com/' . $username . '/';
    }
}
