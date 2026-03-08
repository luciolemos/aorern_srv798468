<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AdminHelper;
use App\Middleware\AuthMiddleware;
use App\Models\FuncaoModel;
use App\Models\GaleriaCategoriaModel;
use App\Models\Post;
use App\Models\PostCategoryModel;
use App\Models\User;

class InstitucionalController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::requireAuth();
    }

    public function index(): void
    {
        $posts = new Post();
        $postCategories = new PostCategoryModel();
        $users = new User();
        $funcoes = new FuncaoModel();
        $galeriaCategorias = new GaleriaCategoriaModel();

        $this->renderTwig('admin/hub', array_merge(AdminHelper::getUserData('institucional'), [
            'hub' => [
                'eyebrow' => 'Núcleo institucional',
                'title' => 'Gestão institucional da AORERN',
                'description' => 'Área destinada à organização dos conteúdos permanentes da associação, identidade visual, documentos oficiais e bases públicas de referência.',
                'stats' => [
                    [
                        'label' => 'Publicações',
                        'value' => $posts->contarPorStatus('published'),
                        'description' => 'Conteúdos institucionais já publicados.',
                        'icon' => 'bi-journal-text',
                    ],
                    [
                        'label' => 'Rascunhos e pendências',
                        'value' => $posts->contarPorStatus('pending'),
                        'description' => 'Itens aguardando revisão editorial.',
                        'icon' => 'bi-hourglass-split',
                    ],
                    [
                        'label' => 'Categorias editoriais',
                        'value' => count($postCategories->listar()),
                        'description' => 'Temas organizados para o portal.',
                        'icon' => 'bi-tags',
                    ],
                    [
                        'label' => 'Cargos cadastrados',
                        'value' => $funcoes->contar(),
                        'description' => 'Estrutura base para diretoria e funções.',
                        'icon' => 'bi-diagram-3',
                    ],
                ],
                'highlights' => [
                    'Centralizar missão, valores, visão e marcos institucionais.',
                    'Consolidar arquivos oficiais, selo, hino e materiais de referência.',
                    'Apoiar a futura gestão de documentos, diretoria e downloads institucionais.',
                ],
                'sections' => [
                    [
                        'title' => 'O que já pode ser governado aqui',
                        'items' => [
                            'Conteúdo institucional do portal por meio das publicações e categorias editoriais.',
                            'Estrutura de diretoria e cargos a partir do módulo de funções já disponível.',
                            'Referências visuais e arquivos permanentes publicados na área pública.',
                        ],
                    ],
                    [
                        'title' => 'Próxima camada recomendada',
                        'items' => [
                            'Criar cadastro próprio de documentos oficiais, mandatos e composições da diretoria.',
                            'Separar downloads institucionais, marca, timbres e formulários em um módulo dedicado.',
                            'Mapear políticas, termos e páginas permanentes que hoje ainda dependem do conteúdo estático.',
                        ],
                    ],
                ],
                'cards' => [
                    [
                        'title' => 'Publicações institucionais',
                        'description' => 'Gerencie notícias, comunicados e conteúdos que alimentam o portal público da associação.',
                        'icon' => 'bi-journal-text',
                        'href' => BASE_URL . 'admin/posts',
                        'label' => 'Abrir publicações',
                    ],
                    [
                        'title' => 'Categorias editoriais',
                        'description' => 'Organize os temas do portal para separar notícias, memória, notas e informes institucionais.',
                        'icon' => 'bi-tags',
                        'href' => BASE_URL . 'admin/post-categories',
                        'label' => 'Abrir categorias',
                    ],
                    [
                        'title' => 'Cargos e funções',
                        'description' => 'Reaproveite o módulo existente para estruturar diretoria, representações e frentes institucionais.',
                        'icon' => 'bi-diagram-3',
                        'href' => BASE_URL . 'admin/funcoes',
                        'label' => 'Abrir cargos',
                    ],
                    [
                        'title' => 'Portal institucional público',
                        'description' => 'Acesse a área institucional do site para revisar a apresentação pública e validar a coerência editorial.',
                        'icon' => 'bi-box-arrow-up-right',
                        'href' => BASE_URL . 'institucional',
                        'label' => 'Ver área pública',
                        'external' => true,
                    ],
                ],
                'note' => sprintf(
                    'Modo compatível ativo: esta área já se apoia em %d usuários do painel e %d categorias de acervo sem alterar a estrutura legada.',
                    $users->contar(),
                    count($galeriaCategorias->listar())
                ),
            ],
        ]));
    }
}
