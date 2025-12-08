# 🏗️ Arquitetura do Projeto MVC

## 📁 Estrutura de Diretórios

```
mvc/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          # Controllers administrativos
│   │   └── Site/           # Controllers do site público
│   ├── Core/
│   │   ├── App.php         # Bootstrap da aplicação
│   │   ├── Controller.php  # Controller base
│   │   ├── Database.php    # Camada de banco de dados
│   │   ├── Router.php      # Sistema de rotas
│   │   ├── View.php        # Renderização de views
│   │   ├── Request.php     # Objeto de requisição HTTP
│   │   └── ExceptionHandler.php  # Tratamento de exceções
│   ├── Helpers/
│   │   ├── CsrfHelper.php     # Proteção CSRF
│   │   ├── Validator.php      # Validação de dados
│   │   ├── FormatHelper.php   # Formatação de dados
│   │   └── RouteHelper.php    # Helpers de rota
│   ├── Middleware/
│   │   └── AuthMiddleware.php # Autenticação e autorização
│   ├── Models/
│   │   └── *.php           # Models de dados
│   └── Views/
│       ├── layouts/        # Layouts (admin, main, etc)
│       ├── admin/          # Views administrativas
│       └── *.php           # Views do site
├── config/
│   └── config.php          # Configurações da aplicação
├── logs/                   # Logs de erro e aplicação
├── public/
│   ├── index.php           # Entry point
│   ├── .htaccess           # Rewrite rules
│   └── assets/             # CSS, JS, imagens
├── sql/                    # Schemas e seeds do banco
├── tests/                  # Testes PHPUnit
├── vendor/                 # Dependências Composer
├── .env                    # Variáveis de ambiente
├── composer.json           # Dependências
├── phpunit.xml             # Configuração PHPUnit
└── SECURITY.md            # Guia de segurança
```

---

## 🔄 Fluxo de Execução

### 1. **Entry Point** (`public/index.php`)

```
Request → .htaccess → index.php
```

1. Carrega autoloader do Composer
2. Carrega configurações (`.env` e `config.php`)
3. Registra `ExceptionHandler`
4. Inicia sessão
5. Instancia `App`

### 2. **Bootstrap** (`App.php`)

```
App::__construct() → Router::parseUrl() → resolveController() → execute()
```

1. Parseia URL via `Router`
2. Resolve Controller e Method baseado na URL
3. Aplica middlewares (se houver)
4. Executa o método do controller

### 3. **Controller** (Herda `Controller.php`)

```
Controller::method() → view() ou model()
```

1. Recebe requisição
2. Valida dados (CSRF, Validator)
3. Interage com Model
4. Renderiza View ou redireciona

### 4. **View** (Renderização)

```
Controller::view() → Extrai $data → Require layout
```

1. Recebe dados do controller
2. Extrai variáveis
3. Renderiza dentro do layout especificado

---

## 🎯 Padrões Implementados

### **MVC (Model-View-Controller)**

- **Model**: Camada de dados (`Database.php` + Models específicos)
- **View**: Templates PHP em `app/Views/`
- **Controller**: Lógica de negócio em `app/Controllers/`

### **Front Controller**

- Único ponto de entrada: `public/index.php`
- Todas as requisições passam pelo mesmo arquivo

### **Dependency Injection**

```php
class UserController extends Controller {
    public function __construct() {
        $this->userModel = $this->model('User');
    }
}
```

### **Repository Pattern** (Parcial)

`Database.php` funciona como repository genérico com métodos CRUD reutilizáveis.

---

## 🛣️ Sistema de Rotas

### **Rotas Atuais** (Via URL parsing)

```
/                         → Site\HomeController::index()
/about                    → Site\AboutController::index()
/admin/dashboard          → Admin\DashboardController::index()
/admin/posts/edit/3       → Admin\PostsController::edit(3)
```

### **Rotas Explícitas** (Novo sistema disponível)

```php
use App\Core\Router;
use App\Middleware\AuthMiddleware;

// Rotas públicas
Router::get('/', 'Site\HomeController@index');
Router::get('/blog', 'Site\BlogController@index');
Router::get('/blog/{id}', 'Site\BlogController@show');

// Grupo admin com middleware
Router::group('/admin', function() {
    Router::get('/dashboard', 'Admin\DashboardController@index');
    Router::get('/posts', 'Admin\PostsController@index');
    Router::post('/posts/create', 'Admin\PostsController@store');
}, ['auth']);

// Registrar middleware
Router::middleware('auth', function($request) {
    if (!AuthMiddleware::isAuthenticated()) {
        header('Location: /admin/login');
        exit;
    }
});
```

---

## 🔐 Camadas de Segurança

### **1. Input (Request)**

```
Request → Validator → Sanitização → Controller
```

### **2. Autenticação**

```
Route → AuthMiddleware::requireAuth() → Controller
```

### **3. CSRF Protection**

```
Form → CsrfHelper::inputField() → POST → CsrfHelper::verifyOrDie()
```

### **4. Database**

```
Controller → Database (prepared statements) → MySQL
```

### **5. Output (View)**

```
Controller → View (htmlspecialchars automático) → HTML
```

---

## 📊 Exemplo de Fluxo Completo

### **Criar Post no Admin**

```
1. GET /admin/posts/create
   ↓
2. AuthMiddleware::requireAuth()
   ↓
3. Admin\PostsController::create()
   ↓
4. View: admin/posts/create.php (com CSRF token)
   ↓
5. Usuário preenche formulário
   ↓
6. POST /admin/posts/create
   ↓
7. AuthMiddleware::requireAuth()
   ↓
8. CsrfHelper::verifyOrDie()
   ↓
9. Validator::make($_POST, [...])
   ↓
10. Database::insert('posts', $validatedData)
    ↓
11. Redirect para /admin/posts
```

---

## 🧩 Componentes Principais

### **Core/App.php**
- Bootstrap da aplicação
- Resolução de rotas
- Carregamento de controllers

### **Core/Router.php**
- Parsing de URLs
- Registro de rotas explícitas
- Suporte a middlewares
- Parâmetros dinâmicos

### **Core/Request.php**
- Encapsulamento de `$_GET`, `$_POST`, `$_SERVER`
- Métodos helper (isPost, isAjax, etc)
- Sanitização de input

### **Core/Database.php**
- Singleton PDO
- CRUD genérico
- Prepared statements
- Transações

### **Core/ExceptionHandler.php**
- Captura de exceções
- Logging estruturado
- Renderização de erros (dev/prod)

### **Middleware/AuthMiddleware.php**
- Verificação de autenticação
- Controle de sessão
- Login/Logout
- Proteção contra hijacking

### **Helpers/Validator.php**
- Validação de campos
- Regras encadeadas
- Sanitização automática
- Mensagens customizadas

### **Helpers/CsrfHelper.php**
- Geração de tokens
- Validação de tokens
- Helper para formulários
- Suporte a AJAX

---

## 🎨 Convenções de Nomenclatura

### **Controllers**
- Namespace: `App\Controllers\{Area}\`
- Nome: `{Nome}Controller.php`
- Exemplo: `App\Controllers\Admin\PostsController`

### **Models**
- Namespace: `App\Models\`
- Nome: `{Nome}Model.php` ou `{Nome}.php`
- Exemplo: `App\Models\Post`

### **Views**
- Localização: `app/Views/{area}/{controller}/{action}.php`
- Exemplo: `app/Views/admin/posts/create.php`

### **Métodos**
- camelCase: `createPost()`, `findById()`
- Verbos: `index()`, `show()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`

---

## 🔧 Configuração

### **.env**
```env
APP_ENV=dev

DB_HOST=localhost
DB_NAME=mvc_db
DB_USER=root
DB_PASS=

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
```

### **config/config.php**
```php
define('BASE_URL', 'http://localhost/');
define('DB_HOST', $_ENV['DB_HOST']);
// etc...
```

---

## 📈 Próximas Melhorias Sugeridas

1. **Service Layer** - Lógica de negócio entre Controller e Model
2. **API REST** - Endpoints JSON com autenticação JWT
3. **Cache** - Redis/Memcached para queries
4. **Queue System** - Jobs assíncronos
5. **Event System** - Observers/Listeners
6. **Command Bus** - CQRS pattern
7. **Rate Limiting** - Proteção contra brute force
8. **2FA** - Autenticação de dois fatores
9. **File Manager** - Upload seguro de arquivos
10. **Migrations** - Versionamento de banco de dados

---

## 📚 Dependências

### **Composer**
```json
{
  "require": {
    "vlucas/phpdotenv": "^5.5",        // Variáveis de ambiente
    "phpmailer/phpmailer": "^6.8",     // Envio de emails
    "ext-pdo": "*"                      // PDO PHP extension
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0"         // Testes unitários
  }
}
```

---

## 🧪 Testes

```bash
# Rodar todos os testes
./vendor/bin/phpunit

# Com cobertura
./vendor/bin/phpunit --coverage-html coverage
```

---

## 📖 Documentação Adicional

- **SECURITY.md** - Guia completo de segurança
- **README.md** - Visão geral do projeto
- **estrutura.md** - Estrutura detalhada dos arquivos
