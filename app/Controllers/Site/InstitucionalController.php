<?php

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Models\BoardMembershipModel;
use App\Models\BoardTermModel;
use App\Models\InstitutionalDocumentModel;
use App\Models\MembershipApplicationModel;
use App\Models\Post;

class InstitucionalController extends Controller
{
    private function documentTypeLabel(string $type): string
    {
        $labels = [
            'estatuto' => 'Estatuto',
            'ata' => 'Ata',
            'oficio' => 'Ofício',
            'formulario' => 'Formulário',
            'politica' => 'Política',
            'termo' => 'Termo',
            'marca' => 'Identidade visual',
            'outro' => 'Documento oficial',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    private function availableDocumentFilters(): array
    {
        return [
            'marca' => 'Marca',
            'politica' => 'Política',
            'termo' => 'Termo',
            'ata' => 'Ata',
            'oficio' => 'Ofício',
            'estatuto' => 'Estatuto',
            'outro' => 'Outros',
        ];
    }

    private function availableDocumentSorts(): array
    {
        return [
            'editorial' => 'Ordem editorial',
            'recentes' => 'Mais recentes',
        ];
    }

    private function documentPreview(array $documento): array
    {
        $url = $documento['arquivo_url'] ?: ($documento['link_externo'] ?: BASE_URL . 'institucional/documentos');
        $type = (string) ($documento['tipo'] ?? 'outro');
        $arquivoUrl = (string) ($documento['arquivo_url'] ?? '');
        $thumbnailMap = [
            'selo-aorern-svg' => BASE_URL . 'assets/images/aore1.png',
            'selo-aorern-png' => BASE_URL . 'assets/images/aore1.png',
            'cabecalho-email-aorern-png' => BASE_URL . 'assets/images/brand/thumb-cabecalho-email-png.svg',
            'cabecalho-email-aorern-svg' => BASE_URL . 'assets/images/brand/thumb-cabecalho-email-svg.svg',
            'timbre-a4-aorern-png' => BASE_URL . 'assets/images/brand/thumb-timbre-a4-png.svg',
            'timbre-a4-aorern-svg' => BASE_URL . 'assets/images/brand/thumb-timbre-a4-svg.svg',
            'manual-identidade-visual-aorern-svg' => BASE_URL . 'assets/images/brand/thumb-manual-identidade-svg.svg',
            'manual-identidade-visual-aorern-pdf' => BASE_URL . 'assets/images/brand/thumb-manual-identidade-pdf.svg',
        ];
        $thumbnail = $thumbnailMap[$documento['slug'] ?? ''] ?? null;
        $typeThumbnailMap = [
            'estatuto' => BASE_URL . 'assets/images/brand/thumb-estatuto.svg',
            'ata' => BASE_URL . 'assets/images/brand/thumb-ata.svg',
            'oficio' => BASE_URL . 'assets/images/brand/thumb-oficio.svg',
            'politica' => BASE_URL . 'assets/images/brand/thumb-politica.svg',
            'termo' => BASE_URL . 'assets/images/brand/thumb-termo.svg',
            'outro' => BASE_URL . 'assets/images/brand/thumb-documento-oficial.svg',
        ];
        $thumbnail ??= $typeThumbnailMap[$type] ?? null;
        $hasImagePreview = (bool) $thumbnail || ($type === 'marca' && preg_match('/\.(svg|png|jpe?g|webp)$/i', $arquivoUrl));

        $iconMap = [
            'estatuto' => 'bi-bank',
            'ata' => 'bi-journal-richtext',
            'oficio' => 'bi-envelope-paper',
            'formulario' => 'bi-ui-checks-grid',
            'politica' => 'bi-shield-lock',
            'termo' => 'bi-file-earmark-medical',
            'marca' => 'bi-palette2',
            'outro' => 'bi-file-earmark-text',
        ];

        return [
            'id' => $documento['id'] ?? null,
            'title' => $documento['titulo'],
            'slug' => $documento['slug'],
            'summary' => $documento['resumo'] ?: 'Material institucional publicado para consulta e referência oficial.',
            'category' => $this->documentTypeLabel($type),
            'status' => $documento['status'] ?? null,
            'published_at' => $documento['publicado_em'] ?? null,
            'order' => (int) ($documento['ordem'] ?? 0),
            'order_label' => sprintf('Ordem editorial %02d', (int) ($documento['ordem'] ?? 0)),
            'url' => $url,
            'external' => true,
            'preview_image' => $thumbnail ?: ($hasImagePreview ? $arquivoUrl : null),
            'preview_icon' => $iconMap[$type] ?? 'bi-file-earmark-text',
        ];
    }

    private function documentFilterChips(?string $active = null): array
    {
        $chips = [[
            'slug' => null,
            'label' => 'Todos',
            'url' => BASE_URL . 'institucional/documentos',
            'active' => $active === null,
        ]];

        foreach ($this->availableDocumentFilters() as $slug => $label) {
            $chips[] = [
                'slug' => $slug,
                'label' => $label,
                'url' => BASE_URL . 'institucional/documentos?categoria=' . rawurlencode($slug),
                'active' => $active === $slug,
            ];
        }

        return $chips;
    }

    private function documentSortChips(string $active = 'editorial', ?string $categoria = null, string $basePath = 'institucional/documentos'): array
    {
        $chips = [];

        foreach ($this->availableDocumentSorts() as $slug => $label) {
            $query = ['ordenacao' => $slug];
            if ($categoria !== null) {
                $query['categoria'] = $categoria;
            }

            $chips[] = [
                'slug' => $slug,
                'label' => $label,
                'url' => BASE_URL . $basePath . '?' . http_build_query($query),
                'active' => $active === $slug,
            ];
        }

        return $chips;
    }

    private function documentQueryParams(?string $categoria, string $ordenacao, string $q = ''): array
    {
        return array_filter([
            'categoria' => $categoria,
            'ordenacao' => $ordenacao !== 'editorial' ? $ordenacao : null,
            'q' => $q !== '' ? $q : null,
        ], static fn($value) => $value !== null && $value !== '');
    }

    private function publishedDocumentUrl(?string $slug, ?string $type, string $fallback): string
    {
        $documentModel = new InstitutionalDocumentModel();

        if ($slug) {
            $documento = $documentModel->buscarPublicadoPorSlug($slug);
            if ($documento) {
                return $documento['arquivo_url'] ?: ($documento['link_externo'] ?: $fallback);
            }
        }

        if ($type) {
            $documento = $documentModel->buscarPrimeiroPublicadoPorTipo($type);
            if ($documento) {
                return $documento['arquivo_url'] ?: ($documento['link_externo'] ?: $fallback);
            }
        }

        return $fallback;
    }

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
                'title' => 'Selo Institucional',
                'description' => 'Significados do emblema oficial da AORE/RN.',
                'icon' => 'bi-patch-check',
                'url' => BASE_URL . 'institucional/brasao',
            ],
            [
                'slug' => 'identidade-visual',
                'title' => 'Identidade Visual',
                'description' => 'Arquivos oficiais de marca, cabeçalhos e timbres.',
                'icon' => 'bi-palette2',
                'url' => BASE_URL . 'institucional/identidade-visual',
            ],
            [
                'slug' => 'hino',
                'title' => 'Hino da AORE/RN',
                'description' => 'Letra oficial e orientações de execução.',
                'icon' => 'bi-music-note-beamed',
                'url' => BASE_URL . 'institucional/hino',
            ],
            [
                'slug' => 'links',
                'title' => 'Links Úteis',
                'description' => 'Portais oficiais, legislações e serviços.',
                'icon' => 'bi-link-45deg',
                'url' => BASE_URL . 'institucional/links',
            ],
            [
                'slug' => 'documentos',
                'title' => 'Documentos Oficiais',
                'description' => 'Estatuto, atas, políticas e referências institucionais.',
                'icon' => 'bi-file-earmark-text',
                'url' => BASE_URL . 'institucional/documentos',
            ],
            [
                'slug' => 'downloads-marca',
                'title' => 'Downloads de Marca',
                'description' => 'Selo, manual visual, timbres e cabeçalhos oficiais.',
                'icon' => 'bi-download',
                'url' => BASE_URL . 'institucional/downloads-marca',
            ],
            [
                'slug' => 'governanca',
                'title' => 'Governança',
                'description' => 'Mandatos, composição diretiva e referências normativas.',
                'icon' => 'bi-diagram-3',
                'url' => BASE_URL . 'institucional/governanca',
            ],
            [
                'slug' => 'busca',
                'title' => 'Busca Institucional',
                'description' => 'Pesquisar documentos, governança e conteúdos publicados.',
                'icon' => 'bi-search',
                'url' => BASE_URL . 'institucional/busca',
            ],
        ];
    }

    public function index(): void
    {
        $documentModel = new InstitutionalDocumentModel();
        $manual = $documentModel->buscarPublicadoPorSlug('manual-identidade-visual-aorern-svg');
        $documentos = array_map(fn(array $documento): array => $this->documentPreview($documento), array_slice(
            $documentModel->paginar(1, 4, ['status' => 'published'])['data'] ?? [],
            0,
            4
        ));

        $this->renderTwig('site/institutional/index', [
            'breadcrumbs' => [
                ['label' => 'Início', 'url' => BASE_URL],
                ['label' => 'Institucional', 'url' => null],
            ],
            'menuItems' => $this->menuItems(),
            'page' => [
                'title' => 'AORERN Institucional',
                'tagline' => 'Transparência, memória e planejamento contínuo.',
                'lead' => 'O portal institucional reúne a identidade visual, a missão, os valores e os referenciais que orientam a atuação associativa da AORERN. Cada seção foi organizada para facilitar o acesso de associados, parceiros e visitantes às informações oficiais da entidade.',
                'spotlight' => [
                    'title' => 'Portal vivo da associação',
                    'description' => 'Este espaço consolida os fundamentos institucionais da AORERN, preserva sua memória associativa e organiza conteúdos oficiais de referência para consulta permanente.',
                    'items' => [
                        'Conteúdo alinhado à natureza associativa, cívica e institucional da AORERN.',
                        'Linguagem unificada com a marca, os símbolos e os canais oficiais da entidade.',
                        'Acesso rápido a páginas institucionais, arquivos oficiais e canais de contato.',
                    ],
                ],
                'featured_manual' => $manual ? $this->documentPreview($manual) : null,
                'documents' => $documentos,
                'pillars' => [
                    [
                        'title' => 'Identidade preservada',
                        'description' => 'Diretrizes para uso do selo, das cores e da identidade visual da AORERN em materiais oficiais.',
                        'icon' => 'bi-palette',
                    ],
                    [
                        'title' => 'Memória e referência institucional',
                        'description' => 'Organização de conteúdos que preservam a história, os valores e os símbolos da associação.',
                        'icon' => 'bi-bank',
                    ],
                    [
                        'title' => 'Integração associativa',
                        'description' => 'Estruturação de canais e páginas que fortalecem o vínculo entre oficiais da reserva, parceiros e sociedade.',
                        'icon' => 'bi-people',
                    ],
                ],
                'milestones' => [
                    [
                        'year' => '2024',
                        'title' => 'Consolidação institucional da AORERN',
                        'description' => 'Reorganização da presença institucional e fortalecimento da identidade visual e associativa da entidade.',
                    ],
                    [
                        'year' => '2025',
                        'title' => 'Estruturação do portal institucional',
                        'description' => 'Ampliação das páginas de referência, organização de conteúdos oficiais e consolidação da comunicação digital da associação.',
                    ],
                    [
                        'year' => '2026',
                        'title' => 'Ampliação da agenda institucional',
                        'description' => 'Novas frentes de integração associativa, memória institucional e relacionamento com o público externo.',
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
            'tagline' => 'Congregar, representar e preservar os valores dos oficiais da reserva.',
            'lead' => 'A missão da AORE/RN é congregar os Oficiais da Reserva do Exército no Rio Grande do Norte, fortalecer a camaradagem e o espírito de corpo, preservar tradições e símbolos militares e promover a valorização institucional, cívica e social dos oficiais R/2.',
            'hero_highlights' => [
                ['value' => 'R/2', 'label' => 'Oficiais da reserva'],
                ['value' => 'RN', 'label' => 'Atuação estadual'],
                ['value' => 'Cívico-militar', 'label' => 'Vínculo permanente'],
            ],
            'sections' => [
                [
                    'title' => 'Diretrizes que nos guiam',
                    'description' => 'A missão institucional da AORE/RN se apoia em princípios de unidade, representação e continuidade histórica.',
                    'icon' => 'bi-compass',
                    'items' => [
                        'Congregar oficiais da reserva formados pelos NPOR e CPOR em ambiente de camaradagem e respeito mútuo.',
                        'Preservar valores, tradições, símbolos e referências do Exército Brasileiro no âmbito associativo estadual.',
                        'Fortalecer a identidade institucional dos oficiais R/2 e seu vínculo permanente com a Pátria.',
                    ],
                ],
                [
                    'title' => 'Atuação associativa permanente',
                    'description' => 'A associação mantém agenda contínua de integração, memória e representação institucional.',
                    'icon' => 'bi-people',
                    'items' => [
                        'Promoção de encontros, solenidades, homenagens e eventos voltados à reserva.',
                        'Apoio à valorização institucional dos oficiais e à defesa de seus interesses legítimos.',
                        'Produção e organização de conteúdos oficiais que reforçam a memória associativa da entidade.',
                    ],
                ],
                [
                    'title' => 'Relação com a sociedade e instituições',
                    'description' => 'A AORE/RN também projeta sua presença no ambiente cívico e institucional potiguar.',
                    'icon' => 'bi-diagram-3',
                    'items' => [
                        'Integração com organizações militares, entidades parceiras e órgãos públicos quando pertinente.',
                        'Participação em atos cívicos, comemorações militares e agendas de representação institucional.',
                        'Estímulo à participação responsável dos oficiais da reserva na vida social, cívica e comunitária.',
                    ],
                ],
            ],
            'spotlight' => [
                'title' => 'Síntese da missão associativa',
                'description' => 'A AORE/RN existe para unir a reserva, preservar a tradição militar e manter viva a contribuição institucional dos oficiais R/2 no Rio Grande do Norte.',
                'chips' => ['Congregação', 'Memória institucional', 'Representação associativa'],
            ],
            'resources' => [
                ['label' => 'Conhecer a AORE/RN', 'type' => 'Página institucional', 'url' => BASE_URL . 'about', 'external' => false],
                ['label' => 'Falar com a associação', 'type' => 'Formulário', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
            'cta' => ['label' => 'Falar com a AORE/RN', 'url' => BASE_URL . 'contact'],
        ]);
    }

    public function valores(): void
    {
        $this->renderPage('valores', [
            'title' => 'Valores Institucionais',
            'badge' => 'Valores',
            'tagline' => 'Princípios que sustentam a vida associativa e a conduta dos oficiais da reserva.',
            'lead' => 'Os valores da AORERN orientam sua cultura institucional, sua forma de representação e o modo como a associação preserva a honra, a disciplina e o compromisso cívico dos oficiais da reserva.',
            'hero_highlights' => [
                ['value' => '06', 'label' => 'Valores centrais'],
                ['value' => 'Associativo', 'label' => 'Compromisso institucional'],
                ['value' => 'Permanente', 'label' => 'Vínculo com a reserva'],
            ],
            'sections' => [
                [
                    'title' => 'Honra e lealdade',
                    'description' => 'A atuação associativa se fundamenta no respeito à palavra empenhada, à tradição e ao espírito militar.',
                    'icon' => 'bi-award',
                    'items' => [
                        'Fidelidade aos valores do Exército Brasileiro e à dignidade da oficialidade.',
                        'Preservação da honra pessoal e institucional em todas as manifestações da associação.',
                        'Postura leal nas relações entre associados, parceiros e instituições.',
                    ],
                ],
                [
                    'title' => 'Disciplina e responsabilidade',
                    'description' => 'A associação valoriza organização, respeito às normas internas e responsabilidade na vida pública e associativa.',
                    'icon' => 'bi-balance-scale',
                    'items' => [
                        'Compromisso com ordem, sobriedade e coerência institucional.',
                        'Condução responsável de atos, comunicações e representações oficiais.',
                        'Respeito aos deveres morais e cívicos inerentes ao oficial da reserva.',
                    ],
                ],
            ],
            'value_cards' => [
                ['title' => 'Honra', 'description' => 'Agir com dignidade e retidão na vida associativa e pública.'],
                ['title' => 'Disciplina', 'description' => 'Respeitar normas, hierarquia moral e compromissos institucionais.'],
                ['title' => 'Lealdade', 'description' => 'Ser fiel à Pátria, à tradição militar e à própria associação.'],
                ['title' => 'Camaradagem', 'description' => 'Fortalecer vínculos de respeito, solidariedade e espírito de corpo.'],
                ['title' => 'Patriotismo', 'description' => 'Manter vivo o compromisso cívico com o Brasil e seus valores permanentes.'],
                ['title' => 'Responsabilidade social', 'description' => 'Estimular participação consciente e contribuição positiva à sociedade.'],
            ],
            'resources' => [
                ['label' => 'Conhecer a base institucional', 'type' => 'Página', 'url' => BASE_URL . 'about', 'external' => false],
                ['label' => 'Entrar em contato', 'type' => 'Canal institucional', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
        ]);
    }

    public function visao(): void
    {
        $this->renderPage('visao', [
            'title' => 'Visão de Futuro',
            'badge' => 'Planejamento 2030',
            'tagline' => 'Consolidar a AORERN como referência associativa da reserva no Rio Grande do Norte.',
            'lead' => 'A visão de futuro da AORERN aponta para o fortalecimento de sua presença institucional, a ampliação da integração entre oficiais da reserva e a consolidação de um legado associativo sólido, respeitado e permanente.',
            'hero_highlights' => [
                ['value' => '2030', 'label' => 'Horizonte estratégico'],
                ['value' => 'Mais integração', 'label' => 'Reserva e sociedade'],
                ['value' => 'Presença digital', 'label' => 'Comunicação institucional'],
            ],
            'sections' => [
                [
                    'title' => 'Fortalecimento institucional',
                    'description' => 'A associação busca ampliar sua capacidade de representação e sua presença pública qualificada.',
                    'icon' => 'bi-building',
                    'items' => [
                        'Consolidação de uma comunicação institucional estável, clara e reconhecível.',
                        'Ampliação da produção de conteúdos oficiais, históricos e simbólicos da associação.',
                        'Fortalecimento da imagem pública da AORERN como entidade representativa da reserva.',
                    ],
                ],
                [
                    'title' => 'Integração entre gerações da reserva',
                    'description' => 'A visão institucional inclui maior aproximação entre oficiais formados em diferentes épocas.',
                    'icon' => 'bi-people',
                    'items' => [
                        'Estímulo à participação de novos e antigos oficiais da reserva na vida associativa.',
                        'Criação de ambientes de memória, intercâmbio e colaboração entre gerações.',
                        'Valorização das trajetórias pessoais e profissionais construídas após a formação militar.',
                    ],
                ],
                [
                    'title' => 'Memória e continuidade',
                    'description' => 'O futuro da AORERN depende da preservação organizada de sua história e de seus marcos institucionais.',
                    'icon' => 'bi-journal-bookmark',
                    'items' => [
                        'Preservação documental de símbolos, homenagens, atos e registros relevantes da associação.',
                        'Disponibilização crescente de acervo institucional em ambiente digital.',
                        'Consolidação de uma cultura de continuidade entre passado, presente e futuro da reserva.',
                    ],
                ],
            ],
            'timeline' => [
                ['label' => '2025-2026', 'description' => 'Organização do portal institucional, padronização da identidade visual e fortalecimento dos canais oficiais.'],
                ['label' => '2027-2028', 'description' => 'Ampliação da agenda associativa e maior integração entre oficiais da reserva em âmbito estadual.'],
                ['label' => '2029-2030', 'description' => 'Consolidação de memória institucional estruturada e presença pública mais ampla da associação.'],
            ],
            'resources' => [
                ['label' => 'Conhecer a missão', 'type' => 'Página institucional', 'url' => BASE_URL . 'institucional/missao', 'external' => false],
                ['label' => 'Fale com a associação', 'type' => 'Contato', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
        ]);
    }

    public function brasao(): void
    {
        $this->renderPage('brasao', [
            'title' => 'AORERN - Selo Institucional',
            'badge' => 'Identidade Visual da AORERN',
            'tagline' => 'Descrição heráldica, esmaltes e interpretação simbólica oficial.',
            'lead' => 'Selo da Associação dos Oficiais da Reserva do Exército – Rio Grande do Norte, conforme descrição heráldica institucional.',
            'hero_highlights' => [
                ['value' => '+40 anos', 'label' => 'Identidade preservada'],
                ['value' => 'Aplicações oficiais', 'label' => 'Uniformes, viaturas e documentos'],
                ['value' => 'Uso controlado', 'label' => 'Por atos normativos internos'],
            ],
            'sections' => [
                [
                    'title' => 'Descrição Heráldica (Blasonamento)',
                    'description' => 'O Selo da Associação dos Oficiais da Reserva do Exército – Rio Grande do Norte é assim composto:',
                    'icon' => 'bi-shield-shaded',
                    'items' => [
                        'Escudo de formato português, com bordadura filetada de ouro.',
                        'No campo superior, de blau (azul celeste), um chefe partido verticalmente, sendo o flanco central de goles (vermelho), carregado de uma estrela de cinco pontas de prata; sobre o campo azul, a inscrição “NATAL” em letras de prata.',
                        'No campo inferior, cortado em faixas horizontais de sinopla (verde), prata e blau, figurando ao centro um resplendor ovalado de ouro, com campo azul celeste semeado de estrelas de prata, circundado por raios dourados, sustentado por ramos de louro também de ouro, atados na base.',
                        'Sobre o escudo, elmo de prata, posto de frente, com viseira cerrada, adornado de paquife de blau e goles, esvoaçante para ambos os flancos.',
                        'O conjunto encontra-se circundado por anel circular de blau, filetado de ouro, carregado na parte superior com a inscrição “ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO” em letras de ouro e, na parte inferior, “RIO GRANDE DO NORTE”, igualmente de ouro, separados lateralmente por duas estrelas de ouro.',
                    ],
                ],
                [
                    'title' => 'Esmaltes (Cores Heráldicas)',
                    'description' => 'Significados tradicionais dos esmaltes empregados no selo institucional.',
                    'icon' => 'bi-palette2',
                    'items' => [
                        'Blau → Azul (lealdade, justiça, perseverança).',
                        'Goles → Vermelho (bravura, valor militar).',
                        'Ouro → Nobreza, autoridade, honra.',
                        'Prata → Pureza, integridade.',
                        'Sinopla → Esperança e serviço.',
                    ],
                ],
                [
                    'title' => 'Interpretação Simbólica',
                    'description' => 'Leitura institucional dos principais elementos heráldicos do selo.',
                    'icon' => 'bi-building-check',
                    'items' => [
                        'Elmo de frente → Condição de oficialidade e honra militar.',
                        'Paquife azul e vermelho → Cores tradicionais associadas à representação do Exército Brasileiro.',
                        'Estrela de ouro → Oficialato e liderança.',
                        'Ramos de louro → Mérito e reconhecimento.',
                        'Resplendor central → Referência à tradição e identidade nacional.',
                        'Anel circular → Unidade e permanência institucional.',
                    ],
                ],
            ],
            'media' => [
                'type' => 'image',
                'src' => BASE_URL . 'assets/images/aore1.png',
                'alt' => 'Selo oficial da AORERN',
                'caption' => 'Utilizar apenas versões autorizadas pela Diretoria de Comunicação Social.',
                'download_url' => BASE_URL . 'assets/images/aore1.png',
                'download_name' => 'aore1.png',
                'download_label' => 'Download da marca (PNG)',
            ],
            'resources' => [
                ['label' => 'Manual de Identidade Visual', 'type' => 'PDF', 'url' => $this->publishedDocumentUrl('manual-identidade-visual-aorern-pdf', 'marca', BASE_URL . 'assets/images/brand/aorern-manual-identidade-visual.pdf'), 'external' => false],
                ['label' => 'Solicitar arquivo vetorial', 'type' => 'Contato', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
        ]);
    }

    public function hino(): void
    {
        $this->renderPage('hino', [
            'title' => 'Hino da AORE/RN',
            'badge' => 'Memória e Cultura',
            'tagline' => 'Associação dos Oficiais da Reserva do Exército do Rio Grande do Norte',
            'lead' => 'Composição institucional concebida para execução em contexto marcial, solene e progressivo, adequada a solenidades, formaturas, encontros associativos e atos cívicos da AORE/RN.',
            'hero_highlights' => [
                ['value' => '4/4', 'label' => 'Compasso'],
                ['value' => '112-116', 'label' => 'Andamento de marcha'],
                ['value' => 'Si bemol maior', 'label' => 'Tom sugerido'],
            ],
            'sections' => [
                [
                    'title' => 'Parâmetros musicais',
                    'description' => 'Referências recomendadas para arranjo, execução e adaptação do hino em banda, coral ou conjunto instrumental.',
                    'icon' => 'bi-music-note-list',
                    'items' => [
                        'Compasso: 4/4.',
                        'Andamento: semínima = 112-116, em marcha padrão.',
                        'Tom sugerido: Si bemol maior, especialmente adequado a bandas militares.',
                        'Caráter: marcial, solene e progressivo.',
                    ],
                ],
                [
                    'title' => 'Contexto de execução',
                    'description' => 'O hino pode ser empregado em cerimônias associativas e institucionais, preservando sempre a dignidade do ambiente cívico-militar.',
                    'icon' => 'bi-award',
                    'items' => [
                        'Solenidades da associação e eventos comemorativos da reserva.',
                        'Atos de integração com organizações militares e encontros institucionais.',
                        'Cerimônias de reconhecimento, homenagens e outorga de honrarias.',
                    ],
                ],
            ],
            'lyrics' => [
                [
                    'title' => 'Introdução (falado + musical crescente)',
                    'lines' => [
                        'Tropa, sentido!',
                        'Olhar à direita!!!!',
                    ],
                ],
                [
                    'title' => 'Verso 1',
                    'lines' => [
                        'AORE Natal, firmes na missão,',
                        'Servindo ao Brasil com fé e razão,',
                        'Civismo e cidadania em cada ação,',
                        'Moral e ética guiando a nação.',
                        '',
                        'Hierarquia e disciplina a conduzir,',
                        'Honestidade em todo o agir e servir,',
                        'Honra estampada em nosso olhar,',
                        'Compromisso eterno de sempre lutar.',
                    ],
                ],
                [
                    'title' => 'Pré-Refrão',
                    'lines' => [
                        'Somos reserva que não recua,',
                        'Força que nunca se apaga ou flutua,',
                        'Unidos no mesmo ideal,',
                        'Defendendo os valores do nosso Brasil.',
                    ],
                ],
                [
                    'title' => 'Refrão',
                    'lines' => [
                        'Reserva atenta e forte!',
                        'Nosso lema, nossa voz!',
                        'AORE Natal presente,',
                        'Sempre unidos, sempre nós!',
                        '',
                        'Reserva atenta e forte!',
                        'Com honra, fé e coração,',
                        'Marchamos firmes pelo Brasil,',
                        'Em defesa da nossa nação!',
                    ],
                ],
                [
                    'title' => 'Verso 2',
                    'lines' => [
                        'Levamos adiante o legado imortal,',
                        'De Caxias, Correia Lima e Apollo Rezk, ideal,',
                        'Exemplo de bravura, honra e dever,',
                        'Luz que nos guia no servir e vencer.',
                        '',
                        'Apoiamos os Oficiais R2 do Brasil,',
                        'Com lealdade, respeito e valor varonil,',
                        'Fortalecendo os laços do Exército,',
                        'Na sociedade, nossa missão e mérito.',
                    ],
                ],
                [
                    'title' => 'Pré-Refrão 2',
                    'lines' => [
                        'Somos porta-vozes da farda honrada,',
                        'Na vida civil, voz respeitada,',
                        'Levamos valores por onde for,',
                        'Com disciplina, verdade e amor.',
                    ],
                ],
                [
                    'title' => 'Refrão',
                    'lines' => [
                        '(Repetição com mais força)',
                        'Reserva atenta e forte!',
                        'Nosso lema, nossa voz!',
                        'AORE Natal presente,',
                        'Sempre unidos, sempre nós!',
                        '',
                        'Reserva atenta e forte!',
                        'Com honra, fé e coração,',
                        'Marchamos firmes pelo Brasil,',
                        'Em defesa da nossa nação!',
                    ],
                ],
                [
                    'title' => 'Ponte (parte mais emocional)',
                    'lines' => [
                        'De norte a sul ecoa a canção,',
                        'É AORE Natal em cada missão,',
                        'Unidos na ética, na honra e no bem,',
                        'Servindo ao povo, servindo além.',
                    ],
                ],
                [
                    'title' => 'Final (grandioso e solene)',
                    'lines' => [
                        'AORE Natal, orgulho de ser,',
                        'Nossa bandeira é servir e vencer,',
                        'Reserva que inspira, exemplo de ação,',
                        'Guardando os valores da nossa nação!',
                        '',
                        'Tropa, descansar!!!!',
                    ],
                ],
            ],
            'resources' => [
                ['label' => 'Baixar hino em PDF', 'type' => 'PDF', 'url' => BASE_URL . 'assets/docs/hino-aorern.pdf', 'external' => false],
                ['label' => 'Solicitar partitura oficial', 'type' => 'Contato institucional', 'url' => BASE_URL . 'contact', 'external' => false],
                ['label' => 'Solicitar arranjo para banda ou coral', 'type' => 'Atendimento', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
            'download_pdf_url' => $this->publishedDocumentUrl('hino-aorern-pdf', 'outro', BASE_URL . 'assets/docs/hino-aorern.pdf'),
            'download_pdf_name' => 'hino-aorern.pdf',
        ]);
    }

    public function links(): void
    {
        $this->renderPage('links', [
            'title' => 'Links Úteis',
            'badge' => 'Serviços e Portais',
            'tagline' => 'Acesso rápido a portais, referências militares e canais institucionais relacionados à AORERN.',
            'lead' => 'Reunimos aqui links de referência para consulta institucional, relacionamento associativo e acesso a ambientes oficiais de interesse dos oficiais da reserva.',
            'hero_highlights' => [
                ['value' => '06', 'label' => 'Referências selecionadas'],
                ['value' => 'Atualização', 'label' => 'Revisão trimestral'],
                ['value' => 'HTTPS', 'label' => 'Ambiente seguro'],
            ],
            'links' => [
                [
                    'label' => 'Portal Institucional AORERN',
                    'description' => 'Canal principal de comunicação institucional da associação.',
                    'url' => rtrim(BASE_URL, '/'),
                    'tag' => 'AORERN',
                ],
                [
                    'label' => 'Exército Brasileiro',
                    'description' => 'Portal oficial do Exército Brasileiro e referências institucionais nacionais.',
                    'url' => 'https://www.eb.mil.br',
                    'tag' => 'Parceiro',
                ],
                [
                    'label' => '16º Batalhão de Infantaria Motorizado',
                    'description' => 'Referência à organização militar vinculada ao ambiente institucional da AORERN.',
                    'url' => 'https://www.instagram.com/16bimtz/',
                    'tag' => 'Organização militar',
                ],
                [
                    'label' => 'Ministério da Defesa',
                    'description' => 'Informações institucionais e normativas relacionadas à Defesa Nacional.',
                    'url' => 'https://www.gov.br/defesa/pt-br',
                    'tag' => 'Governo Federal',
                ],
                [
                    'label' => 'Governo Federal',
                    'description' => 'Acesso a serviços públicos, legislações e informações oficiais.',
                    'url' => 'https://www.gov.br',
                    'tag' => 'Serviço público',
                ],
                [
                    'label' => 'Portal da Transparência',
                    'description' => 'Consulta pública a dados e informações de transparência governamental.',
                    'url' => 'https://portaldatransparencia.gov.br',
                    'tag' => 'Transparência',
                ],
            ],
            'resources' => [
                ['label' => 'Contato da AORERN', 'type' => 'Canal institucional', 'url' => BASE_URL . 'contact', 'external' => false],
                ['label' => 'Conhecer a associação', 'type' => 'Página institucional', 'url' => BASE_URL . 'about', 'external' => false],
            ],
        ]);
    }

    public function identidadeVisual(): void
    {
        $documentModel = new InstitutionalDocumentModel();
        $documentos = array_filter(
            $documentModel->paginar(1, 50, ['status' => 'published', 'type' => 'marca'])['data'] ?? [],
            static fn(array $documento): bool => !empty($documento['arquivo_url']) || !empty($documento['link_externo'])
        );

        $cards = array_map(fn(array $documento): array => $this->documentPreview($documento), $documentos);

        $this->renderPage('identidade-visual', [
            'title' => 'Identidade Visual',
            'badge' => 'Marca oficial',
            'tagline' => 'Selo, timbres e peças institucionais da AORERN.',
            'lead' => 'A identidade visual da AORERN foi organizada em um acervo oficial para uso consistente em materiais digitais, documentos, comunicações e aplicações institucionais.',
            'media' => [
                'type' => 'image',
                'src' => BASE_URL . 'assets/images/aore1.png',
                'alt' => 'Selo institucional da AORERN',
                'caption' => 'Arquivo principal da marca institucional em uso no portal.',
            ],
            'spotlight' => [
                'title' => 'Uso orientado da marca',
                'description' => 'As peças abaixo concentram as versões oficiais da identidade visual, incluindo selo, cabeçalho de e-mail e timbre A4.',
                'chips' => ['Selo oficial', 'Cabeçalhos', 'Timbres'],
            ],
            'sections' => [
                [
                    'title' => 'Peças oficiais disponíveis',
                    'description' => 'O acervo foi estruturado para atender necessidades de web, comunicação institucional e documentos formais.',
                    'icon' => 'bi-collection',
                    'items' => [
                        'Arquivos vetoriais e raster do selo institucional.',
                        'Cabeçalhos oficiais para e-mail institucional.',
                        'Timbres A4 preparados para documentos e expedientes formais.',
                    ],
                ],
            ],
            'document_cards' => $cards,
            'resources' => [
                ['label' => 'Abrir manual visual em PDF', 'type' => 'PDF oficial', 'url' => $this->publishedDocumentUrl('manual-identidade-visual-aorern-pdf', 'marca', BASE_URL . 'assets/images/brand/aorern-manual-identidade-visual.pdf'), 'external' => false],
                ['label' => 'Ver todos os documentos oficiais', 'type' => 'Acervo institucional', 'url' => BASE_URL . 'institucional/documentos', 'external' => false],
                ['label' => 'Solicitar aplicação específica da marca', 'type' => 'Contato institucional', 'url' => BASE_URL . 'contact', 'external' => false],
            ],
            'cta' => ['label' => 'Falar com a AORERN', 'url' => BASE_URL . 'contact'],
        ]);
    }

    public function documentos(): void
    {
        $request = Request::capture();
        $categoria = trim((string) $request->query('categoria', ''));
        $categoriaAtiva = array_key_exists($categoria, $this->availableDocumentFilters()) ? $categoria : null;
        $ordenacao = trim((string) $request->query('ordenacao', 'editorial'));
        $ordenacaoAtiva = array_key_exists($ordenacao, $this->availableDocumentSorts()) ? $ordenacao : 'editorial';
        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $documentModel = new InstitutionalDocumentModel();
        $result = $documentModel->paginar($page, 9, [
            'status' => 'published',
            'type' => $categoriaAtiva,
            'sort' => $ordenacaoAtiva,
            'q' => $q !== '' ? $q : null,
        ]);
        $documentos = $result['data'] ?? [];
        $cards = array_map(fn(array $documento): array => $this->documentPreview($documento), $documentos);
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'institucional/documentos',
            'query' => $this->documentQueryParams($categoriaAtiva, $ordenacaoAtiva, $q),
        ]);

        $this->renderPage('documentos', [
            'title' => 'Documentos Oficiais',
            'badge' => 'Acervo oficial',
            'tagline' => 'Referências institucionais públicas da AORERN.',
            'lead' => 'Esta seção reúne documentos e materiais institucionais publicados para consulta pública, incluindo referências normativas, peças institucionais e documentos permanentes da associação.',
            'hero_highlights' => [
                ['value' => count($documentos), 'label' => 'Documentos publicados'],
                ['value' => $categoriaAtiva ? $this->documentTypeLabel($categoriaAtiva) : 'Acervo completo', 'label' => 'Categoria em foco'],
                ['value' => $this->availableDocumentSorts()[$ordenacaoAtiva], 'label' => 'Ordenação atual'],
            ],
            'sections' => [
                [
                    'title' => 'Acervo institucional disponível',
                    'description' => 'Os materiais abaixo foram organizados para facilitar consulta, conferência e download quando aplicável.',
                    'icon' => 'bi-folder2-open',
                    'items' => [
                        'Documentos normativos e referências oficiais da associação.',
                        'Materiais institucionais permanentes, como políticas, termos e peças de marca.',
                        'Links para arquivos e referências externas validadas pela administração do portal.',
                    ],
                ],
            ],
            'document_filters' => $this->documentFilterChips($categoriaAtiva),
            'document_sorts' => $this->documentSortChips($ordenacaoAtiva, $categoriaAtiva),
            'document_search' => [
                'value' => $q,
                'action' => BASE_URL . 'institucional/documentos',
                'params' => array_filter([
                    'categoria' => $categoriaAtiva,
                    'ordenacao' => $ordenacaoAtiva !== 'editorial' ? $ordenacaoAtiva : null,
                ]),
            ],
            'document_cards' => $cards,
            'pagination' => $pagination,
            'resources' => array_map(function (array $documento): array {
                $card = $this->documentPreview($documento);
                return [
                    'label' => $card['title'],
                    'type' => $card['category'],
                    'url' => $card['url'],
                    'external' => true,
                ];
            }, $documentos),
            'cta' => ['label' => 'Falar com a AORERN', 'url' => BASE_URL . 'contact'],
        ]);
    }

    public function downloadsMarca(): void
    {
        $request = Request::capture();
        $ordenacao = trim((string) $request->query('ordenacao', 'editorial'));
        $ordenacaoAtiva = array_key_exists($ordenacao, $this->availableDocumentSorts()) ? $ordenacao : 'editorial';
        $page = max(1, (int) $request->query('page', 1));

        $documentModel = new InstitutionalDocumentModel();
        $result = $documentModel->paginar($page, 9, [
            'status' => 'published',
            'type' => 'marca',
            'sort' => $ordenacaoAtiva,
        ]);

        $cards = array_map(fn(array $documento): array => $this->documentPreview($documento), $result['data'] ?? []);
        $pagination = array_merge($result['meta'], [
            'path' => BASE_URL . 'institucional/downloads-marca',
            'query' => $this->documentQueryParams('marca', $ordenacaoAtiva),
        ]);

        $this->renderPage('downloads-marca', [
            'title' => 'Downloads de Marca',
            'badge' => 'Acervo visual',
            'tagline' => 'Peças oficiais de identidade visual da AORERN.',
            'lead' => 'Esta área reúne os downloads visuais oficiais da AORERN, incluindo selo institucional, manual visual, timbres e cabeçalhos aprovados para uso institucional.',
            'hero_highlights' => [
                ['value' => count($cards), 'label' => 'Peças visuais'],
                ['value' => 'Marca', 'label' => 'Categoria fixa'],
                ['value' => $this->availableDocumentSorts()[$ordenacaoAtiva], 'label' => 'Ordenação atual'],
            ],
            'sections' => [
                [
                    'title' => 'Pacote oficial de marca',
                    'description' => 'Todos os arquivos abaixo fazem parte do conjunto oficial de identidade visual da associação.',
                    'icon' => 'bi-palette2',
                    'items' => [
                        'Selo institucional em SVG e PNG para aplicações digitais e impressas.',
                        'Manual visual institucional em SVG e PDF para consulta e distribuição.',
                        'Cabeçalhos de e-mail e timbres oficiais em formatos preparados para uso.',
                    ],
                ],
            ],
            'document_sorts' => $this->documentSortChips($ordenacaoAtiva, null, 'institucional/downloads-marca'),
            'document_cards' => $cards,
            'pagination' => $pagination,
            'resources' => [
                ['label' => 'Ver identidade visual', 'type' => 'Página institucional', 'url' => BASE_URL . 'institucional/identidade-visual', 'external' => false],
                ['label' => 'Ver documentos oficiais', 'type' => 'Acervo completo', 'url' => BASE_URL . 'institucional/documentos?categoria=marca', 'external' => false],
            ],
            'cta' => ['label' => 'Falar com a AORERN', 'url' => BASE_URL . 'contact'],
        ]);
    }

    public function governanca(): void
    {
        $termModel = new BoardTermModel();
        $membershipModel = new BoardMembershipModel();
        $documentModel = new InstitutionalDocumentModel();
        $applicationModel = new MembershipApplicationModel();

        $mandatos = $termModel->listar();
        $composicoes = $membershipModel->paginar(1, 100, ['is_active' => 1])['data'] ?? [];
        $documentosNormativos = array_filter(
            $documentModel->paginar(1, 100, ['status' => 'published'])['data'] ?? [],
            static fn(array $documento): bool => in_array($documento['tipo'], ['estatuto', 'ata', 'politica', 'termo', 'oficio'], true)
        );

        $cards = array_map(fn(array $documento): array => $this->documentPreview($documento), array_slice($documentosNormativos, 0, 6));
        $mandatoAtivo = null;
        foreach ($mandatos as $mandato) {
            if (($mandato['status'] ?? '') === 'active') {
                $mandatoAtivo = $mandato;
                break;
            }
        }

        $grupos = [];
        $boardGroups = [];
        foreach ($composicoes as $item) {
            $grupo = $item['grupo'] ?: 'Diretoria';
            $grupos[$grupo] = ($grupos[$grupo] ?? 0) + 1;
            $avatarPath = (string) ($item['associado_foto'] ?: ($item['associado_user_avatar'] ?: ''));
            $roleInstitucional = $item['funcao_nome'] ?: ($item['cargo'] ?: 'Função institucional');
            $boardGroups[$grupo][] = [
                'name' => $item['associado_nome'] ?: 'Associado não vinculado',
                'role' => $roleInstitucional,
                'avatar_url' => $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png'),
            ];
        }

        $presidentSpotlight = null;
        foreach ($composicoes as $item) {
            $roleInstitucional = (string) ($item['funcao_nome'] ?: ($item['cargo'] ?: ''));
            $cargo = strtolower($roleInstitucional);
            $funcao = strtolower((string) ($item['funcao_nome'] ?? ''));
            $isPresidente = (str_contains($cargo, 'presidente') && !str_contains($cargo, 'vice'))
                || ($cargo === '' && str_contains($funcao, 'presidente') && !str_contains($funcao, 'vice'));

            if (!$isPresidente) {
                continue;
            }

            $application = !empty($item['pessoal_id'])
                ? $applicationModel->buscarMaisRecentePorPessoalId((int) $item['pessoal_id'])
                : null;

            $avatarPath = (string) ($item['associado_foto'] ?: ($item['associado_user_avatar'] ?: ''));
            $presidentSpotlight = [
                'name' => $item['associado_nome'] ?: 'Associado não vinculado',
                'role' => $roleInstitucional !== '' ? $roleInstitucional : 'Função institucional',
                'avatar_url' => $this->resolveAssetUrl($avatarPath) ?: (BASE_URL . 'assets/images/conscrito.png'),
                'ano_npor' => $application['ano_npor'] ?? null,
                'turma_npor' => $application['turma_npor'] ?? null,
            ];
            break;
        }

        $groupHighlights = [];
        foreach (array_slice($grupos, 0, 4, true) as $grupo => $total) {
            $groupHighlights[] = $grupo . ': ' . $total . ' integrante(s)';
        }

        $this->renderPage('governanca', [
            'title' => 'Governança',
            'badge' => 'Estrutura institucional',
            'tagline' => 'Mandatos, diretoria e referências normativas da AORE/RN.',
            'lead' => 'Esta área reúne elementos centrais de governança da associação, incluindo composições diretivas, ciclos de mandato e documentos normativos publicados no portal.',
            'hero_highlights' => [
                ['value' => count($mandatos), 'label' => 'Mandatos cadastrados'],
                ['value' => count($composicoes), 'label' => 'Composições ativas'],
                ['value' => count($documentosNormativos), 'label' => 'Documentos normativos'],
            ],
            'spotlight' => [
                'title' => $mandatoAtivo ? 'Mandato em destaque: ' . $mandatoAtivo['nome'] : 'Governança em estruturação',
                'description' => $mandatoAtivo
                    ? 'O mandato ativo concentra a composição diretiva atualmente publicada no painel administrativo da associação.'
                    : 'A estrutura pública de governança está pronta para receber mandatos e composições assim que forem consolidados no admin.',
                'chips' => $groupHighlights ?: ['Diretoria', 'Mandatos', 'Normativos'],
                'president' => $presidentSpotlight,
            ],
            'sections' => [
                [
                    'title' => 'Composição institucional',
                    'description' => 'A governança pública pode refletir a organização diretiva cadastrada no módulo administrativo.',
                    'icon' => 'bi-people',
                    'items' => [
                        'Mandatos e composições podem ser cadastrados e atualizados no painel institucional.',
                        'Cargos e grupos diretivos ficam preparados para exposição pública controlada.',
                        'A área pública pode evoluir para exibir mandatos históricos, diretoria vigente e conselhos.',
                    ],
                ],
                [
                    'title' => 'Base normativa',
                    'description' => 'Os documentos institucionais de governança ficam concentrados no acervo oficial.',
                    'icon' => 'bi-folder2-open',
                    'items' => [
                        'Estatutos, atas, políticas, termos e ofícios podem ser publicados com curadoria editorial.',
                        'O acervo público já conta com filtros, ordenação e paginação para consulta qualificada.',
                        'Novos normativos podem ser integrados sem alterar a estrutura do portal.',
                    ],
                ],
            ],
            'board_groups' => $boardGroups,
            'document_cards' => $cards,
            'resources' => [
                ['label' => 'Ver documentos normativos', 'type' => 'Acervo público', 'url' => BASE_URL . 'institucional/documentos?categoria=estatuto', 'external' => false],
                ['label' => 'Ver diretoria no admin', 'type' => 'Gestão interna', 'url' => BASE_URL . 'admin/diretoria', 'external' => false],
                ['label' => 'Ver mandatos no admin', 'type' => 'Gestão interna', 'url' => BASE_URL . 'admin/mandatos', 'external' => false],
            ],
            'cta' => ['label' => 'Falar com a AORE/RN', 'url' => BASE_URL . 'contact'],
        ]);
    }

    public function busca(): void
    {
        $request = Request::capture();
        $q = trim((string) $request->query('q', ''));

        $documentResults = [];
        $blogResults = [];
        $governanceResults = [];

        if ($q !== '') {
            $documentModel = new InstitutionalDocumentModel();
            $postModel = new Post();
            $termModel = new BoardTermModel();
            $membershipModel = new BoardMembershipModel();

            $documentos = $documentModel->paginar(1, 6, [
                'status' => 'published',
                'q' => $q,
            ])['data'] ?? [];
            $documentResults = array_map(function (array $documento): array {
                $card = $this->documentPreview($documento);
                return [
                    'title' => $card['title'],
                    'type' => 'Documento · ' . $card['category'],
                    'description' => $card['summary'],
                    'url' => $card['url'],
                    'icon' => $card['preview_icon'],
                ];
            }, $documentos);

            $posts = $postModel->listarPublico($q, null, 1, 6)['data'] ?? [];
            $blogResults = array_map(static function (array $post): array {
                return [
                    'title' => $post['titulo'],
                    'type' => 'Blog',
                    'description' => trim(strip_tags((string) $post['conteudo'])) ?: 'Publicação institucional do portal.',
                    'url' => BASE_URL . 'blog/' . $post['slug'],
                    'icon' => 'bi-journal-text',
                ];
            }, $posts);

            $mandatos = $termModel->paginar(1, 10, ['q' => $q])['data'] ?? [];
            $membros = $membershipModel->paginar(1, 12, ['q' => $q, 'is_active' => 1])['data'] ?? [];

            foreach ($mandatos as $mandato) {
                $governanceResults[] = [
                    'title' => 'Mandato ' . $mandato['nome'],
                    'type' => 'Governança · Mandato',
                    'description' => 'Status: ' . ucfirst((string) $mandato['status']) . '. Início: ' . ($mandato['data_inicio'] ?: 'não informado') . '.',
                    'url' => BASE_URL . 'institucional/governanca',
                    'icon' => 'bi-clock-history',
                ];
            }

            foreach ($membros as $membro) {
                $governanceResults[] = [
                    'title' => ($membro['associado_nome'] ?: 'Associado') . ' · ' . ($membro['cargo'] ?: 'Função institucional'),
                    'type' => 'Governança · Diretoria',
                    'description' => ($membro['grupo'] ?: 'Diretoria') . ' · Mandato ' . ($membro['term_nome'] ?: 'não informado'),
                    'url' => BASE_URL . 'institucional/governanca',
                    'icon' => 'bi-people',
                ];
            }
        }

        $this->renderPage('busca', [
            'title' => 'Busca Institucional',
            'badge' => 'Pesquisa pública',
            'tagline' => 'Consulta unificada de documentos, governança e publicações.',
            'lead' => 'Pesquise conteúdos institucionais publicados no portal, incluindo documentos oficiais, estruturas de governança e publicações do blog.',
            'hero_highlights' => [
                ['value' => count($documentResults), 'label' => 'Documentos'],
                ['value' => count($governanceResults), 'label' => 'Governança'],
                ['value' => count($blogResults), 'label' => 'Blog'],
            ],
            'sections' => [
                [
                    'title' => 'Pesquisa unificada',
                    'description' => 'A busca consulta simultaneamente o acervo documental, referências de governança e publicações do portal.',
                    'icon' => 'bi-search',
                    'items' => [
                        'Use termos como estatuto, ata, diretoria, mandato, selo ou nome de associado.',
                        'Os resultados abaixo são agrupados por origem para facilitar a navegação.',
                        'Quando não houver ocorrência no repositório, o portal preserva a estrutura pronta para futura publicação.',
                    ],
                ],
            ],
            'document_search' => [
                'value' => $q,
                'action' => BASE_URL . 'institucional/busca',
                'params' => [],
            ],
            'search_results' => [
                'Documentos' => $documentResults,
                'Governança' => $governanceResults,
                'Blog' => $blogResults,
            ],
            'resources' => [
                ['label' => 'Ver documentos oficiais', 'type' => 'Acervo público', 'url' => BASE_URL . 'institucional/documentos', 'external' => false],
                ['label' => 'Ver governança', 'type' => 'Página institucional', 'url' => BASE_URL . 'institucional/governanca', 'external' => false],
                ['label' => 'Ver blog', 'type' => 'Publicações', 'url' => BASE_URL . 'blog', 'external' => false],
            ],
            'cta' => ['label' => 'Falar com a AORE/RN', 'url' => BASE_URL . 'contact'],
        ]);
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return BASE_URL . ltrim($value, '/');
    }
}
