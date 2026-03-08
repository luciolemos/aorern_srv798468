<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Middleware\AuthMiddleware;
use App\Models\InstitutionalDocumentModel;

class DocumentosController extends Controller
{
    private InstitutionalDocumentModel $model;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->model = new InstitutionalDocumentModel();
    }

    public function index(): void
    {
        $request = Request::capture();
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $type = trim((string) $request->query('type', ''));
        $page = max(1, (int) $request->query('page', 1));
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);

        $result = $this->model->paginar($page, $perPage, [
            'q' => $q !== '' ? $q : null,
            'status' => $status !== '' ? $status : null,
            'type' => $type !== '' ? $type : null,
        ]);

        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/documentos',
            'query' => array_filter([
                'q' => $q,
                'status' => $status,
                'type' => $type,
                'per_page' => ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $perPageOptions = PaginationHelper::options($defaultPerPage);

        $this->renderTwig('admin/documentos/index', array_merge([
            'documentos' => $result['data'],
            'filters' => [
                'q' => $q,
                'status' => $status,
                'type' => $type,
            ],
            'pagination' => $pagination,
            'perPageOptions' => $perPageOptions,
            'perPageSelection' => $perPageSelection,
            'statusOptions' => $this->model->statusOptions(),
            'typeOptions' => $this->model->typeOptions(),
        ], AdminHelper::getUserData('documentos')));
    }

    public function cadastrar(): void
    {
        $this->renderForm('admin/documentos/cadastrar', [
            'documento' => null,
        ]);
    }

    public function salvar(): void
    {
        CsrfHelper::verifyOrDie();
        $request = Request::capture();
        $payload = $this->preparePayload($request);

        if ($payload === null) {
            header('Location: ' . BASE_URL . 'admin/documentos/cadastrar');
            exit;
        }

        $this->model->salvar($payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Documento cadastrado com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/documentos');
        exit;
    }

    public function editar(int $id): void
    {
        $documento = $this->model->buscar($id);

        if (!$documento) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Documento não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/documentos');
            exit;
        }

        $this->renderForm('admin/documentos/editar', [
            'documento' => $documento,
        ]);
    }

    public function atualizar(int $id): void
    {
        CsrfHelper::verifyOrDie();
        $documento = $this->model->buscar($id);

        if (!$documento) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Documento não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/documentos');
            exit;
        }

        $request = Request::capture();
        $payload = $this->preparePayload($request, $id);

        if ($payload === null) {
            header('Location: ' . BASE_URL . 'admin/documentos/editar/' . $id);
            exit;
        }

        $this->model->atualizar($id, $payload);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Documento atualizado com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/documentos');
        exit;
    }

    public function deletar(int $id): void
    {
        if (!$this->model->buscar($id)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Documento não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/documentos');
            exit;
        }

        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Documento removido com sucesso!'];
        header('Location: ' . BASE_URL . 'admin/documentos');
        exit;
    }

    private function renderForm(string $template, array $data): void
    {
        $this->renderTwig($template, array_merge($data, [
            'csrf_token' => CsrfHelper::generateToken(),
            'statusOptions' => $this->model->statusOptions(),
            'typeOptions' => $this->model->typeOptions(),
        ], AdminHelper::getUserData('documentos')));
    }

    private function preparePayload(Request $request, ?int $ignoreId = null): ?array
    {
        $titulo = trim((string) $request->post('titulo', ''));
        $slugInput = trim((string) $request->post('slug', ''));
        $tipo = trim((string) $request->post('tipo', ''));
        $status = trim((string) $request->post('status', 'draft'));
        $resumo = trim((string) $request->post('resumo', ''));
        $arquivoUrl = trim((string) $request->post('arquivo_url', ''));
        $linkExterno = trim((string) $request->post('link_externo', ''));
        $publishedAt = trim((string) $request->post('publicado_em', ''));
        $ordem = (int) $request->post('ordem', '0');

        if ($titulo === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Informe o título do documento.'];
            return null;
        }

        if (!in_array($tipo, $this->model->typeOptions(), true)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione um tipo válido.'];
            return null;
        }

        if (!in_array($status, $this->model->statusOptions(), true)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione um status válido.'];
            return null;
        }

        $slug = $this->model->ensureUniqueSlug($this->slugify($slugInput !== '' ? $slugInput : $titulo), $ignoreId);

        return [
            'titulo' => $titulo,
            'slug' => $slug,
            'tipo' => $tipo,
            'resumo' => $resumo !== '' ? $resumo : null,
            'arquivo_url' => $arquivoUrl !== '' ? $arquivoUrl : null,
            'link_externo' => $linkExterno !== '' ? $linkExterno : null,
            'status' => $status,
            'publicado_em' => $publishedAt !== '' ? str_replace('T', ' ', $publishedAt) : null,
            'ordem' => max(0, $ordem),
        ];
    }

    private function slugify(string $value): string
    {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated === false) {
            $transliterated = $value;
        }

        $slug = strtolower((string) preg_replace('/[^a-z0-9]+/', '-', $transliterated));
        $slug = trim($slug, '-');

        return $slug !== '' ? substr($slug, 0, 180) : 'documento-' . substr(md5((string) microtime(true)), 0, 8);
    }
}
