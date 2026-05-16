<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\BlogController;
use App\Controllers\Site\AboutController;
use App\Controllers\Site\ContactController;
use App\Controllers\Site\AssociadoController;
use App\Controllers\Site\GaleriaController as SiteGaleriaController;
use App\Controllers\Site\InstitucionalController;
use App\Controllers\Site\DocsController as SiteDocsController;
use App\Controllers\Site\CoverageController;
use App\Controllers\Site\OcorrenciasController;
use App\Controllers\Site\EsquadraoController;
use App\Controllers\Site\TermosController;
use App\Controllers\Site\PrivacidadeController;
use App\Controllers\Site\ReadmeController;
use App\Controllers\Site\LoginController as SiteLoginController;
use App\Controllers\Site\RegisterController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\LoginController as AdminLoginController;
use App\Controllers\Admin\PostsController;
use App\Controllers\Admin\PostCategoriesController;
use App\Controllers\Admin\UsuariosController;
use App\Controllers\Admin\SolicitacoesFiliacaoController;
use App\Controllers\Admin\PessoalController;
use App\Controllers\Admin\DiretoriaController;
use App\Controllers\Admin\MandatosController;
use App\Controllers\Admin\DocumentosController;
use App\Controllers\Admin\GaleriaController;
use App\Controllers\Admin\GaleriaCategoriasController;
use App\Controllers\Admin\PatrocinadoresController;
use App\Controllers\Admin\StatusController;
use App\Controllers\Admin\SystemController;
use App\Controllers\Admin\PerfilController;
use App\Controllers\Admin\AlterarSenhaController;
use App\Controllers\Admin\ConfiguracaoController;
use App\Controllers\Admin\LivroTiposController;
use App\Controllers\Admin\LivroOcorrenciasController;
use App\Controllers\Admin\CategoriasController;
use App\Controllers\Admin\FuncoesController;
use App\Controllers\Admin\EquipamentosController;
use App\Controllers\Admin\ObrasController;
use App\Controllers\Admin\DogsController;
use App\Controllers\Admin\DogBreedsController;
use App\Controllers\Admin\InstitucionalController as AdminInstitucionalController;
use App\Controllers\Admin\MemoriaController;
use App\Controllers\Admin\EventosController;
use App\Controllers\Admin\DocsController as AdminDocsController;

/**
 * Rotas declarativas iniciais (migração gradual).
 *
 * Estratégia:
 * - Cobrir fluxos críticos primeiro.
 * - Manter App.php como fallback para rotas ainda não migradas.
 */

Router::middleware('auth', function (): bool {
    AuthMiddleware::requireAuth();
    return true;
});

// Site público
Router::get('/', fn() => (new HomeController())->index());
Router::get('/blog', fn() => (new BlogController())->index());
Router::get('/blog/{slug}', fn($request, $slug) => (new BlogController())->post($slug));
Router::get('/about', fn() => (new AboutController())->index());
Router::get('/contact', fn() => (new ContactController())->index());
Router::post('/contact/send', fn() => (new ContactController())->send());
Router::get('/galeria', fn() => (new SiteGaleriaController())->index());

// Portal institucional (site)
Router::get('/institucional', fn() => (new InstitucionalController())->index());
Router::get('/institucional/missao', fn() => (new InstitucionalController())->missao());
Router::get('/institucional/valores', fn() => (new InstitucionalController())->valores());
Router::get('/institucional/visao', fn() => (new InstitucionalController())->visao());
Router::get('/institucional/brasao', fn() => (new InstitucionalController())->brasao());
Router::get('/institucional/hino', fn() => (new InstitucionalController())->hino());
Router::get('/institucional/links', fn() => (new InstitucionalController())->links());
Router::get('/institucional/identidade-visual', fn() => (new InstitucionalController())->identidadeVisual());
Router::get('/institucional/documentos', fn() => (new InstitucionalController())->documentos());
Router::get('/institucional/downloads-marca', fn() => (new InstitucionalController())->downloadsMarca());
Router::get('/institucional/governanca', fn() => (new InstitucionalController())->governanca());
Router::get('/institucional/busca', fn() => (new InstitucionalController())->busca());
Router::get('/institucional/npor/patrono', fn() => (new InstitucionalController())->patrono());
Router::get('/docs', fn() => (new SiteDocsController())->index());
Router::get('/docs/doc/{slug}', fn($request, $slug) => (new SiteDocsController())->doc($slug));
Router::get('/coverage', fn() => (new CoverageController())->index());
Router::get('/coverage/relatorio', fn() => (new CoverageController())->relatorio());
Router::get('/ocorrencias/mapa-municipios', fn() => (new OcorrenciasController())->mapaMunicipios());
Router::get('/esquadrao', fn() => (new EsquadraoController())->index());
Router::get('/termos', fn() => (new TermosController())->index());
Router::get('/privacidade', fn() => (new PrivacidadeController())->index());
Router::get('/readme', fn() => (new ReadmeController())->index());

// Login público
Router::get('/login/admin', fn() => (new SiteLoginController())->admin());
Router::post('/login/authenticate-admin', fn() => (new SiteLoginController())->authenticateAdmin());
Router::get('/login/logout', fn() => (new SiteLoginController())->logout());

// Filiação
Router::get('/register', fn() => (new RegisterController())->index());
Router::post('/register/store', fn() => (new RegisterController())->store());
Router::get('/register/cidades-por-uf', fn() => (new RegisterController())->cidadesPorUf());

// Area do associado
Router::get('/associado', fn() => (new AssociadoController())->index());
Router::post('/associado/update', fn() => (new AssociadoController())->update());
Router::post('/associado/change-password', fn() => (new AssociadoController())->changePassword());
Router::post('/associado/complementar', fn() => (new AssociadoController())->complementar());

// Admin crítico
Router::group('/admin', function (): void {
    Router::get('', fn() => (new DashboardController())->index(), ['auth']);
    Router::get('/dashboard', fn() => (new DashboardController())->index(), ['auth']);

    // Bloco de autenticação admin (migração declarativa + compatibilidade)
    Router::get('/auth', fn() => (new AuthController())->index());
    Router::get('/auth/login', fn() => (new AuthController())->index());
    Router::post('/auth/login', fn() => (new AuthController())->login());
    Router::get('/auth/register', fn() => (new AuthController())->register());
    Router::post('/auth/store', fn() => (new AuthController())->store());
    Router::get('/auth/logout', fn() => (new AuthController())->logout());

    // Alias legado /admin/login*
    Router::get('/login', fn() => (new AdminLoginController())->index());
    Router::post('/login/login', fn() => (new AdminLoginController())->login());
    Router::get('/login/register', fn() => (new AdminLoginController())->register());
    Router::post('/login/store', fn() => (new AdminLoginController())->store());
    Router::get('/login/logout', fn() => (new AdminLoginController())->logout());

    // Publicacoes (posts)
    Router::get('/publicacoes', fn() => (new PostsController())->index(), ['auth']);
    Router::get('/publicacoes/cadastrar', fn() => (new PostsController())->create(), ['auth']);
    Router::post('/publicacoes/salvar', fn() => (new PostsController())->store(), ['auth']);
    Router::get('/publicacoes/editar/{id}', fn($request, $id) => (new PostsController())->edit((int) $id), ['auth']);
    Router::post('/publicacoes/atualizar/{id}', fn($request, $id) => (new PostsController())->update((int) $id), ['auth']);
    Router::get('/publicacoes/submeter/{id}', fn($request, $id) => (new PostsController())->submit((int) $id), ['auth']);
    Router::get('/publicacoes/aprovar/{id}', fn($request, $id) => (new PostsController())->approve((int) $id), ['auth']);
    Router::get('/publicacoes/ocultar/{id}', fn($request, $id) => (new PostsController())->hide((int) $id), ['auth']);
    Router::get('/publicacoes/exibir/{id}', fn($request, $id) => (new PostsController())->show((int) $id), ['auth']);
    Router::get('/publicacoes/despublicar/{id}', fn($request, $id) => (new PostsController())->unpublish((int) $id), ['auth']);
    Router::post('/publicacoes/rejeitar/{id}', fn($request, $id) => (new PostsController())->reject((int) $id), ['auth']);
    Router::get('/publicacoes/deletar/{id}', fn($request, $id) => (new PostsController())->delete((int) $id), ['auth']);

    // Alias legado
    Router::get('/posts', fn() => (new PostsController())->index(), ['auth']);
    Router::get('/posts/create', fn() => (new PostsController())->create(), ['auth']);
    Router::post('/posts/store', fn() => (new PostsController())->store(), ['auth']);
    Router::get('/posts/edit/{id}', fn($request, $id) => (new PostsController())->edit((int) $id), ['auth']);
    Router::post('/posts/update/{id}', fn($request, $id) => (new PostsController())->update((int) $id), ['auth']);
    Router::get('/posts/submit/{id}', fn($request, $id) => (new PostsController())->submit((int) $id), ['auth']);
    Router::get('/posts/approve/{id}', fn($request, $id) => (new PostsController())->approve((int) $id), ['auth']);
    Router::get('/posts/hide/{id}', fn($request, $id) => (new PostsController())->hide((int) $id), ['auth']);
    Router::get('/posts/show/{id}', fn($request, $id) => (new PostsController())->show((int) $id), ['auth']);
    Router::get('/posts/unpublish/{id}', fn($request, $id) => (new PostsController())->unpublish((int) $id), ['auth']);
    Router::post('/posts/reject/{id}', fn($request, $id) => (new PostsController())->reject((int) $id), ['auth']);
    Router::get('/posts/delete/{id}', fn($request, $id) => (new PostsController())->delete((int) $id), ['auth']);

    // Categorias editoriais
    Router::get('/categorias-editoriais', fn() => (new PostCategoriesController())->index(), ['auth']);
    Router::get('/categorias-editoriais/cadastrar', fn() => (new PostCategoriesController())->create(), ['auth']);
    Router::post('/categorias-editoriais/salvar', fn() => (new PostCategoriesController())->store(), ['auth']);
    Router::get('/categorias-editoriais/editar/{id}', fn($request, $id) => (new PostCategoriesController())->edit((int) $id), ['auth']);
    Router::post('/categorias-editoriais/atualizar/{id}', fn($request, $id) => (new PostCategoriesController())->update((int) $id), ['auth']);
    Router::get('/categorias-editoriais/deletar/{id}', fn($request, $id) => (new PostCategoriesController())->destroy((int) $id), ['auth']);

    // Alias legado
    Router::get('/post-categories', fn() => (new PostCategoriesController())->index(), ['auth']);
    Router::get('/post-categories/create', fn() => (new PostCategoriesController())->create(), ['auth']);
    Router::post('/post-categories/store', fn() => (new PostCategoriesController())->store(), ['auth']);
    Router::get('/post-categories/edit/{id}', fn($request, $id) => (new PostCategoriesController())->edit((int) $id), ['auth']);
    Router::post('/post-categories/update/{id}', fn($request, $id) => (new PostCategoriesController())->update((int) $id), ['auth']);
    Router::get('/post-categories/delete/{id}', fn($request, $id) => (new PostCategoriesController())->destroy((int) $id), ['auth']);

    // Usuarios do painel
    Router::get('/usuarios', fn() => (new UsuariosController())->index(), ['auth']);
    Router::get('/usuarios/visualizar/{id}', fn($request, $id) => (new UsuariosController())->visualizar((int) $id), ['auth']);
    Router::get('/usuarios/editar/{id}', fn($request, $id) => (new UsuariosController())->editar((int) $id), ['auth']);
    Router::post('/usuarios/atualizar', fn() => (new UsuariosController())->atualizar(), ['auth']);
    Router::post('/usuarios/ativar', fn() => (new UsuariosController())->ativar(), ['auth']);
    Router::post('/usuarios/desativar', fn() => (new UsuariosController())->desativar(), ['auth']);
    Router::post('/usuarios/alterar-role', fn() => (new UsuariosController())->alterarRole(), ['auth']);
    Router::post('/usuarios/deletar', fn() => (new UsuariosController())->deletar(), ['auth']);

    // Solicitacoes de filiacao
    Router::get('/solicitacoes-filiacao', fn() => (new SolicitacoesFiliacaoController())->index(), ['auth']);
    Router::post('/solicitacoes-filiacao/aprovar/{id}', fn($request, $id) => (new SolicitacoesFiliacaoController())->aprovar((int) $id), ['auth']);
    Router::post('/solicitacoes-filiacao/rejeitar/{id}', fn($request, $id) => (new SolicitacoesFiliacaoController())->rejeitar((int) $id), ['auth']);
    Router::post('/solicitacoes-filiacao/solicitar-complementacao/{id}', fn($request, $id) => (new SolicitacoesFiliacaoController())->solicitarComplementacao((int) $id), ['auth']);

    // Associados (pessoal)
    Router::get('/pessoal', fn() => (new PessoalController())->index(), ['auth']);
    Router::get('/pessoal/cadastrar', fn() => (new PessoalController())->cadastrar(), ['auth']);
    Router::post('/pessoal/salvar', fn() => (new PessoalController())->salvar(), ['auth']);
    Router::get('/pessoal/visualizar/{id}', fn($request, $id) => (new PessoalController())->visualizar((int) $id), ['auth']);
    Router::get('/pessoal/editar/{id}', fn($request, $id) => (new PessoalController())->editar((int) $id), ['auth']);
    Router::post('/pessoal/atualizar/{id}', fn($request, $id) => (new PessoalController())->atualizar((int) $id), ['auth']);
    Router::post('/pessoal/deletar/{id}', fn($request, $id) => (new PessoalController())->deletar((int) $id), ['auth']);
    Router::post('/pessoal/acesso/{id}', fn($request, $id) => (new PessoalController())->acesso((int) $id), ['auth']);

    // Bloco institucional
    Router::get('/diretoria', fn() => (new DiretoriaController())->index(), ['auth']);
    Router::get('/diretoria/cadastrar', fn() => (new DiretoriaController())->cadastrar(), ['auth']);
    Router::post('/diretoria/salvar', fn() => (new DiretoriaController())->salvar(), ['auth']);
    Router::get('/diretoria/visualizar/{id}', fn($request, $id) => (new DiretoriaController())->visualizar((int) $id), ['auth']);
    Router::get('/diretoria/editar/{id}', fn($request, $id) => (new DiretoriaController())->editar((int) $id), ['auth']);
    Router::post('/diretoria/atualizar/{id}', fn($request, $id) => (new DiretoriaController())->atualizar((int) $id), ['auth']);
    Router::get('/diretoria/deletar/{id}', fn($request, $id) => (new DiretoriaController())->deletar((int) $id), ['auth']);

    Router::get('/mandatos', fn() => (new MandatosController())->index(), ['auth']);
    Router::get('/mandatos/cadastrar', fn() => (new MandatosController())->cadastrar(), ['auth']);
    Router::post('/mandatos/salvar', fn() => (new MandatosController())->salvar(), ['auth']);
    Router::get('/mandatos/editar/{id}', fn($request, $id) => (new MandatosController())->editar((int) $id), ['auth']);
    Router::post('/mandatos/atualizar/{id}', fn($request, $id) => (new MandatosController())->atualizar((int) $id), ['auth']);
    Router::get('/mandatos/deletar/{id}', fn($request, $id) => (new MandatosController())->deletar((int) $id), ['auth']);

    Router::get('/documentos', fn() => (new DocumentosController())->index(), ['auth']);
    Router::get('/documentos/cadastrar', fn() => (new DocumentosController())->cadastrar(), ['auth']);
    Router::post('/documentos/salvar', fn() => (new DocumentosController())->salvar(), ['auth']);
    Router::get('/documentos/editar/{id}', fn($request, $id) => (new DocumentosController())->editar((int) $id), ['auth']);
    Router::post('/documentos/atualizar/{id}', fn($request, $id) => (new DocumentosController())->atualizar((int) $id), ['auth']);
    Router::get('/documentos/deletar/{id}', fn($request, $id) => (new DocumentosController())->deletar((int) $id), ['auth']);

    Router::get('/galeria', fn() => (new GaleriaController())->index(), ['auth']);
    Router::get('/galeria/cadastrar', fn() => (new GaleriaController())->cadastrar(), ['auth']);
    Router::post('/galeria/salvar', fn() => (new GaleriaController())->salvar(), ['auth']);
    Router::get('/galeria/editar/{id}', fn($request, $id) => (new GaleriaController())->editar((int) $id), ['auth']);
    Router::post('/galeria/atualizar/{id}', fn($request, $id) => (new GaleriaController())->atualizar((int) $id), ['auth']);
    Router::get('/galeria/deletar/{id}', fn($request, $id) => (new GaleriaController())->deletar((int) $id), ['auth']);

    Router::get('/galeria-categorias', fn() => (new GaleriaCategoriasController())->index(), ['auth']);
    Router::get('/galeria-categorias/cadastrar', fn() => (new GaleriaCategoriasController())->cadastrar(), ['auth']);
    Router::post('/galeria-categorias/salvar', fn() => (new GaleriaCategoriasController())->salvar(), ['auth']);
    Router::get('/galeria-categorias/editar/{id}', fn($request, $id) => (new GaleriaCategoriasController())->editar((int) $id), ['auth']);
    Router::post('/galeria-categorias/atualizar/{id}', fn($request, $id) => (new GaleriaCategoriasController())->atualizar((int) $id), ['auth']);
    Router::get('/galeria-categorias/deletar/{id}', fn($request, $id) => (new GaleriaCategoriasController())->deletar((int) $id), ['auth']);

    Router::get('/patrocinadores', fn() => (new PatrocinadoresController())->index(), ['auth']);
    Router::get('/patrocinadores/cadastrar', fn() => (new PatrocinadoresController())->cadastrar(), ['auth']);
    Router::post('/patrocinadores/salvar', fn() => (new PatrocinadoresController())->salvar(), ['auth']);
    Router::get('/patrocinadores/editar/{id}', fn($request, $id) => (new PatrocinadoresController())->editar((int) $id), ['auth']);
    Router::post('/patrocinadores/atualizar/{id}', fn($request, $id) => (new PatrocinadoresController())->atualizar((int) $id), ['auth']);
    Router::post('/patrocinadores/deletar/{id}', fn($request, $id) => (new PatrocinadoresController())->deletar((int) $id), ['auth']);

    // Plataforma e controle (PT-BR)
    Router::get('/plataforma/status', fn() => (new StatusController())->index(), ['auth']);
    Router::get('/plataforma/guia-usuario', fn() => (new SystemController())->guiaUsuario(), ['auth']);
    Router::get('/plataforma/versoes', fn() => (new SystemController())->versions(), ['auth']);
    Router::get('/plataforma/informacoes-tecnicas', fn() => (new SystemController())->info(), ['auth']);

    // Alias legado
    Router::get('/status', fn() => (new StatusController())->index(), ['auth']);
    Router::get('/system/guia-usuario', fn() => (new SystemController())->guiaUsuario(), ['auth']);
    Router::get('/system/versions', fn() => (new SystemController())->versions(), ['auth']);
    Router::get('/system/info', fn() => (new SystemController())->info(), ['auth']);

    // Conta e preferencias (PT-BR)
    Router::get('/perfil', fn() => (new PerfilController())->index(), ['auth']);
    Router::post('/perfil/update', fn() => (new PerfilController())->update(), ['auth']);
    Router::post('/perfil/update-avatar', fn() => (new PerfilController())->updateAvatar(), ['auth']);
    Router::post('/perfil/change-password', fn() => (new PerfilController())->changePassword(), ['auth']);

    Router::get('/alterar-senha', fn() => (new AlterarSenhaController())->index(), ['auth']);
    Router::post('/alterar-senha/update', fn() => (new AlterarSenhaController())->update(), ['auth']);

    Router::get('/configuracoes', fn() => (new ConfiguracaoController())->index(), ['auth']);
    Router::post('/configuracoes/update', fn() => (new ConfiguracaoController())->update(), ['auth']);

    // Alias legado de conta/plataforma
    Router::get('/profile', fn() => (new PerfilController())->index(), ['auth']);
    Router::post('/profile/update', fn() => (new PerfilController())->update(), ['auth']);
    Router::get('/settings', fn() => (new ConfiguracaoController())->index(), ['auth']);
    Router::post('/settings/update', fn() => (new ConfiguracaoController())->update(), ['auth']);

    // Livro de ocorrencias (bloco legado operacional)
    Router::get('/livro-tipos', fn() => (new LivroTiposController())->index(), ['auth']);
    Router::get('/livro-tipos/create', fn() => (new LivroTiposController())->create(), ['auth']);
    Router::post('/livro-tipos/store', fn() => (new LivroTiposController())->store(), ['auth']);
    Router::get('/livro-tipos/edit/{id}', fn($request, $id) => (new LivroTiposController())->edit((int) $id), ['auth']);
    Router::post('/livro-tipos/update/{id}', fn($request, $id) => (new LivroTiposController())->update((int) $id), ['auth']);
    Router::get('/livro-tipos/destroy/{id}', fn($request, $id) => (new LivroTiposController())->destroy((int) $id), ['auth']);

    Router::get('/livro-ocorrencias', fn() => (new LivroOcorrenciasController())->index(), ['auth']);
    Router::get('/livro-ocorrencias/create', fn() => (new LivroOcorrenciasController())->create(), ['auth']);
    Router::post('/livro-ocorrencias/store', fn() => (new LivroOcorrenciasController())->store(), ['auth']);
    Router::get('/livro-ocorrencias/edit/{id}', fn($request, $id) => (new LivroOcorrenciasController())->edit((int) $id), ['auth']);
    Router::post('/livro-ocorrencias/update/{id}', fn($request, $id) => (new LivroOcorrenciasController())->update((int) $id), ['auth']);
    Router::get('/livro-ocorrencias/destroy/{id}', fn($request, $id) => (new LivroOcorrenciasController())->destroy((int) $id), ['auth']);
    Router::get('/livro-ocorrencias/municipios', fn() => (new LivroOcorrenciasController())->municipios(), ['auth']);

    // Modulos operacionais legados em uso
    Router::get('/categorias', fn() => (new CategoriasController())->index(), ['auth']);
    Router::get('/categorias/cadastrar', fn() => (new CategoriasController())->cadastrar(), ['auth']);
    Router::post('/categorias/salvar', fn() => (new CategoriasController())->salvar(), ['auth']);
    Router::get('/categorias/editar/{id}', fn($request, $id) => (new CategoriasController())->editar((int) $id), ['auth']);
    Router::post('/categorias/atualizar/{id}', fn($request, $id) => (new CategoriasController())->atualizar((int) $id), ['auth']);
    Router::get('/categorias/deletar/{id}', fn($request, $id) => (new CategoriasController())->deletar((int) $id), ['auth']);

    Router::get('/funcoes', fn() => (new FuncoesController())->index(), ['auth']);
    Router::get('/funcoes/cadastrar', fn() => (new FuncoesController())->cadastrar(), ['auth']);
    Router::post('/funcoes/salvar', fn() => (new FuncoesController())->salvar(), ['auth']);
    Router::get('/funcoes/editar/{id}', fn($request, $id) => (new FuncoesController())->editar((int) $id), ['auth']);
    Router::post('/funcoes/atualizar/{id}', fn($request, $id) => (new FuncoesController())->atualizar((int) $id), ['auth']);
    Router::get('/funcoes/deletar/{id}', fn($request, $id) => (new FuncoesController())->deletar((int) $id), ['auth']);

    Router::get('/equipamentos', fn() => (new EquipamentosController())->index(), ['auth']);
    Router::get('/equipamentos/cadastrar', fn() => (new EquipamentosController())->cadastrar(), ['auth']);
    Router::post('/equipamentos/salvar', fn() => (new EquipamentosController())->salvar(), ['auth']);
    Router::get('/equipamentos/editar/{id}', fn($request, $id) => (new EquipamentosController())->editar((int) $id), ['auth']);
    Router::post('/equipamentos/atualizar/{id}', fn($request, $id) => (new EquipamentosController())->atualizar((int) $id), ['auth']);
    Router::get('/equipamentos/deletar/{id}', fn($request, $id) => (new EquipamentosController())->deletar((int) $id), ['auth']);

    Router::get('/obras', fn() => (new ObrasController())->index(), ['auth']);
    Router::get('/obras/cadastrar', fn() => (new ObrasController())->cadastrar(), ['auth']);
    Router::post('/obras/salvar', fn() => (new ObrasController())->salvar(), ['auth']);
    Router::get('/obras/editar/{id}', fn($request, $id) => (new ObrasController())->editar((int) $id), ['auth']);
    Router::post('/obras/atualizar/{id}', fn($request, $id) => (new ObrasController())->atualizar((int) $id), ['auth']);
    Router::get('/obras/deletar/{id}', fn($request, $id) => (new ObrasController())->deletar((int) $id), ['auth']);

    Router::get('/dogs', fn() => (new DogsController())->index(), ['auth']);
    Router::get('/dogs/create', fn() => (new DogsController())->create(), ['auth']);
    Router::post('/dogs/store', fn() => (new DogsController())->store(), ['auth']);
    Router::get('/dogs/edit/{id}', fn($request, $id) => (new DogsController())->edit((int) $id), ['auth']);
    Router::post('/dogs/update/{id}', fn($request, $id) => (new DogsController())->update((int) $id), ['auth']);
    Router::get('/dogs/delete/{id}', fn($request, $id) => (new DogsController())->delete((int) $id), ['auth']);
    Router::get('/dogs/destroy/{id}', fn($request, $id) => (new DogsController())->destroy((int) $id), ['auth']);

    Router::get('/dog-breeds', fn() => (new DogBreedsController())->index(), ['auth']);
    Router::get('/dog-breeds/create', fn() => (new DogBreedsController())->create(), ['auth']);
    Router::post('/dog-breeds/store', fn() => (new DogBreedsController())->store(), ['auth']);
    Router::get('/dog-breeds/edit/{id}', fn($request, $id) => (new DogBreedsController())->edit((int) $id), ['auth']);
    Router::post('/dog-breeds/update/{id}', fn($request, $id) => (new DogBreedsController())->update((int) $id), ['auth']);
    Router::get('/dog-breeds/delete/{id}', fn($request, $id) => (new DogBreedsController())->delete((int) $id), ['auth']);
    Router::get('/dog-breeds/destroy/{id}', fn($request, $id) => (new DogBreedsController())->destroy((int) $id), ['auth']);

    // Hubs internos e documentacao
    Router::get('/institucional', fn() => (new AdminInstitucionalController())->index(), ['auth']);
    Router::get('/memoria', fn() => (new MemoriaController())->index(), ['auth']);
    Router::get('/eventos', fn() => (new EventosController())->index(), ['auth']);

    Router::get('/docs', fn() => (new AdminDocsController())->index(), ['auth']);
    Router::get('/docs/doc/{slug}', fn($request, $slug) => (new AdminDocsController())->doc($slug), ['auth']);
    Router::get('/docs/estrutura', fn() => (new AdminDocsController())->estrutura(), ['auth']);
    Router::get('/docs/virtualhost', fn() => (new AdminDocsController())->virtualhost(), ['auth']);
    Router::get('/docs/composer', fn() => (new AdminDocsController())->composer(), ['auth']);
    Router::get('/docs/diagrama', fn() => (new AdminDocsController())->diagrama(), ['auth']);
    Router::get('/docs/caracteristicas', fn() => (new AdminDocsController())->caracteristicas(), ['auth']);
    Router::get('/docs/fluxomvc', fn() => (new AdminDocsController())->fluxomvc(), ['auth']);
    Router::get('/docs/fluxopost', fn() => (new AdminDocsController())->fluxopost(), ['auth']);
    Router::get('/docs/novofluxomvc', fn() => (new AdminDocsController())->novofluxomvc(), ['auth']);
    Router::get('/docs/blog', fn() => (new AdminDocsController())->blog(), ['auth']);
    Router::get('/docs/elements', fn() => (new AdminDocsController())->elements(), ['auth']);
    Router::get('/docs/scripts', fn() => (new AdminDocsController())->scripts(), ['auth']);
});
