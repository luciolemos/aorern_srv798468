# 🔒 Guia de Segurança - MVC Framework

## ✅ Implementações de Segurança

### 1. **CSRF Protection** (`CsrfHelper`)

Proteção contra Cross-Site Request Forgery em todos os formulários.

#### Uso em Formulários:
```php
<form method="POST" action="<?= BASE_URL ?>admin/posts/create">
    <?= CsrfHelper::inputField() ?>
    
    <input type="text" name="title">
    <button type="submit">Enviar</button>
</form>
```

#### Validação no Controller:
```php
use App\Helpers\CsrfHelper;

public function create() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Valida CSRF ou retorna 403
        CsrfHelper::verifyOrDie();
        
        // Continua com o processamento...
    }
}
```

#### Para Requisições AJAX:
```php
// No layout/header:
<?= CsrfHelper::metaTag() ?>

// No JavaScript:
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
});
```

---

### 2. **Autenticação** (`AuthMiddleware`)

Proteção de rotas administrativas com verificação de sessão.

#### Proteger Rota Admin:
```php
use App\Middleware\AuthMiddleware;

class DashboardController extends Controller {
    public function index() {
        // Verifica se está autenticado
        AuthMiddleware::requireAuth();
        
        // Verifica se é admin
        AuthMiddleware::requireAdmin();
        
        // Código do dashboard...
    }
}
```

#### Login de Usuário:
```php
use App\Middleware\AuthMiddleware;

public function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Valida credenciais...
        $userId = 123;
        
        AuthMiddleware::login($userId, 'admin', [
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);
        
        header('Location: ' . BASE_URL . 'admin/dashboard');
    }
}
```

#### Logout:
```php
use App\Middleware\AuthMiddleware;

public function logout() {
    AuthMiddleware::logout();
    header('Location: ' . BASE_URL . 'admin/login');
}
```

#### Verificar Timeout:
```php
// No início de rotas admin
AuthMiddleware::checkTimeout(1800); // 30 minutos
```

---

### 3. **Validação de Dados** (`Validator`)

Validação robusta de entrada de dados.

#### Validação Básica:
```php
use App\Helpers\Validator;

$validator = new Validator($_POST);

$validator
    ->required('email')
    ->email('email')
    ->required('password')
    ->min('password', 6)
    ->required('name')
    ->min('name', 3)
    ->max('name', 100);

if ($validator->fails()) {
    $errors = $validator->errors();
    // Exibe erros...
} else {
    $data = $validator->validated(); // Dados sanitizados
}
```

#### Validação com Helper Estático:
```php
$v = Validator::make($_POST, [
    'email' => 'required|email',
    'password' => 'required|min:6',
    'name' => 'required|min:3|max:100'
]);

if ($v->passes()) {
    // Sucesso
}
```

#### Validação Customizada:
```php
$validator->custom('username', function($value) {
    return preg_match('/^[a-zA-Z0-9_]+$/', $value);
}, 'Username deve conter apenas letras, números e underscore');
```

---

### 4. **Request Object** (`Request`)

Encapsulamento seguro de superglobals.

#### Uso Básico:
```php
use App\Core\Request;

$request = Request::capture();

// Pegar valores
$email = $request->input('email');
$page = $request->query('page', 1); // Com padrão
$title = $request->post('title');

// Verificar método
if ($request->isPost()) {
    // Processar POST
}

// Verificar campos
if ($request->has('email')) {
    // Email está presente
}

// Pegar apenas campos específicos
$data = $request->only(['name', 'email', 'phone']);

// Upload de arquivos
if ($request->hasFile('avatar')) {
    $file = $request->file('avatar');
}
```

---

### 5. **Database com Prepared Statements**

Todas as queries usam prepared statements para prevenir SQL Injection.

#### Métodos Seguros:
```php
use App\Core\Database;

$db = new Database();

// Buscar todos
$users = $db->all('users');

// Buscar por ID
$user = $db->find('users', 1);

// Buscar com condição
$user = $db->findWhere('users', ['email' => 'user@example.com']);

// Inserir
$id = $db->insert('users', [
    'name' => 'João',
    'email' => 'joao@example.com'
]);

// Atualizar
$db->update('users', 1, ['name' => 'João Silva']);

// Deletar
$db->delete('users', 1);

// Query customizada (sempre com prepared statements)
$stmt = $db->query(
    'SELECT * FROM users WHERE created_at > :date', 
    ['date' => '2024-01-01']
);
```

---

### 6. **Exception Handler**

Tratamento centralizado de erros com logs.

#### Configuração no .env:
```env
APP_ENV=dev    # ou 'production'
```

#### Logs Gerados:
- **Localização**: `/logs/YYYY-MM-DD-errors.log`
- **Formato**: Data, Tipo, Mensagem, Arquivo, Linha, Stack Trace

#### Log Customizado:
```php
use App\Core\ExceptionHandler;

ExceptionHandler::log('Usuário tentou acessar área restrita', 'WARNING');
ExceptionHandler::log('Email enviado com sucesso', 'INFO');
```

---

## 🛡️ Checklist de Segurança

### Antes de ir para Produção:

- [ ] Alterar `.env` com `APP_ENV=production`
- [ ] Desabilitar `display_errors` no PHP
- [ ] Usar HTTPS em produção
- [ ] Implementar CSRF em TODOS os formulários
- [ ] Proteger todas as rotas admin com `AuthMiddleware`
- [ ] Validar TODAS as entradas de usuário
- [ ] Usar `Request` object ao invés de superglobals
- [ ] Revisar permissões de diretórios (logs, uploads)
- [ ] Configurar backup automático do banco
- [ ] Implementar rate limiting para login
- [ ] Adicionar 2FA para admin (futuro)

---

## 📋 Exemplo Completo de Controller Seguro

```php
<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Helpers\CsrfHelper;
use App\Helpers\Validator;

class PostsController extends Controller
{
    public function create()
    {
        // 1. Verificar autenticação
        AuthMiddleware::requireAuth();
        
        $request = Request::capture();
        
        if ($request->isPost()) {
            // 2. Validar CSRF
            CsrfHelper::verifyOrDie();
            
            // 3. Validar dados
            $validator = Validator::make($request->post(), [
                'title' => 'required|min:5|max:200',
                'content' => 'required|min:50',
                'category_id' => 'required|numeric'
            ]);
            
            if ($validator->fails()) {
                return $this->view('admin/posts/create', [
                    'errors' => $validator->errors(),
                    'old' => $request->post()
                ]);
            }
            
            // 4. Processar dados validados
            $data = $validator->validated();
            $data['user_id'] = AuthMiddleware::userId();
            
            // 5. Salvar no banco (prepared statement automático)
            $db = new \App\Core\Database();
            $postId = $db->insert('posts', $data);
            
            header('Location: ' . BASE_URL . 'admin/posts');
            exit;
        }
        
        $this->view('admin/posts/create');
    }
}
```

---

## 🚨 Vulnerabilidades Prevenidas

✅ **SQL Injection** - Prepared statements em todo Database  
✅ **XSS** - Sanitização automática no Validator  
✅ **CSRF** - Token em todos os formulários  
✅ **Session Hijacking** - Validação de IP/User-Agent  
✅ **Session Fixation** - Regeneração de ID no login  
✅ **Directory Traversal** - Sanitização de nomes de tabela/coluna  
✅ **Information Disclosure** - Exception handler com modo dev/prod  

---

## 📚 Referências

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [CSRF Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
