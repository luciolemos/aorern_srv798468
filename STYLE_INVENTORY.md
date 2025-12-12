# Inventário Visual Público — Dez/2025

## Layout base
- `layouts/base.twig` injeta `bombeiros-theme.css`, `navbar-universal.css`, `footer-robust.css` e `main.css`, além das folhas específicas carregadas via `{% block styles %}` em cada página.
- Componentes globais disponíveis: `components/navbar.twig` e `components/footer.twig`.
- Hero, cards e breadcrumbs variam entre páginas, cada uma com seu próprio CSS.

## Páginas atuais
| Página / Rota | Controller | View principal | CSS específico | Observações |
| --- | --- | --- | --- | --- |
| Home (`/`) | `HomeController` | `site/pages/home.twig` | `home.css` | Hero personalizado, cards e métricas exclusivas.
| Blog list (`/blog`) | `BlogController@index` | `site/pages/blog.twig` | `blog.css` | Destaque + grid; mesma folha usada no post individual.
| Post individual (`/blog/{slug}`) | `BlogController@post` | `site/pages/post.twig` | `blog.css` | Reaproveita estilos do blog; adiciona share-card.
| Sobre (`/about`) | `AboutController` | `site/pages/about.twig` | `about.css` | Visual próximo do home, porém com outra folha.
| Contato (`/contact`) | `ContactController` | `site/pages/contact.twig` | `contact.css` | Hero escuro + formulário, sem reaproveitar componentes globais.
| Esquadrão (`/esquadrao`) | `EsquadraoController` | `site/content/esquadrao.twig` | `esquadrao.css` | Outra linguagem visual (section-kicker genérico).
| Institucional (`/institucional` e subrotas) | `InstitucionalController` | `site/institutional/index.twig` + `site/institutional/page.twig` | `institutional.css` | Único módulo com controller/view/estilo centralizados.
| Coverage docs | `CoverageController` | `site/pages/??` (verificar futuramente) | `coverage.css` (não existe) | conteúdo ainda heterogêneo.
| Login/Register | `LoginController`, `RegisterController` | `auth/*` | `style.css` | Fora do escopo visual público.

## CSS globais existentes
| Arquivo | Função atual |
| --- | --- |
| `bombeiros-theme.css` | Reseta cores básicas e define fontes para o tema público.
| `navbar-universal.css` | Estilos do navbar público.
| `footer-robust.css` | Estilos do footer atual.
| `main.css` | Utilitários diversos, mas ainda sem padronização de cards/heros.
| `about.css`, `home.css`, `blog.css`, `contact.css`, `esquadrao.css` | Cada um replica tokens (cores #df6301, gradientes, sombras) e define variações do mesmo padrão.
| `institutional.css` | Primeiro experimento com tokens dedicados e componentes consistentes.

## Gaps identificados
1. **Tokens duplicados**: códigos como `#df6301`, `#c55501`, `#006989` aparecem em todas as folhas de estilo.
2. **Componentes equivalentes com classes diferentes**: `home-section-kicker`, `section-kicker`, `inst-hero-tag` resolvem o mesmo problema.
3. **Breadcrumbs/headers**: cada página desenha o próprio cabeçalho; apenas Institucional tem componente reutilizável.
4. **Ordem de importação**: variáveis não existem; algumas páginas redefinem `:root` local.

## Próximos passos (fase atual)
1. Criar `public/assets/css/system/variables.css` com a paleta oficial e fontes para todo o site.
2. Criar `public/assets/css/system/components.css` contendo utilitários padronizados (hero tag, section kicker, cards).
3. Atualizar `layouts/base.twig` para carregar esse novo núcleo antes das folhas específicas.
4. Migrar gradualmente cada página para consumir os tokens/componentes unificados, começando por Home, Blog e Contato.
