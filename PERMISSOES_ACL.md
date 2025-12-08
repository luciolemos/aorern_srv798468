# Sistema Profissional de Controle de Acesso (ACL)

## 📋 Visão Geral

Implementamos um sistema de **permissões baseado em roles** (ACL - Access Control List) que define com precisão quem pode fazer o quê no sistema.

## 🎯 Estrutura de Roles

### Hierarquia de Permissões

```
┌─ ADMIN (Administrador) ─────────────────────────┐
│ • Acesso total a tudo                           │
│ • Pode criar, editar, deletar qualquer recurso  │
│ • Pode gerenciar usuários                       │
│ • Pode aprovar/bloquear contas                  │
└─────────────────────────────────────────────────┘

┌─ GERENTE ──────────────────────────────────────┐
│ • Pode criar e editar posts                     │
│ • Pode criar equipamentos e obras               │
│ • Pode editar pessoal                           │
│ • NÃO pode deletar ou gerenciar usuários        │
│ • Acesso a dashboard com estatísticas           │
└─────────────────────────────────────────────────┘

┌─ OPERADOR ─────────────────────────────────────┐
│ • Acesso leitura a posts, equipamentos          │
│ • Acesso leitura a pessoal, obras               │
│ • NÃO pode criar/editar/deletar                 │
│ • Apenas consulta e visualização                │
└─────────────────────────────────────────────────┘

┌─ USUÁRIO (Comum) ──────────────────────────────┐
│ • Acesso apenas ao seu perfil                   │
│ • Pode visualizar conteúdo público              │
│ • NÃO pode acessar painel admin                 │
│ • Acesso leitura a blog, home, etc              │
└─────────────────────────────────────────────────┘
```

## 📝 Matriz de Permissões

### POSTS

| Ação | Admin | Gerente | Operador | Usuário |
|------|:-----:|:-------:|:--------:|:-------:|
| **list** | ✅ | ✅ | ✅ | ✅ |
| **create** | ✅ | ✅ | ❌ | ❌ |
| **edit** | ✅ | ✅ | ❌ | ❌ |
| **delete** | ✅ | ❌ | ❌ | ❌ |
| **publish** | ✅ | ✅ | ❌ | ❌ |

### EQUIPAMENTOS

| Ação | Admin | Gerente | Operador | Usuário |
|------|:-----:|:-------:|:--------:|:-------:|
| **list** | ✅ | ✅ | ✅ | ❌ |
| **create** | ✅ | ✅ | ❌ | ❌ |
| **edit** | ✅ | ✅ | ❌ | ❌ |
| **delete** | ✅ | ❌ | ❌ | ❌ |

### PESSOAL

| Ação | Admin | Gerente | Operador | Usuário |
|------|:-----:|:-------:|:--------:|:-------:|
| **list** | ✅ | ✅ | ✅ | ❌ |
| **create** | ✅ | ✅ | ❌ | ❌ |
| **edit** | ✅ | ✅ | ❌ | ❌ |
| **delete** | ✅ | ❌ | ❌ | ❌ |

### USUÁRIOS

| Ação | Admin | Gerente | Operador | Usuário |
|------|:-----:|:-------:|:--------:|:-------:|
| **list** | ✅ | ✅ | ❌ | ❌ |
| **create** | ✅ | ❌ | ❌ | ❌ |
| **edit** | ✅ | ❌ | ❌ | ❌ |
| **delete** | ✅ | ❌ | ❌ | ❌ |
| **approve** | ✅ | ❌ | ❌ | ❌ |
| **block** | ✅ | ❌ | ❌ | ❌ |

## 🔐 Como Usar

### 1. **Validação Obrigatória no Controller**

```php
<?php
namespace App\Controllers\Admin;

use App\Middleware\PermissionMiddleware;

class PostsController extends Controller {
    
    public function create(): void {
        // Valida permissão - redireciona se negar
        PermissionMiddleware::authorize('posts:create');
        
        // Resto do código...
    }
    
    public function store(): void {
        PermissionMiddleware::authorize('posts:create');
        // ...
    }
    
    public function delete(int $id): void {
        PermissionMiddleware::authorize('posts:delete');
        // ...
    }
}
```

### 2. **Validação Condicional (sem redirecionar)**

```php
// Verificar se pode, sem sair da execução
if (PermissionMiddleware::can('posts:delete')) {
    echo '<button>Deletar</button>';
} else {
    echo '<!-- Botão deletar escondido -->'; 
}
```

### 3. **Validação de Propriedade (seu próprio recurso)**

```php
// Permite que:
// - Admins editem qualquer post
// - Usuários comuns editem apenas seus próprios posts

$post = $this->post->encontrarPorId($id);

if (!PermissionMiddleware::canOrOwns('posts:edit', $post['user_id'])) {
    http_response_code(403);
    die('Acesso negado: você não pode editar este post');
}
```

### 4. **Obter Todas as Permissões do Usuário**

```php
$myPermissions = PermissionMiddleware::getMyPermissions();
// Retorna: ['posts:list', 'posts:create', 'dashboard:view', ...]
```

## 📍 Arquivo de Configuração

**`app/Config/Permissions.php`**

Defina aqui todas as permissões:
- `'recurso:acao' => ['role1', 'role2', ...]`

Exemplo:
```php
'posts:create' => ['admin', 'gerente'],    // Só admin e gerente
'posts:delete' => ['admin'],               // Só admin
'dashboard:view' => ['admin', 'gerente', 'operador', 'usuario'],  // Todos
```

## 🔧 Implementação por Controller

### ✅ Já Implementado

- **PostsController** - Permissões para criar, editar, deletar posts
  - `posts:create` (admin, gerente)
  - `posts:edit` (admin, gerente)
  - `posts:delete` (admin)

### ⏳ Para Implementar

Para adicionar permissões a outros controllers:

```php
public function create(): void {
    // Adicione esta linha no início
    PermissionMiddleware::authorize('recurso:create');
    // ...
}
```

## 🚨 O Que Acontece se Negar Acesso?

1. **Se usar `authorize()`**
   - Usuário é redirecionado para `/admin/dashboard`
   - Log é registrado (arquivo error_log)
   - HTTP 403 Forbidden é retornado

2. **Se usar `can()`**
   - Apenas retorna `true/false`
   - Você controla o que fazer (mostrar botão ou não, etc)

## 📊 Logs de Acesso Negado

Toda tentativa de acesso negado é registrada:

```log
[PERMISSION DENIED] User: luciolemos (role: usuario) tried to access: posts:create at /admin/posts/create
```

## 🎓 Exemplo Real: Criar um Post

### Admin
```
1. Clica em "Novo Post"
2. Carrega formulário ✅
3. Submete → PermissionMiddleware::authorize('posts:create') ✅
4. Post criado com sucesso
```

### Gerente
```
1. Clica em "Novo Post"
2. Carrega formulário ✅
3. Submete → PermissionMiddleware::authorize('posts:create') ✅
4. Post criado com sucesso
```

### Operador
```
1. Tenta acessar /admin/posts/create
2. Carrega formulário ❌ (permissão negada)
3. Redireciona para /admin/dashboard
4. Log: [PERMISSION DENIED] operador tried posts:create
```

### Usuário Comum
```
1. Usuário não tem acesso ao /admin/posts
2. Middleware AuthMiddleware::requireAuth() bloqueia
3. Redireciona para /login/admin
```

## ✨ Melhores Práticas

✅ **DO:**
- Validar permissões no início de cada ação
- Usar `authorize()` para ações críticas
- Usar `can()` para UI condicional
- Registrar tentativas de acesso negado
- Testar como cada role funciona

❌ **DON'T:**
- Confiar apenas em validação de UI
- Colocar a validação no final do método
- Esquecer de validar em métodos de deleção
- Hardcoding de roles (sempre use Permissions::has())

## 🔄 Fluxo de Inicialização

```
1. Usuário faz login
   ↓
2. $_SESSION['user_role'] = 'gerente'
   ↓
3. Acessa /admin/posts/create
   ↓
4. PostsController::create()
   └─ PermissionMiddleware::authorize('posts:create')
      └─ Permissions::has('gerente', 'posts:create') ✅
   ↓
5. Formulário exibido
```

## 📚 Referência Rápida

```php
// Valida permissão (redireciona se negar)
PermissionMiddleware::authorize('posts:create');

// Verifica sem redirecionar
if (PermissionMiddleware::can('posts:delete')) { ... }

// Verifica propriedade
PermissionMiddleware::isOwner($userId);

// Verifica permissão OU propriedade
PermissionMiddleware::canOrOwns('posts:edit', $postUserId);

// Obter permissões do usuário
PermissionMiddleware::getMyPermissions();

// Conferir permissão para um role específico
Permissions::has('gerente', 'posts:create');

// Obter todas permissões de um role
Permissions::getByRole('gerente');

// Label amigável
Permissions::getRoleLabel('gerente'); // "Gerente"
```

---

**Criado em:** 7 de Dezembro de 2025
**Sistema:** MVC CBMRN
**Status:** Pronto para Produção ✅
