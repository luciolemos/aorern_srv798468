<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\DogBreedModel;

class DogBreedsController extends Controller
{
    private DogBreedModel $model;
    private const STORAGE_DIR = 'uploads/dog_breeds/';
    private const LEGACY_DIRS = ['uploads/dogs_breeds/'];

    private const SIZE_OPTIONS = [
        'small' => 'Porte pequeno',
        'medium' => 'Porte médio',
        'large' => 'Porte grande',
        'giant' => 'Porte gigante',
    ];

    private const FUNCTION_OPTIONS = [
        'companion' => 'Companhia',
        'guard' => 'Guarda',
        'hunting' => 'Caça',
        'herding' => 'Pastor',
        'working' => 'Trabalho',
        'terrier' => 'Terrier',
        'sporting' => 'Esportivo',
        'toy' => 'Miniatura',
        'non-sporting' => 'Não esportivo',
    ];

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->model = new DogBreedModel();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('dog_breeds:list');
        $request = Request::capture();
        $q = trim($request->query('q', ''));
        $size = $this->normalizeSize($request->query('size'));
        $function = $this->normalizeFunction($request->query('function'));
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $result = $this->model->paginarComFiltros($page, $perPage, $q ?: null, $size, $function);
        $breeds = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/dog-breeds',
            'query' => array_filter([
                'q' => $q,
                'size' => $size,
                'function' => $function,
                'per_page' => $perPageQueryValue,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $payload = [
            'breeds' => $breeds,
            'q' => $q,
            'filterSize' => $size,
            'filterFunction' => $function,
            'sizeOptions' => self::SIZE_OPTIONS,
            'functionOptions' => self::FUNCTION_OPTIONS,
            'pagination' => $pagination,
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
        ];

        $this->renderTwig('admin/dog_breeds/index', array_merge($payload, AdminHelper::getUserData('dog-breeds')));
    }

    public function create(): void
    {
        PermissionMiddleware::authorize('dog_breeds:create');
        $csrf = CsrfHelper::generateToken();
        $payload = [
            'csrf_token' => $csrf,
            'sizeOptions' => self::SIZE_OPTIONS,
            'functionOptions' => self::FUNCTION_OPTIONS,
        ];

        $this->renderTwig('admin/dog_breeds/create', array_merge($payload, AdminHelper::getUserData('dog-breeds')));
    }

    public function store(): void
    {
        PermissionMiddleware::authorize('dog_breeds:create');
        $request = Request::capture();
        CsrfHelper::verifyOrDie();

        $name = trim($request->post('name', ''));
        if ($name === '') {
            $this->flashAndRedirect('danger', 'Informe o nome da raça.', 'admin/dog-breeds/create');
        }

        $slugInput = trim($request->post('slug', ''));
        $slug = $this->generateSlug($slugInput !== '' ? $slugInput : $name);
        $slug = $this->ensureUniqueSlug($slug);

        $dados = [
            'name' => $name,
            'slug' => $slug,
            'size' => $this->normalizeSize($request->post('size')) ?? 'medium',
            'function' => $this->normalizeFunction($request->post('function')),
            'origin' => $this->sanitizeNullable($request->post('origin')),
            'description' => $this->sanitizeNullable($request->post('description')),
            'image_url' => $this->maybeUploadImage('admin/dog-breeds/create'),
        ];

        $this->model->criar($dados);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Raça cadastrada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/dog-breeds');
        exit;
    }

    public function edit(int $id): void
    {
        PermissionMiddleware::authorize('dog_breeds:edit');
        $breed = $this->model->buscar($id);
        if (!$breed) {
            $this->flashAndRedirect('danger', 'Raça não encontrada.', 'admin/dog-breeds');
        }

        $csrf = CsrfHelper::generateToken();
        $payload = [
            'breed' => $breed,
            'csrf_token' => $csrf,
            'sizeOptions' => self::SIZE_OPTIONS,
            'functionOptions' => self::FUNCTION_OPTIONS,
        ];

        $this->renderTwig('admin/dog_breeds/edit', array_merge($payload, AdminHelper::getUserData('dog-breeds')));
    }

    public function update(int $id): void
    {
        PermissionMiddleware::authorize('dog_breeds:edit');
        $request = Request::capture();
        CsrfHelper::verifyOrDie();
        $breed = $this->model->buscar($id);
        if (!$breed) {
            $this->flashAndRedirect('danger', 'Raça não encontrada.', 'admin/dog-breeds');
        }

        $name = trim($request->post('name', ''));
        if ($name === '') {
            $this->flashAndRedirect('danger', 'Informe o nome da raça.', 'admin/dog-breeds/edit/' . $id);
        }

        $slugInput = trim($request->post('slug', ''));
        $slugBase = $slugInput !== '' ? $slugInput : ($breed['slug'] ?? $name);
        $slug = $this->generateSlug($slugBase, $breed['slug'] ?? null);
        $slug = $this->ensureUniqueSlug($slug, $id);

        $dados = [
            'name' => $name,
            'slug' => $slug,
            'size' => $this->normalizeSize($request->post('size')) ?? 'medium',
            'function' => $this->normalizeFunction($request->post('function')),
            'origin' => $this->sanitizeNullable($request->post('origin')),
            'description' => $this->sanitizeNullable($request->post('description')),
            'image_url' => $this->maybeUploadImage('admin/dog-breeds/edit/' . $id, $breed['image_url'] ?? null),
        ];

        $this->model->atualizar($id, $dados);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Raça atualizada com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/dog-breeds');
        exit;
    }

    public function destroy(int $id): void
    {
        PermissionMiddleware::authorize('dog_breeds:delete');
        $breed = $this->model->buscar($id);
        if (!$breed) {
            $this->flashAndRedirect('danger', 'Raça não encontrada.', 'admin/dog-breeds');
        }

        if ($this->model->temCachorros($id)) {
            $this->flashAndRedirect('danger', 'Não é possível excluir: existem cães vinculados a esta raça.', 'admin/dog-breeds');
        }

        $this->deleteImageIfLocal($breed['image_url'] ?? null);
        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Raça removida com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/dog-breeds');
        exit;
    }

    public function delete(int $id): void
    {
        $this->destroy($id);
    }

    private function normalizeSize(?string $value): ?string
    {
        $value = $value !== null ? strtolower(trim($value)) : null;
        return $value && array_key_exists($value, self::SIZE_OPTIONS) ? $value : null;
    }

    private function normalizeFunction(?string $value): ?string
    {
        $value = $value !== null ? strtolower(trim($value)) : null;
        return $value && array_key_exists($value, self::FUNCTION_OPTIONS) ? $value : null;
    }

    private function sanitizeNullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed !== '' ? $trimmed : null;
    }

    private function generateSlug(?string $value, ?string $preserve = null): string
    {
        $base = trim((string) ($value ?? ''));

        if ($base === '' && $preserve) {
            return $preserve;
        }

        if ($base === '') {
            return strtolower('k9-' . uniqid());
        }

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
        if ($transliterated === false || $transliterated === null) {
            $transliterated = $base;
        }

        $slug = strtolower($transliterated);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug ?? '');
        $slug = trim((string) $slug, '-');

        if ($slug === '' && $preserve) {
            return $preserve;
        }

        if ($slug === '') {
            $slug = strtolower('k9-' . uniqid());
        }

        return substr($slug, 0, 140);
    }

    private function ensureUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $counter = 2;

        while ($this->model->slugExiste($slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function maybeUploadImage(string $redirectPath, ?string $currentPath = null): ?string
    {
        if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentPath;
        }

        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            $this->flashAndRedirect('danger', 'Falha ao enviar a imagem. Tente novamente.', $redirectPath);
        }

        [$newPath, $error] = $this->processImageUpload($_FILES['image_file']);
        if ($newPath === null) {
            $message = $error ?: 'Arquivo inválido. Use JPG, PNG ou WebP (máx. 10MB).';
            $this->flashAndRedirect('danger', $message, $redirectPath);
        }

        if ($currentPath && $currentPath !== $newPath) {
            $this->deleteImageIfLocal($currentPath);
        }

        return $newPath;
    }

    private function processImageUpload(array $file): array
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [null, 'Nenhum arquivo válido foi enviado.'];
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if (($file['size'] ?? 0) > $maxSize) {
            return [null, 'O arquivo excede o limite de 10MB.'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        if ($mime === false || !in_array($mime, $allowedMime, true)) {
            return [null, 'Formato não suportado. Utilize JPG, PNG ou WebP.'];
        }

        $extensionMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $ext = $extensionMap[$mime] ?? strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!$ext) {
            return [null, 'Não foi possível identificar a extensão da imagem.'];
        }

        $publicDir = $this->resolvePublicRoot();
        $targetDir = $publicDir . '/' . self::STORAGE_DIR;
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) {
            return [null, 'Falha ao preparar o diretório de upload. Verifique permissões.'];
        }

        if (!is_writable($targetDir) && !@chmod($targetDir, 0775)) {
            return [null, 'Diretório de upload não possui permissão de escrita.'];
        }

        try {
            $random = bin2hex(random_bytes(4));
        } catch (\Throwable $e) {
            return [null, 'Falha ao gerar nome para o arquivo.'];
        }

        $filename = 'breed_' . date('YmdHis') . '_' . $random . '.' . $ext;
        $destination = $targetDir . $filename;

        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            return [null, 'Não foi possível mover o arquivo para o diretório de upload.'];
        }

        @chmod($destination, 0644);

        return [self::STORAGE_DIR . $filename, null];
    }

    private function deleteImageIfLocal(?string $path): void
    {
        if (!$path) {
            return;
        }

        $normalized = ltrim($path, '/');
        $managedDirs = array_merge([self::STORAGE_DIR], self::LEGACY_DIRS);
        $isManaged = false;
        foreach ($managedDirs as $dir) {
            if (str_starts_with($normalized, $dir)) {
                $isManaged = true;
                break;
            }
        }

        if (!$isManaged) {
            return;
        }

        $fullPath = $this->resolvePublicRoot() . '/' . $normalized;
        if (is_file($fullPath)) {
            @unlink($fullPath);
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

    private function flashAndRedirect(string $type, string $message, string $path): void
    {
        $_SESSION['toast'] = ['type' => $type, 'message' => $message];
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
