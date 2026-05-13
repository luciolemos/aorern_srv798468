<?php

namespace Tests\Integration;

use App\Controllers\Site\LoginController;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

class AuthRoutingIntegrationTest extends TestCase
{
    private static array $dispatchCapture = [];

    public static function setUpBeforeClass(): void
    {
        if (!defined('BASE_URL')) {
            define('BASE_URL', 'https://srv798468.hstgr.cloud/aorern/');
        }
    }

    protected function setUp(): void
    {
        $this->resetRouterState();
        require __DIR__ . '/../../config/routes.php';
    }

    public function testDeclarativeRoutesContainPublicAuthEndpoints(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('POST', $routes);

        $this->assertArrayHasKey('/login/admin', $routes['GET']);
        $this->assertArrayHasKey('/login/logout', $routes['GET']);
        $this->assertArrayHasKey('/login/authenticate-admin', $routes['POST']);
    }

    public function testDeclarativeRoutesContainPublicInstitutionalAndContactEndpoints(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/about', $routes['GET']);
        $this->assertArrayHasKey('/contact', $routes['GET']);
        $this->assertArrayHasKey('/contact/send', $routes['POST']);
        $this->assertArrayHasKey('/galeria', $routes['GET']);

        $this->assertArrayHasKey('/institucional', $routes['GET']);
        $this->assertArrayHasKey('/institucional/missao', $routes['GET']);
        $this->assertArrayHasKey('/institucional/valores', $routes['GET']);
        $this->assertArrayHasKey('/institucional/visao', $routes['GET']);
        $this->assertArrayHasKey('/institucional/brasao', $routes['GET']);
        $this->assertArrayHasKey('/institucional/hino', $routes['GET']);
        $this->assertArrayHasKey('/institucional/links', $routes['GET']);
        $this->assertArrayHasKey('/institucional/identidade-visual', $routes['GET']);
        $this->assertArrayHasKey('/institucional/documentos', $routes['GET']);
        $this->assertArrayHasKey('/institucional/downloads-marca', $routes['GET']);
        $this->assertArrayHasKey('/institucional/governanca', $routes['GET']);
        $this->assertArrayHasKey('/institucional/busca', $routes['GET']);
        $this->assertArrayHasKey('/docs', $routes['GET']);
        $this->assertArrayHasKey('/docs/doc/{slug}', $routes['GET']);
        $this->assertArrayHasKey('/coverage', $routes['GET']);
        $this->assertArrayHasKey('/coverage/relatorio', $routes['GET']);
        $this->assertArrayHasKey('/ocorrencias/mapa-municipios', $routes['GET']);
        $this->assertArrayHasKey('/esquadrao', $routes['GET']);
        $this->assertArrayHasKey('/termos', $routes['GET']);
        $this->assertArrayHasKey('/privacidade', $routes['GET']);
        $this->assertArrayHasKey('/readme', $routes['GET']);
    }

    public function testDeclarativeRoutesContainAdminAuthAndLegacyAliases(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/admin/auth', $routes['GET']);
        $this->assertArrayHasKey('/admin/auth/login', $routes['GET']);
        $this->assertArrayHasKey('/admin/auth/register', $routes['GET']);
        $this->assertArrayHasKey('/admin/auth/logout', $routes['GET']);

        $this->assertArrayHasKey('/admin/login', $routes['GET']);
        $this->assertArrayHasKey('/admin/login/register', $routes['GET']);
        $this->assertArrayHasKey('/admin/login/logout', $routes['GET']);
        $this->assertArrayHasKey('/admin/login/login', $routes['POST']);
        $this->assertArrayHasKey('/admin/login/store', $routes['POST']);
    }

    public function testAdminDashboardRoutesRequireAuthMiddleware(): void
    {
        $routes = Router::routes();

        $this->assertSame(['auth'], $routes['GET']['/admin']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/dashboard']['middleware'] ?? null);
    }

    public function testDeclarativeRoutesContainCoreAdminModules(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/admin/publicacoes', $routes['GET']);
        $this->assertArrayHasKey('/admin/publicacoes/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/categorias-editoriais', $routes['GET']);
        $this->assertArrayHasKey('/admin/categorias-editoriais/salvar', $routes['POST']);

        $this->assertArrayHasKey('/admin/posts', $routes['GET']);
        $this->assertArrayHasKey('/admin/posts/store', $routes['POST']);
        $this->assertArrayHasKey('/admin/post-categories', $routes['GET']);
        $this->assertArrayHasKey('/admin/post-categories/store', $routes['POST']);
        $this->assertArrayHasKey('/admin/usuarios', $routes['GET']);
        $this->assertArrayHasKey('/admin/usuarios/atualizar', $routes['POST']);
        $this->assertArrayHasKey('/admin/solicitacoes-filiacao', $routes['GET']);
        $this->assertArrayHasKey('/admin/solicitacoes-filiacao/aprovar/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/pessoal', $routes['GET']);
        $this->assertArrayHasKey('/admin/pessoal/salvar', $routes['POST']);

        $this->assertSame(['auth'], $routes['GET']['/admin/publicacoes']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/categorias-editoriais']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/posts']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/usuarios']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/pessoal']['middleware'] ?? null);
    }

    public function testDeclarativeRoutesContainAssociadoJourneyEndpoints(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/associado', $routes['GET']);
        $this->assertArrayHasKey('/associado/update', $routes['POST']);
        $this->assertArrayHasKey('/associado/change-password', $routes['POST']);
        $this->assertArrayHasKey('/associado/complementar', $routes['POST']);
    }

    public function testRoleRedirectMappingForLoginFlow(): void
    {
        $this->assertSame('admin/dashboard', LoginController::redirectPathForRole('admin'));
        $this->assertSame('admin/dashboard', LoginController::redirectPathForRole('gerente'));
        $this->assertSame('admin/dashboard', LoginController::redirectPathForRole('operador'));
        $this->assertSame('associado', LoginController::redirectPathForRole('usuario'));
        $this->assertSame('associado', LoginController::redirectPathForRole(null));
    }

    public function testDeclarativeRoutesContainInstitutionalModules(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/admin/diretoria', $routes['GET']);
        $this->assertArrayHasKey('/admin/diretoria/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/mandatos', $routes['GET']);
        $this->assertArrayHasKey('/admin/mandatos/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/documentos', $routes['GET']);
        $this->assertArrayHasKey('/admin/documentos/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/galeria', $routes['GET']);
        $this->assertArrayHasKey('/admin/galeria/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/galeria-categorias', $routes['GET']);
        $this->assertArrayHasKey('/admin/galeria-categorias/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/patrocinadores', $routes['GET']);
        $this->assertArrayHasKey('/admin/patrocinadores/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/plataforma/status', $routes['GET']);
        $this->assertArrayHasKey('/admin/plataforma/guia-usuario', $routes['GET']);
        $this->assertArrayHasKey('/admin/plataforma/versoes', $routes['GET']);
        $this->assertArrayHasKey('/admin/plataforma/informacoes-tecnicas', $routes['GET']);
        $this->assertArrayHasKey('/admin/perfil', $routes['GET']);
        $this->assertArrayHasKey('/admin/perfil/update', $routes['POST']);
        $this->assertArrayHasKey('/admin/alterar-senha', $routes['GET']);
        $this->assertArrayHasKey('/admin/alterar-senha/update', $routes['POST']);
        $this->assertArrayHasKey('/admin/configuracoes', $routes['GET']);
        $this->assertArrayHasKey('/admin/configuracoes/update', $routes['POST']);

        $this->assertArrayHasKey('/admin/status', $routes['GET']);
        $this->assertArrayHasKey('/admin/system/guia-usuario', $routes['GET']);
        $this->assertArrayHasKey('/admin/system/versions', $routes['GET']);
        $this->assertArrayHasKey('/admin/system/info', $routes['GET']);
        $this->assertArrayHasKey('/admin/profile', $routes['GET']);
        $this->assertArrayHasKey('/admin/profile/update', $routes['POST']);
        $this->assertArrayHasKey('/admin/settings', $routes['GET']);
        $this->assertArrayHasKey('/admin/settings/update', $routes['POST']);

        $this->assertSame(['auth'], $routes['GET']['/admin/diretoria']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/mandatos']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/documentos']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/galeria']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/galeria-categorias']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/patrocinadores']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/plataforma/status']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/plataforma/guia-usuario']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/plataforma/versoes']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/plataforma/informacoes-tecnicas']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/perfil']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/alterar-senha']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/configuracoes']['middleware'] ?? null);
    }

    public function testDeclarativeRoutesContainLivroOcorrenciasModules(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/admin/livro-tipos', $routes['GET']);
        $this->assertArrayHasKey('/admin/livro-tipos/create', $routes['GET']);
        $this->assertArrayHasKey('/admin/livro-tipos/store', $routes['POST']);
        $this->assertArrayHasKey('/admin/livro-tipos/edit/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/livro-tipos/update/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/livro-tipos/destroy/{id}', $routes['GET']);

        $this->assertArrayHasKey('/admin/livro-ocorrencias', $routes['GET']);
        $this->assertArrayHasKey('/admin/livro-ocorrencias/create', $routes['GET']);
        $this->assertArrayHasKey('/admin/livro-ocorrencias/store', $routes['POST']);
        $this->assertArrayHasKey('/admin/livro-ocorrencias/edit/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/livro-ocorrencias/update/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/livro-ocorrencias/destroy/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/livro-ocorrencias/municipios', $routes['GET']);

        $this->assertSame(['auth'], $routes['GET']['/admin/livro-tipos']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/livro-tipos/create']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['POST']['/admin/livro-tipos/store']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/livro-tipos/edit/{id}']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['POST']['/admin/livro-tipos/update/{id}']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/livro-tipos/destroy/{id}']['middleware'] ?? null);

        $this->assertSame(['auth'], $routes['GET']['/admin/livro-ocorrencias']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/livro-ocorrencias/create']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['POST']['/admin/livro-ocorrencias/store']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/livro-ocorrencias/edit/{id}']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['POST']['/admin/livro-ocorrencias/update/{id}']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/livro-ocorrencias/destroy/{id}']['middleware'] ?? null);
        $this->assertSame(['auth'], $routes['GET']['/admin/livro-ocorrencias/municipios']['middleware'] ?? null);
    }

    public function testDeclarativeRoutesContainOperationalLegacyModules(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/admin/categorias', $routes['GET']);
        $this->assertArrayHasKey('/admin/categorias/cadastrar', $routes['GET']);
        $this->assertArrayHasKey('/admin/categorias/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/categorias/editar/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/categorias/atualizar/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/categorias/deletar/{id}', $routes['GET']);

        $this->assertArrayHasKey('/admin/funcoes', $routes['GET']);
        $this->assertArrayHasKey('/admin/funcoes/cadastrar', $routes['GET']);
        $this->assertArrayHasKey('/admin/funcoes/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/funcoes/editar/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/funcoes/atualizar/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/funcoes/deletar/{id}', $routes['GET']);

        $this->assertArrayHasKey('/admin/equipamentos', $routes['GET']);
        $this->assertArrayHasKey('/admin/equipamentos/cadastrar', $routes['GET']);
        $this->assertArrayHasKey('/admin/equipamentos/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/equipamentos/editar/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/equipamentos/atualizar/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/equipamentos/deletar/{id}', $routes['GET']);

        $this->assertArrayHasKey('/admin/obras', $routes['GET']);
        $this->assertArrayHasKey('/admin/obras/cadastrar', $routes['GET']);
        $this->assertArrayHasKey('/admin/obras/salvar', $routes['POST']);
        $this->assertArrayHasKey('/admin/obras/editar/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/obras/atualizar/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/obras/deletar/{id}', $routes['GET']);

        $pathsByMethod = [
            'GET' => [
                '/admin/categorias', '/admin/categorias/cadastrar', '/admin/categorias/editar/{id}', '/admin/categorias/deletar/{id}',
                '/admin/funcoes', '/admin/funcoes/cadastrar', '/admin/funcoes/editar/{id}', '/admin/funcoes/deletar/{id}',
                '/admin/equipamentos', '/admin/equipamentos/cadastrar', '/admin/equipamentos/editar/{id}', '/admin/equipamentos/deletar/{id}',
                '/admin/obras', '/admin/obras/cadastrar', '/admin/obras/editar/{id}', '/admin/obras/deletar/{id}',
            ],
            'POST' => [
                '/admin/categorias/salvar', '/admin/categorias/atualizar/{id}',
                '/admin/funcoes/salvar', '/admin/funcoes/atualizar/{id}',
                '/admin/equipamentos/salvar', '/admin/equipamentos/atualizar/{id}',
                '/admin/obras/salvar', '/admin/obras/atualizar/{id}',
            ],
        ];

        foreach ($pathsByMethod as $method => $paths) {
            foreach ($paths as $path) {
                $this->assertSame(['auth'], $routes[$method][$path]['middleware'] ?? null);
            }
        }
    }

    public function testDeclarativeRoutesContainK9LegacyModules(): void
    {
        $routes = Router::routes();

        $this->assertArrayHasKey('/admin/dogs', $routes['GET']);
        $this->assertArrayHasKey('/admin/dogs/create', $routes['GET']);
        $this->assertArrayHasKey('/admin/dogs/store', $routes['POST']);
        $this->assertArrayHasKey('/admin/dogs/edit/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/dogs/update/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/dogs/delete/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/dogs/destroy/{id}', $routes['GET']);

        $this->assertArrayHasKey('/admin/dog-breeds', $routes['GET']);
        $this->assertArrayHasKey('/admin/dog-breeds/create', $routes['GET']);
        $this->assertArrayHasKey('/admin/dog-breeds/store', $routes['POST']);
        $this->assertArrayHasKey('/admin/dog-breeds/edit/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/dog-breeds/update/{id}', $routes['POST']);
        $this->assertArrayHasKey('/admin/dog-breeds/delete/{id}', $routes['GET']);
        $this->assertArrayHasKey('/admin/dog-breeds/destroy/{id}', $routes['GET']);

        $authGetPaths = [
            '/admin/dogs', '/admin/dogs/create', '/admin/dogs/edit/{id}', '/admin/dogs/delete/{id}', '/admin/dogs/destroy/{id}',
            '/admin/dog-breeds', '/admin/dog-breeds/create', '/admin/dog-breeds/edit/{id}', '/admin/dog-breeds/delete/{id}', '/admin/dog-breeds/destroy/{id}',
        ];
        foreach ($authGetPaths as $path) {
            $this->assertSame(['auth'], $routes['GET'][$path]['middleware'] ?? null);
        }

        $authPostPaths = [
            '/admin/dogs/store', '/admin/dogs/update/{id}',
            '/admin/dog-breeds/store', '/admin/dog-breeds/update/{id}',
        ];
        foreach ($authPostPaths as $path) {
            $this->assertSame(['auth'], $routes['POST'][$path]['middleware'] ?? null);
        }
    }

    public function testDeclarativeRoutesContainAdminHubsAndDocsModules(): void
    {
        $routes = Router::routes();

        $authGetPaths = [
            '/admin/institucional',
            '/admin/memoria',
            '/admin/eventos',
            '/admin/docs',
            '/admin/docs/doc/{slug}',
            '/admin/docs/estrutura',
            '/admin/docs/virtualhost',
            '/admin/docs/composer',
            '/admin/docs/diagrama',
            '/admin/docs/caracteristicas',
            '/admin/docs/fluxomvc',
            '/admin/docs/fluxopost',
            '/admin/docs/novofluxomvc',
            '/admin/docs/blog',
            '/admin/docs/elements',
            '/admin/docs/scripts',
        ];

        foreach ($authGetPaths as $path) {
            $this->assertArrayHasKey($path, $routes['GET']);
            $this->assertSame(['auth'], $routes['GET'][$path]['middleware'] ?? null);
        }
    }

    public function testPtBrAndLegacyAliasPairsStaySynchronizedForMiddleware(): void
    {
        $routes = Router::routes();
        $pairs = [
            ['GET', '/admin/publicacoes', '/admin/posts'],
            ['GET', '/admin/publicacoes/cadastrar', '/admin/posts/create'],
            ['POST', '/admin/publicacoes/salvar', '/admin/posts/store'],
            ['GET', '/admin/publicacoes/editar/{id}', '/admin/posts/edit/{id}'],
            ['POST', '/admin/publicacoes/atualizar/{id}', '/admin/posts/update/{id}'],
            ['GET', '/admin/categorias-editoriais', '/admin/post-categories'],
            ['GET', '/admin/categorias-editoriais/cadastrar', '/admin/post-categories/create'],
            ['POST', '/admin/categorias-editoriais/salvar', '/admin/post-categories/store'],
            ['GET', '/admin/plataforma/status', '/admin/status'],
            ['GET', '/admin/plataforma/guia-usuario', '/admin/system/guia-usuario'],
            ['GET', '/admin/plataforma/versoes', '/admin/system/versions'],
            ['GET', '/admin/plataforma/informacoes-tecnicas', '/admin/system/info'],
            ['GET', '/admin/perfil', '/admin/profile'],
            ['POST', '/admin/perfil/update', '/admin/profile/update'],
            ['GET', '/admin/configuracoes', '/admin/settings'],
            ['POST', '/admin/configuracoes/update', '/admin/settings/update'],
        ];

        foreach ($pairs as [$method, $newPath, $legacyPath]) {
            $this->assertArrayHasKey($newPath, $routes[$method], "Rota PT-BR ausente: {$method} {$newPath}");
            $this->assertArrayHasKey($legacyPath, $routes[$method], "Alias legado ausente: {$method} {$legacyPath}");

            $newMiddleware = $routes[$method][$newPath]['middleware'] ?? null;
            $legacyMiddleware = $routes[$method][$legacyPath]['middleware'] ?? null;

            $this->assertSame(
                $newMiddleware,
                $legacyMiddleware,
                "Middleware divergente entre {$newPath} e {$legacyPath}"
            );
        }
    }

    public function testRouterDispatchResolvesBasePathAndDynamicParamsInSubdirectoryDeploy(): void
    {
        $this->resetRouterState();
        self::$dispatchCapture = [];

        Router::get('/probe/{id}', function ($request, $id): void {
            self::$dispatchCapture = [
                'id' => $id,
                'path' => $request->path(),
                'method' => $request->method(),
            ];
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/aorern/probe/42?origem=teste';

        $handled = Router::dispatch(new \App\Core\Request());

        $this->assertTrue($handled);
        $this->assertSame('42', self::$dispatchCapture['id'] ?? null);
        $this->assertSame('/aorern/probe/42', self::$dispatchCapture['path'] ?? null);
        $this->assertSame('GET', self::$dispatchCapture['method'] ?? null);
    }

    public function testRouterDispatchReturnsFalseWhenRouteDoesNotExist(): void
    {
        $this->resetRouterState();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/aorern/nao-existe';

        $handled = Router::dispatch(new \App\Core\Request());

        $this->assertFalse($handled);
    }

    private function resetRouterState(): void
    {
        $class = new \ReflectionClass(Router::class);
        foreach (['routes', 'middlewares', 'currentGroup', 'groupMiddleware'] as $property) {
            $prop = $class->getProperty($property);
            $prop->setAccessible(true);
            $default = match ($property) {
                'currentGroup' => null,
                'groupMiddleware' => [],
                'middlewares' => [],
                default => [],
            };
            $prop->setValue(null, $default);
        }
    }
}
