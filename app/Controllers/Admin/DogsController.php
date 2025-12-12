<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\DogModel;
use App\Models\DogBreedModel;

class DogsController extends Controller
{
    private DogModel $model;
    private DogBreedModel $breedModel;

    private const SEX_OPTIONS = [
        'male' => 'Macho',
        'female' => 'Fêmea',
        'unknown' => 'Indefinido'
    ];

    private const STATUS_OPTIONS = [
        'available' => 'Disponível',
        'training' => 'Em treinamento',
        'adopted' => 'Adotado',
        'inactive' => 'Inativo'
    ];

    private const FUNCTION_OPTIONS = [
        'search_rescue' => 'Busca e salvamento',
        'scent' => 'Farejador',
        'guard' => 'Guarda',
        'other' => 'Outros'
    ];

    private const TRAINING_PHASES = [
        'iib' => 'IIB',
        'i3' => 'I3',
        'iiq' => 'IIQ'
    ];

    private const STORAGE_DIR = 'uploads/dogs/';

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->model = new DogModel();
        $this->breedModel = new DogBreedModel();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('dogs:list');
        $request = Request::capture();
        $q = trim($request->query('q', ''));
        $breedId = (int) $request->query('breed');
        $breedId = $breedId > 0 ? $breedId : null;
        $status = $this->normalizeOption($request->query('status'), self::STATUS_OPTIONS);
        $function = $this->normalizeOption($request->query('function'), self::FUNCTION_OPTIONS);
        $sex = $this->normalizeOption($request->query('sex'), self::SEX_OPTIONS);
        $page = max(1, (int) $request->query('page', 1));
            $defaultPerPage = 10;
            $perPageRaw = $request->query('per_page');
            [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
            $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $result = $this->model->paginarComFiltros($page, $perPage, $q ?: null, $breedId, $status, $function, $sex);
        $dogs = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/dogs',
            'query' => array_filter([
                'q' => $q,
                'breed' => $breedId,
                'status' => $status,
                'function' => $function,
                'sex' => $sex,
                    'per_page' => $perPageQueryValue,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $payload = [
            'dogs' => $dogs,
            'q' => $q,
            'filterBreed' => $breedId,
            'filterStatus' => $status,
            'filterFunction' => $function,
            'filterSex' => $sex,
            'breeds' => $this->breedModel->listarTodas(),
            'statusOptions' => self::STATUS_OPTIONS,
            'functionOptions' => self::FUNCTION_OPTIONS,
            'sexOptions' => self::SEX_OPTIONS,
            'trainingOptions' => self::TRAINING_PHASES,
            'pagination' => $pagination,
                'perPageOptions' => PaginationHelper::options($defaultPerPage),
                'perPageSelection' => $perPageSelection,
        ];

        $this->renderTwig('admin/dogs/index', array_merge($payload, AdminHelper::getUserData('dogs')));
    }

    public function create(): void
    {
        PermissionMiddleware::authorize('dogs:create');
        $csrf = CsrfHelper::generateToken();
        $payload = $this->formPayload($csrf);
        $this->renderTwig('admin/dogs/create', array_merge($payload, AdminHelper::getUserData('dogs')));
    }

    public function store(): void
    {
        PermissionMiddleware::authorize('dogs:create');
        $request = Request::capture();
        CsrfHelper::verifyOrDie();

        $nome = trim($request->post('name', ''));
        if ($nome === '') {
            $this->flashAndRedirect('danger', 'Informe o nome do cão.', 'admin/dogs/create');
        }

        $breedId = (int) $request->post('breed_id');
        if ($breedId <= 0 || !$this->breedModel->buscar($breedId)) {
            $this->flashAndRedirect('danger', 'Selecione uma raça válida.', 'admin/dogs/create');
        }

        $slugInput = trim($request->post('slug', ''));
        $slug = $this->generateSlug($slugInput !== '' ? $slugInput : $nome);
        $slug = $this->ensureUniqueSlug($slug);

        $avatar = $this->maybeUploadImage('admin/dogs/create');

        $dados = $this->collectData($request, $breedId, $slug, $avatar);
        $this->model->criar($dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Cão cadastrado com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/dogs');
        exit;
    }

    public function edit(int $id): void
    {
        PermissionMiddleware::authorize('dogs:edit');
        $dog = $this->model->buscar($id);
        if (!$dog) {
            $this->flashAndRedirect('danger', 'Cão não encontrado.', 'admin/dogs');
        }

        $csrf = CsrfHelper::generateToken();
        $payload = $this->formPayload($csrf, $dog);
        $this->renderTwig('admin/dogs/edit', array_merge($payload, AdminHelper::getUserData('dogs')));
    }

    public function update(int $id): void
    {
        PermissionMiddleware::authorize('dogs:edit');
        $request = Request::capture();
        CsrfHelper::verifyOrDie();

        $dog = $this->model->buscar($id);
        if (!$dog) {
            $this->flashAndRedirect('danger', 'Cão não encontrado.', 'admin/dogs');
        }

        $nome = trim($request->post('name', ''));
        if ($nome === '') {
            $this->flashAndRedirect('danger', 'Informe o nome do cão.', 'admin/dogs/edit/' . $id);
        }

        $breedId = (int) $request->post('breed_id');
        if ($breedId <= 0 || !$this->breedModel->buscar($breedId)) {
            $this->flashAndRedirect('danger', 'Selecione uma raça válida.', 'admin/dogs/edit/' . $id);
        }

        $slugInput = trim($request->post('slug', ''));
        $slugBase = $slugInput !== '' ? $slugInput : ($dog['slug'] ?? $nome);
        $slug = $this->generateSlug($slugBase, $dog['slug'] ?? null);
        $slug = $this->ensureUniqueSlug($slug, $id);

        $avatar = $this->maybeUploadImage('admin/dogs/edit/' . $id, $dog['avatar'] ?? null);

        $dados = $this->collectData($request, $breedId, $slug, $avatar);
        $this->model->atualizar($id, $dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Cão atualizado com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/dogs');
        exit;
    }

    public function destroy(int $id): void
    {
        PermissionMiddleware::authorize('dogs:delete');
        $dog = $this->model->buscar($id);
        if (!$dog) {
            $this->flashAndRedirect('danger', 'Cão não encontrado.', 'admin/dogs');
        }

        $this->deleteImageIfLocal($dog['avatar'] ?? null);
        $this->model->deletar($id);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Cão removido com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/dogs');
        exit;
    }

    public function delete(int $id): void
    {
        $this->destroy($id);
    }

    private function collectData(Request $request, int $breedId, string $slug, ?string $avatar): array
    {
        return [
            'name' => trim($request->post('name', '')),
            'slug' => $slug,
            'breed_id' => $breedId,
            'birth_date' => $this->sanitizeDate($request->post('birth_date')),
            'birth_city' => $this->sanitizeNullable($request->post('birth_city')),
            'birth_state' => $this->sanitizeState($request->post('birth_state')),
            'weight_kg' => $this->sanitizeDecimal($request->post('weight_kg')),
            'sex' => $this->normalizeOption($request->post('sex'), self::SEX_OPTIONS) ?? 'unknown',
            'operational_function' => $this->normalizeOption($request->post('operational_function'), self::FUNCTION_OPTIONS),
            'training_phase' => $this->normalizeOption($request->post('training_phase'), self::TRAINING_PHASES),
            'avatar' => $avatar,
            'status' => $this->normalizeOption($request->post('status'), self::STATUS_OPTIONS) ?? 'available',
            'notes' => $this->sanitizeNullable($request->post('notes')),
            'identifying_marks' => $this->sanitizeNullable($request->post('identifying_marks')),
        ];
    }

    private function formPayload(string $csrf, ?array $dog = null): array
    {
        return [
            'csrf_token' => $csrf,
            'dog' => $dog,
            'breeds' => $this->breedModel->listarTodas(),
            'statusOptions' => self::STATUS_OPTIONS,
            'functionOptions' => self::FUNCTION_OPTIONS,
            'sexOptions' => self::SEX_OPTIONS,
            'trainingOptions' => self::TRAINING_PHASES,
        ];
    }

    private function normalizeOption(?string $value, array $options): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower(trim($value));
        return array_key_exists($value, $options) ? $value : null;
    }

    private function sanitizeNullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed !== '' ? $trimmed : null;
    }

    private function sanitizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $date = date_create_from_format('Y-m-d', $value);
        return $date ? $date->format('Y-m-d') : null;
    }

    private function sanitizeState(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $letters = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $value), 0, 2));
        return $letters !== '' ? $letters : null;
    }

    private function sanitizeDecimal(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        $normalized = str_replace(',', '.', $value);
        return is_numeric($normalized) ? $normalized : null;
    }

    private function generateSlug(?string $value, ?string $preserve = null): string
    {
        $base = trim((string) ($value ?? ''));

        if ($base === '' && $preserve) {
            return $preserve;
        }

        if ($base === '') {
            return strtolower('k9-dog-' . uniqid());
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
            $slug = strtolower('k9-dog-' . uniqid());
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
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentPath;
        }

        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->flashAndRedirect('danger', 'Falha ao enviar o arquivo. Tente novamente.', $redirectPath);
        }

        [$newPath, $error] = $this->processImageUpload($_FILES['avatar']);
        if ($newPath === null) {
            $this->flashAndRedirect('danger', $error ?: 'Arquivo inválido. Use JPG, PNG ou WebP (máx. 10MB).', $redirectPath);
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

        $publicDir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        if ($publicDir === '') {
            $publicDir = rtrim(dirname(__DIR__, 3) . '/public', '/');
        }

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

        $filename = 'dog_' . date('YmdHis') . '_' . $random . '.' . $ext;
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
        if (!str_starts_with($normalized, self::STORAGE_DIR)) {
            return;
        }

        $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . '/' . $normalized;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function flashAndRedirect(string $type, string $message, string $path): void
    {
        $_SESSION['toast'] = ['type' => $type, 'message' => $message];
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
