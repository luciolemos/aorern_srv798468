<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AdminHelper;
use App\Middleware\AuthMiddleware;
use App\Models\FuncaoModel;
use App\Models\GaleriaImagemModel;
use App\Models\PessoalModel;

class MemoriaController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::requireAuth();
    }

    public function index(): void
    {
        $pessoal = new PessoalModel();
        $funcoes = new FuncaoModel();
        $galeria = new GaleriaImagemModel();

        $this->renderTwig('admin/hub', array_merge(AdminHelper::getUserData('memoria'), [
            'hub' => [
                'eyebrow' => 'Honrarias e memória',
                'title' => 'Memória institucional e reconhecimento',
                'description' => 'Espaço inicial para consolidar acervo histórico, homenagens, distinções e registros simbólicos da AORERN.',
                'stats' => [
                    [
                        'label' => 'Associados na base',
                        'value' => $pessoal->contar(),
                        'description' => 'Cadastro-base para memória biográfica.',
                        'icon' => 'bi-people-fill',
                    ],
                    [
                        'label' => 'Funções registradas',
                        'value' => $funcoes->contar(),
                        'description' => 'Estrutura útil para mandatos e composições históricas.',
                        'icon' => 'bi-diagram-3',
                    ],
                    [
                        'label' => 'Itens visuais recentes',
                        'value' => count($galeria->listarRecentes(12)),
                        'description' => 'Material imediato para acervo e homenagens.',
                        'icon' => 'bi-camera',
                    ],
                ],
                'highlights' => [
                    'Pode concentrar registros de medalhas, homenagens e marcos históricos.',
                    'Apoia a preservação da memória associativa e do legado dos oficiais da reserva.',
                    'Serve como base para o futuro módulo de honrarias e acervo histórico.',
                ],
                'sections' => [
                    [
                        'title' => 'Base já aproveitável',
                        'items' => [
                            'Cadastro de associados como referência para homenagens e registros biográficos.',
                            'Galeria institucional como ponto inicial de preservação fotográfica.',
                            'Materiais públicos como selo, hino e referências simbólicas da associação.',
                        ],
                    ],
                    [
                        'title' => 'Estrutura futura ideal',
                        'items' => [
                            'Cadastro de honrarias, homenageados e distinções por ano ou solenidade.',
                            'Linha do tempo institucional com gestões, marcos e documentos relevantes.',
                            'Acervo histórico com busca por evento, associado e período.',
                        ],
                    ],
                ],
                'cards' => [
                    [
                        'title' => 'Acervo visual',
                        'description' => 'Organize fotos, registros históricos e imagens de cerimônias na galeria administrativa.',
                        'icon' => 'bi-camera',
                        'href' => BASE_URL . 'admin/galeria',
                        'label' => 'Abrir acervo',
                    ],
                    [
                        'title' => 'Associados',
                        'description' => 'Utilize o cadastro de associados como base para futuras homenagens, registros biográficos e memória institucional.',
                        'icon' => 'bi-people-fill',
                        'href' => BASE_URL . 'admin/pessoal',
                        'label' => 'Abrir associados',
                    ],
                    [
                        'title' => 'Selo e materiais oficiais',
                        'description' => 'Acesse a área pública institucional para validar selo, hino e referências simbólicas já publicadas.',
                        'icon' => 'bi-award',
                        'href' => BASE_URL . 'institucional/brasao',
                        'label' => 'Ver materiais públicos',
                        'external' => true,
                    ],
                ],
                'note' => 'Esta área já está pronta para receber um módulo próprio de honrarias sem descartar o acervo e os cadastros que o portal já possui.',
            ],
        ]));
    }
}
