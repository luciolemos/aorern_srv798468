<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
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
        $total_usuarios = $userModel->contar();
        $usuarios_pendentes = $userModel->contarPorStatus('pendente');

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
            'ultimos_posts' => $ultimos_posts,
            'ultimas_imagens' => $ultimas_imagens,
            'ultimos_equipamentos' => $ultimos_equipamentos,
            'ultimas_obras'        => $ultimas_obras,
            'ultimo_login'        => $_SESSION['last_activity'] ?? time(),
        ];

        $this->renderTwig('admin/dashboard', array_merge($dados, AdminHelper::getUserData('dashboard')));
    }
}
