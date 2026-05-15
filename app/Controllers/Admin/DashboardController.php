<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\CsrfHelper;
use App\Middleware\AuthMiddleware;
use App\Models\FuncaoModel;
use App\Models\PessoalModel;
use App\Models\CategoriaModel;
use App\Models\EquipamentoModel;
use App\Models\ObraModel;
use App\Models\LivroOcorrenciaModel;
use App\Models\User as UserModel;
use App\Models\Post;
use App\Models\PostCategoryModel;
use App\Models\GaleriaImagemModel;
use App\Models\GaleriaCategoriaModel;
use App\Helpers\AdminHelper;
use App\Services\BirthdayGreetingService;

class DashboardController extends Controller {
    public function index() {
        // Protege a rota admin
        AuthMiddleware::requireAuth();
        
        // Estatísticas principais
        $total_funcoes       = (new FuncaoModel())->contar();
        $total_pessoal       = (new PessoalModel())->contar();
        $total_categoria_eqp = (new CategoriaModel())->contar();
        $total_equipamentos  = (new EquipamentoModel())->contar();
        $total_obras         = (new ObraModel())->contar();

        $livroModel = new LivroOcorrenciaModel();
        $subgrupamentoFiltro = "2\xC2\xBA SGB"; // ordinal indicator (\xC2\xBA) keeps file ASCII while matching the DB enum
        $total_ocorrencias = $livroModel->contarTodos($subgrupamentoFiltro);
        $ocorrencias_abertas = $livroModel->contarPorStatus('aberta', $subgrupamentoFiltro);
        $ocorrencias_concluidas = $livroModel->contarPorStatus('concluida', $subgrupamentoFiltro);

        $userModel = new UserModel();
        $rolesPainel = ['admin', 'gerente', 'operador'];
        $total_usuarios = $userModel->contarPorRoles($rolesPainel);
        $usuarios_pendentes = $userModel->contarPorStatusERoles('pendente', $rolesPainel);

        $postModel = new Post();
        $postsPendentes = $postModel->contarPorStatus('pending');
        $postsPublicados = $postModel->contarPorStatus('published');
        $ultimos_posts = array_slice($postModel->todos(), 0, 5);

        $postCategoryModel = new PostCategoryModel();
        $total_categorias_posts = count($postCategoryModel->listar());

        $galeriaImagemModel = new GaleriaImagemModel();
        $ultimas_imagens = $galeriaImagemModel->listarRecentes(5);

        $galeriaCategoriaModel = new GaleriaCategoriaModel();
        $total_categorias_galeria = count($galeriaCategoriaModel->listar());
        
        // Atividades recentes
        $pessoalModel = new PessoalModel();
        $equipamentoModel = new EquipamentoModel();
        $obraModel = new ObraModel();
        
        $ultimos_bombeiros = $pessoalModel->all('id DESC', 5);
        $aniversariantes_hoje = array_map(
            fn(array $item): array => $this->mapBirthdayMember($item),
            $pessoalModel->listarAniversariantesDoDia(date('m-d'), 8)
        );
        $ultimos_equipamentos = $equipamentoModel->all('id DESC', 5);
        $ultimas_obras = $obraModel->all('id DESC', 3);

        $dados = [
            'total_funcoes'       => $total_funcoes,
            'total_pessoal'       => $total_pessoal,
            'total_categoria_eqp' => $total_categoria_eqp,
            'total_equipamentos'  => $total_equipamentos,
            'total_obras'         => $total_obras,
            'total_ocorrencias'   => $total_ocorrencias,
            'ocorrencias_abertas' => $ocorrencias_abertas,
            'ocorrencias_concluidas' => $ocorrencias_concluidas,
            'total_usuarios'      => $total_usuarios,
            'usuarios_pendentes'  => $usuarios_pendentes,
            'posts_pendentes'     => $postsPendentes,
            'posts_publicados'    => $postsPublicados,
            'total_categorias_posts' => $total_categorias_posts,
            'total_categorias_galeria' => $total_categorias_galeria,
            'ultimos_bombeiros' => $ultimos_bombeiros,
            'aniversariantes_hoje' => $aniversariantes_hoje,
            'ultimos_posts' => $ultimos_posts,
            'ultimas_imagens' => $ultimas_imagens,
            'ultimos_equipamentos' => $ultimos_equipamentos,
            'ultimas_obras'        => $ultimas_obras,
            'ultimo_login'        => $_SESSION['last_activity'] ?? time(),
            'csrf_token' => CsrfHelper::generateToken(),
        ];

        $this->renderTwig('admin/dashboard', array_merge($dados, AdminHelper::getUserData('dashboard')));
    }

    public function enviarSaudacaoAniversariante(): void
    {
        AuthMiddleware::requireAuth();
        CsrfHelper::verifyOrDie();

        $pessoalId = (int) ($_POST['pessoal_id'] ?? 0);
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($pessoalId <= 0) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Associado aniversariante inválido para envio da saudação.'];
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }

        if ($message === '') {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Digite a mensagem de saudação antes de enviar.'];
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }

        if (strlen($message) > 1000) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'A saudação deve ter no máximo 1000 caracteres.'];
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }

        $recipient = $this->findBirthdayRecipient($pessoalId);
        if (!$recipient) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'O associado selecionado não está na lista de aniversariantes de hoje.'];
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }

        $service = new BirthdayGreetingService();
        [$sent, $error] = $service->send(
            [
                'name' => (string) ($recipient['nome'] ?? 'Associado'),
                'email' => (string) ($recipient['user_email'] ?? ''),
            ],
            [
                'display_name' => trim((string) ($_SESSION['user_name'] ?? 'Administracao AORE/RN')),
                'context_label' => 'Administracao do painel institucional AORE/RN',
                'reply_to_email' => trim((string) ($_SESSION['user_email'] ?? '')),
                'reply_to_name' => trim((string) ($_SESSION['user_name'] ?? 'Administracao AORE/RN')),
            ],
            $message
        );

        $_SESSION['toast'] = $sent
            ? ['type' => 'success', 'message' => 'Saudação enviada com sucesso pelo e-mail institucional da AORE/RN.']
            : ['type' => 'danger', 'message' => 'Não foi possível enviar a saudação. ' . ($error ?: 'Verifique a configuração SMTP.')];

        header('Location: ' . BASE_URL . 'admin/dashboard');
        exit;
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

        $avatarPath = trim((string) ($item['foto'] ?? '')) ?: trim((string) ($item['user_avatar'] ?? ''));
        if ($avatarPath !== '' && !preg_match('#^https?://#i', $avatarPath)) {
            $avatarPath = BASE_URL . ltrim($avatarPath, '/');
        }

        return [
            'id' => (int) ($item['id'] ?? 0),
            'nome' => (string) ($item['nome'] ?? ''),
            'nome_guerra' => trim((string) ($item['nome_guerra'] ?? '')) ?: 'Associado',
            'numero_militar' => trim((string) ($item['numero_militar'] ?? '')) ?: '-',
            'ano_npor' => trim((string) ($item['ano_npor'] ?? '')) ?: '-',
            'idade' => $age,
            'foto_url' => $avatarPath !== '' ? $avatarPath : (BASE_URL . 'assets/images/conscrito.png'),
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
        $items = (new PessoalModel())->listarAniversariantesDoDia(date('m-d'), 50);
        foreach ($items as $item) {
            if ((int) ($item['id'] ?? 0) === $pessoalId) {
                return $item;
            }
        }

        return null;
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
            trim($salutation) . ' Nesta data especial, a administração da AORE/RN registra votos de saúde, felicidade e um excelente novo ciclo. Parabéns pelo seu aniversário.'
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
}
