<?php

namespace App\Controllers\Admin;


use App\Helpers\AdminHelper;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\PessoalModel;
use App\Models\FuncaoModel;
use App\Models\ObraModel;

class PessoalController extends Controller {

    private PessoalModel $model;

    public function __construct() {
        $this->model = new PessoalModel();
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $request = Request::capture();
        $q = $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        
        $result = $this->model->paginar($page, $perPage, $q ?: null);
        $pessoal = $result['data'];
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'admin/pessoal',
            'query' => array_filter([
                'q' => $q,
            ], fn($value) => $value !== null && $value !== ''),
        ]);

        $this->renderTwig('admin/pessoal/index', array_merge(compact('pessoal', 'q', 'pagination'), AdminHelper::getUserData('pessoal')));
    }

    public function cadastrar(): void {
        PermissionMiddleware::authorize('pessoal:create');
        $obras   = (new ObraModel())->listarObrasSimples();
        $funcoes = (new FuncaoModel())->listar();

        $this->renderTwig('admin/pessoal/cadastrar', array_merge(compact('obras', 'funcoes'), AdminHelper::getUserData('pessoal')));
    }

    public function salvar(): void {
        PermissionMiddleware::authorize('pessoal:create');
        $request = Request::capture();
        $cpf = preg_replace('/\D/', '', $request->post('cpf', ''));
        
        if (strlen($cpf) !== 11) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'CPF inválido!'];
            header("Location: " . BASE_URL . "admin/pessoal/cadastrar");
            exit;
        }

        $dados = $this->coletarDados($request->post(), $cpf);
        $dados['foto'] = null;

        if (empty($dados['funcao_id'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione uma função válida.'];
            header("Location: " . BASE_URL . "admin/pessoal/cadastrar");
            exit;
        }
        
        // Processa foto se enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = $this->processarAvatarBombeiro($_FILES['foto']);
            if ($foto) {
                $dados['foto'] = $foto;
            }
        }
        
        $this->model->salvar($dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Bombeiro cadastrado com sucesso!'];
        header("Location: " . BASE_URL . "admin/pessoal");
        exit;
    }

    public function editar(int $id): void {
        PermissionMiddleware::authorize('pessoal:edit');
        $registro = $this->model->buscar($id);
        if (!$registro) {
            die("Bombeiro não encontrado.");
        }

        $obras   = (new ObraModel())->listarObrasSimples();
        $funcoes = (new FuncaoModel())->listar();

        $this->renderTwig('admin/pessoal/editar', array_merge(compact('registro', 'obras', 'funcoes'), AdminHelper::getUserData('pessoal')));
    }

    public function atualizar(int $id): void {
        PermissionMiddleware::authorize('pessoal:edit');
        $request = Request::capture();
        $cpf = preg_replace('/\D/', '', $request->post('cpf', ''));
        $registro = $this->model->buscar($id);
        if (!$registro) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Bombeiro não encontrado.'];
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

        if (empty($dados['funcao_id'])) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Selecione uma função válida.'];
            header("Location: " . BASE_URL . "admin/pessoal/editar/$id");
            exit;
        }
        
        // Processa foto se enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = $this->processarAvatarBombeiro($_FILES['foto']);
            if ($foto) {
                // Remove foto antiga se existir
                if (!empty($registro['foto'])) {
                    $old_foto = $_SERVER['DOCUMENT_ROOT'] . '/' . $registro['foto'];
                    if (file_exists($old_foto)) {
                        @unlink($old_foto);
                    }
                }
                $dados['foto'] = $foto;
            }
        }
        
        $this->model->atualizar($id, $dados);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Bombeiro atualizado com sucesso!'];
        header("Location: " . BASE_URL . "admin/pessoal");
        exit;
    }

    public function deletar(int $id): void {
        PermissionMiddleware::authorize('pessoal:delete');
        $this->model->deletar($id);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Bombeiro excluído com sucesso!'];
        header("Location: " . BASE_URL . "admin/pessoal");
        exit;
    }

    private function coletarDados(array $post, string $cpf): array {
        return [
            'staff_id'      => $post['staff_id'] ?? 'FIREMAN-' . date('YmdHis'),
            'nome'          => trim($post['nome'] ?? ''),
            'cpf'           => $cpf,
            'nascimento'    => trim($post['nascimento'] ?? '') ?: null,
            'telefone'      => preg_replace('/\D/', '', $post['telefone'] ?? ''),
            'funcao_id'     => isset($post['workRole']) ? (int) $post['workRole'] : null,
            'obra_id'       => $post['obra_id'] ?? null,
            'data_admissao' => trim($post['data_admissao'] ?? ''),
            'status'        => $post['status'] ?? 'Ativo',
            'jornada'       => $post['jornada'] ?? null,
            'observacoes'   => trim($post['observacoes'] ?? '')
        ];
    }

    /**
     * Processa upload de avatar de bombeiro
     */
    private function processarAvatarBombeiro(array $file): ?string
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
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/funcionarios/avatars/';

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
        $filename = 'avatar_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $filepath = $upload_dir . $filename;

        // Move o arquivo
        if (!@move_uploaded_file($file['tmp_name'], $filepath)) {
            return null;
        }

        // Ajusta permissões do arquivo
        @chmod($filepath, 0644);

        return 'assets/funcionarios/avatars/' . $filename;
    }
}
