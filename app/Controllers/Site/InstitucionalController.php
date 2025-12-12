<?php

namespace App\Controllers\Site;

use App\Core\Controller;

class InstitucionalController extends Controller
{
    private function menuItems(): array
    {
        return [
            [
                'slug' => 'missao',
                'title' => 'Missão',
                'description' => 'Propósito institucional e entregas estratégicas.',
                'icon' => 'bi-bullseye',
                'url' => BASE_URL . 'institucional/missao',
            ],
            [
                'slug' => 'valores',
                'title' => 'Valores',
                'description' => 'Princípios éticos que sustentam nossas equipes.',
                'icon' => 'bi-shield-check',
                'url' => BASE_URL . 'institucional/valores',
            ],
            [
                'slug' => 'visao',
                'title' => 'Visão de Futuro',
                'description' => 'Onde queremos chegar até 2030.',
                'icon' => 'bi-binoculars',
                'url' => BASE_URL . 'institucional/visao',
            ],
            [
                'slug' => 'brasao',
                'title' => 'Brasão Institucional',
                'description' => 'Significados do emblema oficial do CBMRN.',
                'icon' => 'bi-patch-check',
                'url' => BASE_URL . 'institucional/brasao',
            ],
            [
                'slug' => 'hino',
                'title' => 'Hino do CBMRN',
                'description' => 'Letra oficial e orientações de execução.',
                'icon' => 'bi-music-note-beamed',
                'url' => BASE_URL . 'institucional/hino',
            ],
            [
                'slug' => 'pac',
                'title' => 'PAC Cinotécnico',
                'description' => 'Processo Avaliativo Cinotécnico para duplas K9.',
                'icon' => 'bi-clipboard-check',
                'url' => BASE_URL . 'institucional/pac',
            ],
            [
                'slug' => 'links',
                'title' => 'Links Úteis',
                'description' => 'Portais oficiais, legislações e serviços.',
                'icon' => 'bi-link-45deg',
                'url' => BASE_URL . 'institucional/links',
            ],
        ];
    }

    public function index(): void
    {
        $this->renderTwig('site/institutional/index', [
            'breadcrumbs' => [
                ['label' => 'Início', 'url' => BASE_URL],
                ['label' => 'Institucional', 'url' => null],
            ],
            'menuItems' => $this->menuItems(),
            'page' => [
                'title' => 'CBMRN Institucional',
                'tagline' => 'Transparência, memória e planejamento contínuo.',
                'lead' => 'O portal institucional reúne nossa identidade visual, missão, valores e o detalhamento dos processos que sustentam a atuação do 2º SGB/2º GBM no Litoral Sul e Agreste. Cada seção foi pensada para facilitar o acesso de militares, parceiros e cidadãos às informações oficiais.',
                'highlight_stats' => [
                    ['value' => '25', 'label' => 'Municípios cobertos'],
                    ['value' => '24/7', 'label' => 'Monitoramento do CIOSP'],
                    ['value' => '+30', 'label' => 'Projetos comunitários ativos'],
                ],
                'pillars' => [
                    [
                        'title' => 'Identidade preservada',
                        'description' => 'Manual único para brasão, cores e aplicação do nome CBMRN em materiais oficiais.',
                        'icon' => 'bi-palette',
                    ],
                    [
                        'title' => 'Gestão baseada em dados',
                        'description' => 'Protocolos compartilhados entre operações, logística e comunicação.',
                        'icon' => 'bi-bar-chart-line',
                    ],
                    [
                        'title' => 'Integração regional',
                        'description' => 'Rotinas conjuntas com Defesas Civis, SAMU e secretarias municipais.',
                        'icon' => 'bi-diagram-3',
                    ],
                ],
                'milestones' => [
                    [
                        'year' => '2024',
                        'title' => 'Inauguração do 2º SGB/2º GBM',
                        'description' => 'Nova sede em Goianinha com parque de treinamento e setor de prevenção integrado.',
                    ],
                    [
                        'year' => '2025',
                        'title' => 'Plano Operacional Regional',
                        'description' => 'Documento que unifica indicadores de resposta, frota e equipes para 25 municípios.',
                    ],
                    [
                        'year' => '2026',
                        'title' => 'Expansão do PAC Cinotécnico',
                        'description' => 'Duplas K9 dedicadas a busca terrestre e salvamento em estruturas colapsadas.',
                    ],
                ],
            ],
        ]);
    }

    private function renderPage(string $slug, array $page): void
    {
        $page['slug'] = $slug;

        $this->renderTwig('site/institutional/page', [
            'breadcrumbs' => [
                ['label' => 'Início', 'url' => BASE_URL],
                ['label' => 'Institucional', 'url' => BASE_URL . 'institucional'],
                ['label' => $page['title'], 'url' => null],
            ],
            'menuItems' => $this->menuItems(),
            'page' => $page,
        ]);
    }

    public function missao(): void
    {
        $this->renderPage('missao', [
            'title' => 'Missão Institucional',
            'badge' => 'Missão',
            'tagline' => 'Responder com rapidez, prevenir com inteligência e cuidar das pessoas.',
            'lead' => 'Garantimos proteção à vida, ao patrimônio e ao meio ambiente por meio de planejamento baseado em cenários, equipes altamente treinadas e tecnologia aplicada à gestão operacional.',
            'hero_highlights' => [
                ['value' => '25', 'label' => 'Municípios assistidos'],
                ['value' => '12 min', 'label' => 'Tempo médio de resposta'],
                ['value' => '24/7', 'label' => 'Operação contínua'],
            ],
            'sections' => [
                [
                    'title' => 'Diretrizes que nos guiam',
                    'description' => 'A missão do 2º SGB/2º GBM prioriza vidas e reforça o protagonismo da prevenção.',
                    'icon' => 'bi-compass',
                    'items' => [
                        'Atuação integrada com CIOSP, Defesa Civil e SAMU para decisões em tempo real.',
                        'Planejamento orientado por dados climáticos, históricos de ocorrências e índices de vegetação.',
                        'Cuidado continuado aos bombeiros e às vítimas, com acompanhamento pós-ocorrência.',
                    ],
                ],
                [
                    'title' => 'Protocolos permanentes',
                    'description' => 'Cada município atendido possui plano de operação revisado anualmente.',
                    'icon' => 'bi-clipboard-check',
                    'items' => [
                        'Simulados trimestrais de incêndio, salvamento e resposta a desastres.',
                        'Checklist digital de frota, materiais e EPI em todos os turnos.',
                        'Comunicação comunitária com alertas de queimadas e níveis de risco.',
                    ],
                ],
                [
                    'title' => 'Relacionamento com a comunidade',
                    'description' => 'Educação preventiva e presença constante em escolas, feiras e associações rurais.',
                    'icon' => 'bi-people',
                    'items' => [
                        'Projeto Brigadas nas Escolas, com oficinas sobre evacuação e primeiros socorros.',
                        'Patrulhas educativas em áreas de queimadas e pontos turísticos.',
                        'Boletins semanais com recomendações sazonais para pesca, agricultura e turismo.',
                    ],
                ],
            ],
            'spotlight' => [
                'title' => 'Sala de Situação Integrada',
                'description' => 'Centraliza imagens de drones, sensores meteorológicos e rastreamento das guarnições.',
                'chips' => ['Monitoramento climático', 'Painel único de ocorrências', 'Integração com CIOSP'],
            ],
            'resources' => [
                ['label' => 'Plano Operacional 2025', 'type' => 'PDF', 'url' => BASE_URL . 'coverage', 'external' => false],
                ['label' => 'Contato da Seção de Planejamento', 'type' => 'Formulário', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
            'cta' => ['label' => 'Falar com Planejamento', 'url' => BASE_URL . 'contact'],
        ]);
    }

    public function valores(): void
    {
        $this->renderPage('valores', [
            'title' => 'Valores Institucionais',
            'badge' => 'Valores',
            'tagline' => 'Princípios que sustentam cada guarnição.',
            'lead' => 'Nossa cultura é construída sobre coragem, disciplina, empatia e inovação responsável. Esses valores regem promoções, formações e o relacionamento com a sociedade.',
            'hero_highlights' => [
                ['value' => '06', 'label' => 'Princípios oficiais'],
                ['value' => '+30', 'label' => 'Projetos sociais ativos'],
                ['value' => '100%', 'label' => 'Equipes certificadas'],
            ],
            'sections' => [
                [
                    'title' => 'Cuidado com pessoas',
                    'description' => 'Servimos com empatia, respeito às diferenças e atenção às famílias impactadas.',
                    'icon' => 'bi-heart-pulse',
                    'items' => [
                        'Acolhimento psicológico pós-ocorrência para vítimas e bombeiros.',
                        'Cartilha de linguagem inclusiva aplicada aos canais oficiais.',
                        'Programas de visitas guiadas para escolas e idosos.',
                    ],
                ],
                [
                    'title' => 'Integridade e disciplina',
                    'description' => 'Processos decisórios transparentes e fidelidade ao Código de Ética.',
                    'icon' => 'bi-balance-scale',
                    'items' => [
                        'Comissões de ética com participação de oficiais, praças e corregedoria.',
                        'Prestação de contas trimestral sobre recursos e convênios.',
                        'Uso obrigatório de body cams em operações de grande vulto.',
                    ],
                ],
            ],
            'value_cards' => [
                ['title' => 'Coragem', 'description' => 'Agir com firmeza diante de cenários extremos.'],
                ['title' => 'Disciplina', 'description' => 'Cumprir normas e procedimentos com rigor técnico.'],
                ['title' => 'Lealdade', 'description' => 'Honrar a tropa e a confiança da população.'],
                ['title' => 'Empatia', 'description' => 'Humanizar atendimentos e decisões.'],
                ['title' => 'Excelência técnica', 'description' => 'Buscar aprendizado contínuo.'],
                ['title' => 'Inovação responsável', 'description' => 'Adotar tecnologia que amplie a segurança.'],
            ],
            'resources' => [
                ['label' => 'Código de Ética CBMRN', 'type' => 'PDF', 'url' => '#', 'external' => false],
                ['label' => 'Programa Guardiões do Futuro', 'type' => 'Projeto', 'url' => BASE_URL . 'about', 'external' => false],
            ],
        ]);
    }

    public function visao(): void
    {
        $this->renderPage('visao', [
            'title' => 'Visão de Futuro',
            'badge' => 'Planejamento 2030',
            'tagline' => 'Ser referência nordestina em prevenção e resposta integrada.',
            'lead' => 'Nossa visão projeta investimentos em infraestrutura, tecnologia e capacitação para ampliar a presença do CBMRN no território potiguar.',
            'hero_highlights' => [
                ['value' => '2030', 'label' => 'Horizonte estratégico'],
                ['value' => '3 Bases', 'label' => 'Novas unidades planejadas'],
                ['value' => '100%', 'label' => 'Frota conectada'],
            ],
            'sections' => [
                [
                    'title' => 'Infraestrutura e cobertura',
                    'description' => 'Expansão para regiões agrestes e turísticas com maior índice de ocorrências.',
                    'icon' => 'bi-columns-gap',
                    'items' => [
                        'Novos destacamentos em municípios-polo do Agreste e do Litoral Norte.',
                        'Centros de treinamento com torre de incêndio, casa de fumaça e pista de salvamento veicular.',
                        'Modernização de alojamentos e oficinas com energia limpa.',
                    ],
                ],
                [
                    'title' => 'Tecnologia e dados',
                    'description' => 'Conectividade total para frota, estoque e gestão de ocorrências.',
                    'icon' => 'bi-cloud-arrow-up',
                    'items' => [
                        'Tablets embarcados para checklist eletrônico e despacho inteligente.',
                        'Sistema de previsão de risco com base em satélites e modelagem climática.',
                        'Dashboards públicos com indicadores de desempenho.',
                    ],
                ],
                [
                    'title' => 'Gestão do conhecimento',
                    'description' => 'Carreira apoiada em certificações nacionais e internacionais.',
                    'icon' => 'bi-mortarboard',
                    'items' => [
                        'Programa de mestrado profissional com universidades parceiras.',
                        'Intercâmbio com corpos de bombeiros de outros estados.',
                        'Academia digital com trilhas para praças e oficiais.',
                    ],
                ],
            ],
            'timeline' => [
                ['label' => '2025-2026', 'description' => 'Implantação da frota conectada e novos simuladores.'],
                ['label' => '2027-2028', 'description' => 'Bases avançadas no Agreste e expansão do PAC.'],
                ['label' => '2029-2030', 'description' => 'Centro integrado de prevenção e dados abertos.'],
            ],
            'resources' => [
                ['label' => 'Plano Diretor CBMRN', 'type' => 'PDF', 'url' => '#', 'external' => false],
                ['label' => 'Agenda RN Sustentável', 'type' => 'Link externo', 'url' => 'https://www.rn.gov.br', 'external' => true],
            ],
        ]);
    }

    public function brasao(): void
    {
        $this->renderPage('brasao', [
            'title' => 'Brasão Institucional',
            'badge' => 'Identidade Visual',
            'tagline' => 'Símbolo que expressa história, atributos e compromissos.',
            'lead' => 'O brasão do CBMRN reúne elementos que representam coragem, disciplina militar e proteção ao povo potiguar.',
            'hero_highlights' => [
                ['value' => '+40 anos', 'label' => 'Identidade preservada'],
                ['value' => 'Aplicações oficiais', 'label' => 'Uniformes, viaturas e documentos'],
                ['value' => 'Uso controlado', 'label' => 'Por atos normativos internos'],
            ],
            'sections' => [
                [
                    'title' => 'Escudo e elmo',
                    'description' => 'Representam bravura e disciplina do militar bombeiro.',
                    'icon' => 'bi-shield-shaded',
                    'items' => [
                        'Escudo português remete às origens da heráldica militar.',
                        'Elmo frontal simboliza vigilância permanente.',
                        'As machadinhas cruzadas remetem às operações de salvamento.',
                    ],
                ],
                [
                    'title' => 'Cores e significados',
                    'description' => 'Paleta oficial reforça energia, seriedade e confiança.',
                    'icon' => 'bi-palette2',
                    'items' => [
                        'Vermelho/laranja: coragem e prontidão para o combate às chamas.',
                        'Amarelo/dourado: nobreza do serviço prestado à sociedade.',
                        'Azul profundo: proteção às águas e atuação em salvamentos aquáticos.',
                    ],
                ],
                [
                    'title' => 'Aplicações permitidas',
                    'description' => 'Uso em peças gráficas segue manual de identidade visual.',
                    'icon' => 'bi-card-image',
                    'items' => [
                        'Uniformes, viaturas, documentos oficiais e cenografia institucional.',
                        'Proibido distorcer proporções, aplicar filtros ou alterar cores.',
                        'Versões monocromáticas apenas para gravação em baixo-relevo.',
                    ],
                ],
            ],
            'media' => [
                'type' => 'image',
                'src' => BASE_URL . 'assets/images/brasao_cbmrn_oficial.png',
                'alt' => 'Brasão oficial do CBMRN',
                'caption' => 'Utilizar apenas versões autorizadas pela Diretoria de Comunicação Social.',
            ],
            'resources' => [
                ['label' => 'Manual de Identidade Visual', 'type' => 'PDF', 'url' => '#', 'external' => false],
                ['label' => 'Solicitar arquivo vetorial', 'type' => 'Contato', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
        ]);
    }

    public function hino(): void
    {
        $this->renderPage('hino', [
            'title' => 'Hino do CBMRN',
            'badge' => 'Memória e Cultura',
            'tagline' => 'Versos que celebram a bravura e a vocação de servir.',
            'lead' => 'A execução do hino acontece em formaturas, solenidades e eventos cívicos. Utilize arranjos oficiais e respeite o protocolo cerimonial.',
            'hero_highlights' => [
                ['value' => '3 estrofes', 'label' => 'Letra oficial'],
                ['value' => 'Tom sugerido', 'label' => 'Sol maior'],
                ['value' => 'Execução', 'label' => 'Formaturas, honras e premiações'],
            ],
            'sections' => [
                [
                    'title' => 'Orientações de execução',
                    'description' => 'O hino deve anteceder o Hino Nacional quando executado em eventos internos.',
                    'icon' => 'bi-music-note-list',
                    'items' => [
                        'Tempo marcado em 4/4, com fanfarra ou banda marcial.',
                        'Todos em forma de continência, exceto comandantes durante revista.',
                        'Uso de playback oficial apenas quando não houver banda.',
                    ],
                ],
                [
                    'title' => 'Registro e autoria',
                    'description' => 'Letra e melodia cadastradas na Diretoria de Comunicação Social.',
                    'icon' => 'bi-journal-text',
                    'items' => [
                        'Disponibilização mediante ofício simples.',
                        'Versões corais e fanfarra armazenadas no arquivo digital.',
                        'Atualização melódica somente por ato do Comando-Geral.',
                    ],
                ],
            ],
            'lyrics_note' => 'Substitua os versos abaixo pelo texto oficial fornecido pela Diretoria de Comunicação Social.',
            'lyrics' => [
                [
                    'title' => 'Estrofe I',
                    'lines' => [
                        'Salve, bravos bombeiros potiguares,',
                        'Que vigiam sertões, dunas e mar.',
                        'Com coragem, disciplina e esperança,',
                        'Nossa gente vocês vêm amparar.',
                    ],
                ],
                [
                    'title' => 'Estrofe II',
                    'lines' => [
                        'Quando o fogo ameaça nossas casas,',
                        'Lá está o CBMRN a chegar.',
                        'Entre sirenes, orações e abraços,',
                        'Vidas voltam a respirar.',
                    ],
                ],
                [
                    'title' => 'Estrofe III',
                    'lines' => [
                        'Rio Grande do Norte agradecido,',
                        'Entoando a canção do bem-servir.',
                        'Honra e glória aos soldados das chamas,',
                        'Que escolheram amar e proteger.',
                    ],
                ],
            ],
            'resources' => [
                ['label' => 'Solicitar partitura oficial', 'type' => 'Ofício eletrônico', 'url' => BASE_URL . 'contact', 'external' => false],
                ['label' => 'Manual de Cerimonial Militar', 'type' => 'PDF', 'url' => '#', 'external' => false],
            ],
        ]);
    }

    public function pac(): void
    {
        $this->renderPage('pac', [
            'title' => 'Processo Avaliativo Cinotécnico (PAC)',
            'badge' => 'Cinotecnia',
            'tagline' => 'Seleção e manutenção de duplas K9 para busca e salvamento.',
            'lead' => 'O PAC mede desempenho físico, cognitivo e comportamental de cães e condutores designados para missões de busca urbana, rural e em estruturas colapsadas.',
            'hero_highlights' => [
                ['value' => '04 fases', 'label' => 'Processo completo'],
                ['value' => '2x por ano', 'label' => 'Aplicação'],
                ['value' => '+8 duplas', 'label' => 'Ativas no 2º SGB/2º GBM'],
            ],
            'sections' => [
                [
                    'title' => 'Pré-requisitos',
                    'description' => 'Condição física, disciplina e vínculo afetivo são avaliados antes da inscrição.',
                    'icon' => 'bi-list-check',
                    'items' => [
                        'Cão com até 6 anos, laudos veterinários e carteira de vacinação.',
                        'Condutor com curso básico de cinotecnia e CNH categoria "B".',
                        'Disponibilidade para viagens e treinamentos em fins de semana.',
                    ],
                ],
                [
                    'title' => 'Fases avaliativas',
                    'description' => 'Provas combinam estímulos olfativos, obediência e simulações reais.',
                    'icon' => 'bi-flag',
                    'items' => [
                        'Fase 1: obediência básica e vínculo com o condutor.',
                        'Fase 2: prova de faro em ambientes urbanos e rurais.',
                        'Fase 3: resistência física e navegação em trilhas.',
                        'Fase 4: missão integrada com equipes de salvamento.',
                    ],
                ],
                [
                    'title' => 'Homologação e manutenção',
                    'description' => 'Duplas homologadas passam por reciclagens semestrais.',
                    'icon' => 'bi-patch-check',
                    'items' => [
                        'Certificação válida por 24 meses, renovável após avaliação.',
                        'Plano nutricional e veterinário acompanhado pela corporação.',
                        'Registro em banco de dados nacional de cães de busca.',
                    ],
                ],
            ],
            'timeline' => [
                ['label' => 'Janeiro', 'description' => 'Publicação do edital e inscrições.'],
                ['label' => 'Março', 'description' => 'Provas teóricas e práticas.'],
                ['label' => 'Abril', 'description' => 'Homologação e início das reciclagens.'],
                ['label' => 'Agosto', 'description' => '2ª edição do ano (se houver vagas).'],
            ],
            'resources' => [
                ['label' => 'Modelo de edital PAC', 'type' => 'PDF', 'url' => '#', 'external' => false],
                ['label' => 'Contato da Coordenadoria de Cinotecnia', 'type' => 'Telefone', 'url' => 'tel:+558432201919', 'external' => false],
            ],
            'cta' => ['label' => 'Falar com a Coordenadoria K9', 'url' => BASE_URL . 'contact'],
        ]);
    }

    public function links(): void
    {
        $this->renderPage('links', [
            'title' => 'Links Úteis',
            'badge' => 'Serviços e Portais',
            'tagline' => 'Acesso rápido a documentos, legislações e plataformas do CBMRN.',
            'lead' => 'Utilize os canais oficiais para solicitar informações, consultar editais e conhecer os programas do Governo do RN.',
            'hero_highlights' => [
                ['value' => '12', 'label' => 'Portais selecionados'],
                ['value' => 'Atualização', 'label' => 'Revisão trimestral'],
                ['value' => 'HTTPS', 'label' => 'Ambiente seguro'],
            ],
            'links' => [
                [
                    'label' => 'Portal Institucional CBMRN',
                    'description' => 'Notícias oficiais e comunicados do Comando-Geral.',
                    'url' => 'https://www.cbmrn.rn.gov.br',
                    'tag' => 'Governo RN',
                ],
                [
                    'label' => 'Defesa Civil RN',
                    'description' => 'Alertas, planos de contingência e formulários de desastres.',
                    'url' => 'https://www.defesacivil.rn.gov.br',
                    'tag' => 'Parceiro',
                ],
                [
                    'label' => 'Portal da Transparência RN',
                    'description' => 'Execução orçamentária e convênios do Estado.',
                    'url' => 'https://transparencia.rn.gov.br',
                    'tag' => 'Transparência',
                ],
                [
                    'label' => 'SINE/IDEMA - Monitoramento Climático',
                    'description' => 'Dados meteorológicos utilizados na Sala de Situação.',
                    'url' => 'https://www.idema.rn.gov.br',
                    'tag' => 'Dados Abertos',
                ],
                [
                    'label' => 'Ouvidoria Geral do Estado',
                    'description' => 'Protocolos de elogios, denúncias e solicitações.',
                    'url' => 'https://www.rn.gov.br/ouvidoria',
                    'tag' => 'Participação social',
                ],
                [
                    'label' => 'SUDEC - Sistema Integrado de Defesa Civil',
                    'description' => 'Registro de ocorrências e atualização de planos municipais.',
                    'url' => 'https://sudec.gov.br',
                    'tag' => 'Federal',
                ],
            ],
            'resources' => [
                ['label' => 'Canal do Cidadão RN', 'type' => 'Serviço', 'url' => 'https://www.rn.gov.br/servicos', 'external' => true],
                ['label' => 'Contato do CBMRN', 'type' => 'Telefone', 'url' => 'tel:+5584988881911', 'external' => false],
            ],
        ]);
    }
}
