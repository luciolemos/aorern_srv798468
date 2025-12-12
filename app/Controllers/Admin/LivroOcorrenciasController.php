<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\AdminHelper;
use App\Helpers\CsrfHelper;
use App\Helpers\PaginationHelper;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\LivroOcorrenciaModel;
use App\Models\MunicipioModel;
use DateTime;

class LivroOcorrenciasController extends Controller
{
    private LivroOcorrenciaModel $model;
    private MunicipioModel $municipios;

    private const STATUS = [
        'aberta' => 'Aberta',
        'em_andamento' => 'Em andamento',
        'concluida' => 'Concluída',
        'arquivada' => 'Arquivada',
    ];

    private const SUBGRUPAMENTOS = [
        '1º SGB','2º SGB','3º SGB','4º SGB','5º SGB','6º SGB','7º SGB','8º SGB','9º SGB','10º SGB'
    ];

    private const TIPOS = [
        'Busca e Salvamento',
        'Resgate Marítimo',
        'Incêndio Estrutural',
        'Incêndio Florestal',
        'Defesa Civil',
        'Suporte Pré-Hospitalar',
        'Outros'
    ];

    public function __construct()
    {
        $this->model = new LivroOcorrenciaModel();
        $this->municipios = new MunicipioModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void
    {
        PermissionMiddleware::authorize('livro_ocorrencias:list');
        $request = Request::capture();
        $page = max(1, (int) $request->query('page', 1));
        $filters = [
            'q' => trim($request->query('q', '')),
            'status' => $request->query('status', ''),
            'tipo' => $request->query('tipo', ''),
            'subgrupamento' => $request->query('subgrupamento', ''),
            'municipio' => $request->query('municipio', ''),
            'responsavel' => $request->query('responsavel', ''),
            'data_inicio' => $request->query('data_inicio', ''),
            'data_fim' => $request->query('data_fim', ''),
        ];
        $defaultPerPage = 10;
        $perPageRaw = $request->query('per_page');
        [$perPage, $perPageSelection] = PaginationHelper::resolve($perPageRaw, $defaultPerPage);
        $perPageQueryValue = ($perPageRaw !== null && $perPageRaw !== '') ? $perPageSelection : null;

        $municipioCodigo = ctype_digit((string) $filters['municipio']) ? (int) $filters['municipio'] : null;
        $responsavelId = ctype_digit((string) $filters['responsavel']) ? (int) $filters['responsavel'] : null;

        $municipioSelecionado = $municipioCodigo ? $this->municipios->garantirPorCodigo($municipioCodigo) : null;

        $result = $this->model->paginarComFiltros(
            $page,
            $perPage,
            $filters['q'] ?: null,
            $filters['status'] ?: null,
            $filters['tipo'] ?: null,
            $filters['subgrupamento'] ?: null,
            $municipioCodigo,
            $filters['data_inicio'] ?: null,
            $filters['data_fim'] ?: null,
            $responsavelId
        );

        $registros = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/livro-ocorrencias',
            'query' => array_filter([
                'q' => $filters['q'],
                'status' => $filters['status'],
                'tipo' => $filters['tipo'],
                'subgrupamento' => $filters['subgrupamento'],
                'municipio' => $filters['municipio'],
                'responsavel' => $filters['responsavel'],
                'data_inicio' => $filters['data_inicio'],
                'data_fim' => $filters['data_fim'],
                'per_page' => $perPageQueryValue,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/livro_ocorrencias/index', array_merge([
            'registros' => $registros,
            'pagination' => $pagination,
            'filters' => $filters,
            'statusOptions' => self::STATUS,
            'tipoOptions' => self::TIPOS,
            'subgrupamentos' => self::SUBGRUPAMENTOS,
            'municipioSelecionado' => $municipioSelecionado,
            'perPageOptions' => PaginationHelper::options($defaultPerPage),
            'perPageSelection' => $perPageSelection,
        ], AdminHelper::getUserData('livro-ocorrencias')));
    }

    public function create(): void
    {
        PermissionMiddleware::authorize('livro_ocorrencias:create');
        $defaultDate = date('Y-m-d\TH:i');
        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $protocolo = $oldInput['protocolo'] ?? $this->model->gerarProtocolo($defaultDate . ':00');

        $this->renderTwig('admin/livro_ocorrencias/create', array_merge([
            'csrf_token' => CsrfHelper::generateToken(),
            'statusOptions' => self::STATUS,
            'tipoOptions' => self::TIPOS,
            'subgrupamentos' => self::SUBGRUPAMENTOS,
            'protocolo' => $protocolo,
            'defaultDate' => $oldInput['data_ocorrencia'] ?? $defaultDate,
            'old' => $oldInput,
            'municipioSelecionado' => $oldInput['municipio_label'] ?? null,
        ], AdminHelper::getUserData('livro-ocorrencias')));
    }

    public function store(): void
    {
        PermissionMiddleware::authorize('livro_ocorrencias:create');
        $request = Request::capture();
        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/livro-ocorrencias/create');
            exit;
        }

        CsrfHelper::verifyOrDie();
        $payload = $request->post();

        $validator = Validator::make($payload, [
            'protocolo' => 'required',
            'data_ocorrencia' => 'required',
            'municipio_codigo' => 'required',
            'subgrupamento' => 'required',
            'tipo_ocorrencia' => 'required',
            'descricao' => 'required|min:10',
            'status' => 'required',
        ]);

        $dataOcorrencia = $this->normalizeDateTime($payload['data_ocorrencia'] ?? '');
        $municipioCodigo = isset($payload['municipio_codigo']) ? (int) $payload['municipio_codigo'] : 0;
        $municipio = $municipioCodigo ? $this->municipios->garantirPorCodigo($municipioCodigo) : null;

        if (($payload['municipio_codigo'] ?? '') !== '' && !$municipio) {
            $validator->custom('municipio_codigo', fn () => false, 'Selecione um município válido.');
        }

        if (($payload['data_ocorrencia'] ?? '') !== '' && !$dataOcorrencia) {
            $validator->custom('data_ocorrencia', fn () => false, 'Informe uma data e hora válidas.');
        }

        if (($payload['status'] ?? '') !== '' && !array_key_exists($payload['status'], self::STATUS)) {
            $validator->custom('status', fn () => false, 'Status inválido.');
        }

        if (($payload['subgrupamento'] ?? '') !== '' && !in_array($payload['subgrupamento'], self::SUBGRUPAMENTOS, true)) {
            $validator->custom('subgrupamento', fn () => false, 'Subgrupamento inválido.');
        }

        if (($payload['tipo_ocorrencia'] ?? '') !== '' && !in_array($payload['tipo_ocorrencia'], self::TIPOS, true)) {
            $validator->custom('tipo_ocorrencia', fn () => false, 'Tipo de ocorrência inválido.');
        }

        if ($validator->fails()) {
            $_SESSION['old_input'] = $payload;
            $_SESSION['old_input']['municipio_label'] = $payload['municipio_label'] ?? ($municipio['nome'] ?? '');
            $_SESSION['toast'] = ['type' => 'danger', 'message' => $this->firstError($validator->errors()) ?? 'Erro de validação nos campos informados.'];
            header('Location: ' . BASE_URL . 'admin/livro-ocorrencias/create');
            exit;
        }

        $protocolo = trim($payload['protocolo']);
        if ($this->model->buscarPorProtocolo($protocolo)) {
            $protocolo = $this->model->gerarProtocolo($dataOcorrencia);
        }

        $dados = [
            'protocolo' => $protocolo,
            'data_ocorrencia' => $dataOcorrencia,
            'municipio_codigo' => $municipio['codigo'],
            'municipio_nome' => sprintf('%s/%s', $municipio['nome'], $municipio['uf']),
            'subgrupamento' => $payload['subgrupamento'],
            'tipo_ocorrencia' => $payload['tipo_ocorrencia'],
            'descricao' => $payload['descricao'],
            'relatorio_conclusao' => $payload['relatorio_conclusao'] ?? null,
            'status' => $payload['status'],
            'responsavel_id' => $_SESSION['user_id'] ?? null,
        ];

        $this->model->criar($dados);
        unset($_SESSION['old_input']);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Ocorrência registrada com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/livro-ocorrencias');
        exit;
    }

    public function edit(int $id): void
    {
        PermissionMiddleware::authorize('livro_ocorrencias:edit');
        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Registro não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/livro-ocorrencias');
            exit;
        }

        $registro['data_ocorrencia_formatted'] = $registro['data_ocorrencia']
            ? date('Y-m-d\TH:i', strtotime($registro['data_ocorrencia']))
            : '';

        $oldInput = $_SESSION['old_input'] ?? null;

        $this->renderTwig('admin/livro_ocorrencias/edit', array_merge([
            'csrf_token' => CsrfHelper::generateToken(),
            'registro' => $registro,
            'statusOptions' => self::STATUS,
            'tipoOptions' => self::TIPOS,
            'subgrupamentos' => self::SUBGRUPAMENTOS,
            'municipioSelecionado' => $registro['municipio_nome'],
            'old' => $oldInput,
        ], AdminHelper::getUserData('livro-ocorrencias')));
        unset($_SESSION['old_input']);
    }

    public function update(int $id): void
    {
        PermissionMiddleware::authorize('livro_ocorrencias:edit');
        $request = Request::capture();
        if (!$request->isPost()) {
            header('Location: ' . BASE_URL . 'admin/livro-ocorrencias/edit/' . $id);
            exit;
        }

        CsrfHelper::verifyOrDie();
        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Registro não encontrado.'];
            header('Location: ' . BASE_URL . 'admin/livro-ocorrencias');
            exit;
        }

        $payload = $request->post();
        $validator = Validator::make($payload, [
            'data_ocorrencia' => 'required',
            'municipio_codigo' => 'required',
            'subgrupamento' => 'required',
            'tipo_ocorrencia' => 'required',
            'descricao' => 'required|min:10',
            'status' => 'required',
        ]);

        $dataOcorrencia = $this->normalizeDateTime($payload['data_ocorrencia'] ?? '');
        $municipioCodigo = isset($payload['municipio_codigo']) ? (int) $payload['municipio_codigo'] : $registro['municipio_codigo'];
        $municipio = $municipioCodigo ? $this->municipios->garantirPorCodigo($municipioCodigo) : null;

        if (($payload['municipio_codigo'] ?? '') !== '' && !$municipio) {
            $validator->custom('municipio_codigo', fn () => false, 'Selecione um município válido.');
        }

        if (($payload['data_ocorrencia'] ?? '') !== '' && !$dataOcorrencia) {
            $validator->custom('data_ocorrencia', fn () => false, 'Informe uma data e hora válidas.');
        }

        if (($payload['status'] ?? '') !== '' && !array_key_exists($payload['status'], self::STATUS)) {
            $validator->custom('status', fn () => false, 'Status inválido.');
        }

        if (($payload['subgrupamento'] ?? '') !== '' && !in_array($payload['subgrupamento'], self::SUBGRUPAMENTOS, true)) {
            $validator->custom('subgrupamento', fn () => false, 'Subgrupamento inválido.');
        }

        if (($payload['tipo_ocorrencia'] ?? '') !== '' && !in_array($payload['tipo_ocorrencia'], self::TIPOS, true)) {
            $validator->custom('tipo_ocorrencia', fn () => false, 'Tipo de ocorrência inválido.');
        }

        if ($validator->fails()) {
            $_SESSION['old_input'] = $payload;
            $_SESSION['old_input']['municipio_label'] = $payload['municipio_label'] ?? ($municipio['nome'] ?? '');
            $_SESSION['toast'] = ['type' => 'danger', 'message' => $this->firstError($validator->errors()) ?? 'Erro de validação nos campos informados.'];
            header('Location: ' . BASE_URL . 'admin/livro-ocorrencias/edit/' . $id);
            exit;
        }

        $dados = [
            'data_ocorrencia' => $dataOcorrencia,
            'municipio_codigo' => $municipio['codigo'],
            'municipio_nome' => sprintf('%s/%s', $municipio['nome'], $municipio['uf']),
            'subgrupamento' => $payload['subgrupamento'],
            'tipo_ocorrencia' => $payload['tipo_ocorrencia'],
            'descricao' => $payload['descricao'],
            'relatorio_conclusao' => $payload['relatorio_conclusao'] ?? null,
            'status' => $payload['status'],
        ];

        $this->model->atualizar($id, $dados);
        unset($_SESSION['old_input']);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Registro atualizado com sucesso.'];
        header('Location: ' . BASE_URL . 'admin/livro-ocorrencias');
        exit;
    }

    public function destroy(int $id): void
    {
        PermissionMiddleware::authorize('livro_ocorrencias:delete');
        $this->model->deletar($id);
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Registro removido do livro.'];
        header('Location: ' . BASE_URL . 'admin/livro-ocorrencias');
        exit;
    }

    public function municipios(): void
    {
        if (!PermissionMiddleware::can('livro_ocorrencias:list')
            && !PermissionMiddleware::can('livro_ocorrencias:create')
            && !PermissionMiddleware::can('livro_ocorrencias:edit')) {
            PermissionMiddleware::authorize('livro_ocorrencias:list');
        }
        $request = Request::capture();
        $term = trim($request->query('q', ''));
        $uf = trim($request->query('uf', ''));
        $limit = (int) $request->query('limit', 12);

        $result = $term !== ''
            ? $this->municipios->sugerirMunicipios($term, max(1, min($limit, 25)), $uf ?: null)
            : [];

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    private function normalizeDateTime(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d\TH:i', 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);
            if ($date instanceof DateTime) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function firstError(array $errors): ?string
    {
        foreach ($errors as $fieldErrors) {
            if (is_array($fieldErrors) && isset($fieldErrors[0])) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
}
