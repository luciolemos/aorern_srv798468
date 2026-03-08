<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AdminHelper;
use App\Middleware\AuthMiddleware;
use App\Models\GaleriaCategoriaModel;
use App\Models\GaleriaImagemModel;
use App\Models\Post;

class EventosController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::requireAuth();
    }

    public function index(): void
    {
        $galeria = new GaleriaImagemModel();
        $categorias = new GaleriaCategoriaModel();
        $posts = new Post();

        $this->renderTwig('admin/hub', array_merge(AdminHelper::getUserData('eventos'), [
            'hub' => [
                'eyebrow' => 'Agenda associativa',
                'title' => 'Eventos e agenda institucional',
                'description' => 'Hub inicial para estruturar a futura gestão de solenidades, encontros, convites, reuniões e marcos associativos da AORERN.',
                'stats' => [
                    [
                        'label' => 'Itens visuais recentes',
                        'value' => count($galeria->listarRecentes(12)),
                        'description' => 'Amostra imediata para cobertura de eventos.',
                        'icon' => 'bi-images',
                    ],
                    [
                        'label' => 'Categorias de acervo',
                        'value' => count($categorias->listar()),
                        'description' => 'Classificações que podem virar agenda e memória.',
                        'icon' => 'bi-collection',
                    ],
                    [
                        'label' => 'Comunicados publicados',
                        'value' => $posts->contarPorStatus('published'),
                        'description' => 'Canal pronto para difundir convites e agendas.',
                        'icon' => 'bi-megaphone',
                    ],
                ],
                'highlights' => [
                    'Preparado para receber agenda de eventos e confirmações futuras.',
                    'Pode apoiar a divulgação de encontros, homenagens e solenidades.',
                    'Mantém o painel alinhado ao eixo associativo, sem depender do legado operacional.',
                ],
                'sections' => [
                    [
                        'title' => 'Uso imediato com a estrutura atual',
                        'items' => [
                            'Criar categorias específicas para solenidades, reuniões, encontros e homenagens.',
                            'Publicar notícias e convites no módulo de publicações já ativo.',
                            'Arquivar os registros fotográficos na galeria com curadoria institucional.',
                        ],
                    ],
                    [
                        'title' => 'Quando evoluir esta área',
                        'items' => [
                            'Cadastrar eventos com data, local, responsável e lista de presença.',
                            'Adicionar fluxo de confirmação para convites e encontros.',
                            'Relacionar cada evento a galerias, publicações e homenageados.',
                        ],
                    ],
                ],
                'cards' => [
                    [
                        'title' => 'Galeria institucional',
                        'description' => 'Use a galeria para registrar visualmente eventos, encontros e atividades da associação.',
                        'icon' => 'bi-images',
                        'href' => BASE_URL . 'admin/galeria',
                        'label' => 'Abrir galeria',
                    ],
                    [
                        'title' => 'Categorias de acervo',
                        'description' => 'Crie categorias específicas para eventos, solenidades, reuniões e memórias visuais.',
                        'icon' => 'bi-collection',
                        'href' => BASE_URL . 'admin/galeria-categorias',
                        'label' => 'Abrir categorias',
                    ],
                    [
                        'title' => 'Canal de contato',
                        'description' => 'Enquanto o módulo de eventos não é próprio, use o contato institucional para convites e solicitações de agenda.',
                        'icon' => 'bi-envelope',
                        'href' => BASE_URL . 'contact',
                        'label' => 'Abrir contato público',
                        'external' => true,
                    ],
                ],
                'note' => 'Enquanto não existe um cadastro próprio de eventos, esta área funciona como hub operacional leve para agenda, convites e cobertura visual.',
            ],
        ]));
    }
}
