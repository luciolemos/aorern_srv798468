<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\BlogController;
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

// Login público
Router::get('/login/admin', fn() => (new SiteLoginController())->admin());
Router::post('/login/authenticate-admin', fn() => (new SiteLoginController())->authenticateAdmin());
Router::get('/login/logout', fn() => (new SiteLoginController())->logout());

// Filiação
Router::get('/register', fn() => (new RegisterController())->index());
Router::post('/register/store', fn() => (new RegisterController())->store());
Router::get('/register/cidades-por-uf', fn() => (new RegisterController())->cidadesPorUf());

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
});
