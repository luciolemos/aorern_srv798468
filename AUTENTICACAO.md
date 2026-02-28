# Sistema Unificado de Autenticação - Documentação

## Visão Geral

O sistema de autenticação foi **completamente unificado em um único ponto de entrada**: o formulário de login do **ADMIN** (não é exclusivo para administradores).

### Filosofia de Acesso

- **Usuários públicos**: Acessam conteúdo público SEM fazer login
- **Usuários registrados**: Podem criar uma conta (REGISTRAR) e depois logar
- **Após aprovação pelo admin**: Acessam funcionalidades específicas baseadas no seu `role`
  - `admin`, `gerente`, `operador` → Painel de administração completo
  - `usuario` → Conteúdo específico e seu perfil

## Fluxo de Autenticação

### 1. Usuário Comum - Registrar Conta Nova

```
Navbar: [ADMIN] 
  ↓
Acesso: https://site.com/login/admin
  ↓
Link "Registre-se aqui"
  ↓
RegisterController::index() → Renderiza auth/register.twig
  ↓
Preencher: username, email, senha, foto (opcional)
  ↓
RegisterController::store()
  ├─ Validações (email único, username único, senha 6+ chars)
  ├─ Hash password (bcrypt)
  ├─ Salva avatar em /public/uploads/users/
  ├─ Cria usuário com status='pendente', ativo=0, role='usuario'
  └─ Redireciona → /login/admin?success=account_created
```

### 2. Login - Após Registro ou Aprovação

```
Acesso: https://site.com/login/admin
  ↓
Submeter: username + senha
  ↓
LoginController::authenticateAdmin()
  ├─ Validar campos vazios
  ├─ Buscar usuário por username
  ├─ Verificar password (bcrypt)
  ├─ Validar se ativo = 1 (aprovado pelo admin)
  ├─ Registrar último login
  ├─ Setar sessão
  └─ Redirecionar:
      ├─ Se role = admin/gerente/operador → /admin/dashboard (painel completo)
      └─ Se role = usuario → /perfil (seu próprio perfil)
```

### 3. Logout

```
Acesso: https://site.com/login/logout
  ↓
LoginController::logout()
  ├─ Limpar $_SESSION
  ├─ Destruir sessão
  └─ Redirecionar → / (home)
```

## Estrutura de Arquivos

### Controllers

**`app/Controllers/Site/LoginController.php`** (Principal)
```php
class LoginController extends Controller {
    // Exibe formulário de login
    public function admin()
    
    // Processa login (username + senha)
    public function authenticateAdmin()
    
    // Faz logout
    public function logout()
}
```

**`app/Controllers/Site/RegisterController.php`**
```php
class RegisterController extends Controller {
    // Exibe formulário de registro
    public function index()
    
    // Processa registro (cria nova conta com status=pendente)
    public function store()
    
    // Salva avatar em /public/uploads/users/
    private function processAvatar()
}
```

### Views/Templates

**`app/Views/templates/auth/admin-login.twig`** (Principal)
- Título: "Painel Admin"
- Subtítulo: "Acesso restrito a administradores e usuários registrados"
- Campos: Usuário (username), Senha
- Botão: "Entrar no Painel"
- Link: "Não tem uma conta? Registre-se aqui"
- Mostra mensagens de sucesso (após registro) e erro

**`app/Views/templates/auth/register.twig`**
- Título: "Criar Nova Conta"
- Campos: Username, Email, Senha, Confirmar Senha, Avatar (opcional)
- Validações client-side e server-side
- Avatar preview e validação de tamanho
- Link: "Já tem uma conta? Faça login aqui"

### Navbar - Componente Dinâmico

**`app/Views/templates/components/navbar.twig`**

**Quando usuário NÃO está logado:**
```
[INÍCIO] [SOBRE] [BLOG] [CONTATO] [ADMIN]
                                    └─ Link para /login/admin
```

**Quando usuário ESTÁ logado:**
```
[INÍCIO] [SOBRE] [BLOG] [CONTATO] [👤 João Silva ▼]
                                      │
                                      ├─ Logado como: João Silva (email@exemplo.com)
                                      ├─ 🔧 Painel Admin (se role admin/gerente/operador)
                                      ├─ 👤 Meu Perfil
                                      ├─ 🔑 Alterar Senha
                                      └─ ❌ Sair
```

## Validação de Erros - Login

| Erro | Mensagem |
|------|----------|
| `admin_empty_fields` | "Preencha usuário e senha." |
| `admin_invalid_credentials` | "Credenciais inválidas. Confira seu usuário e senha." |
| `admin_not_authorized` | "Sua conta ainda não foi aprovada pelo administrador." |
| `admin_inactive` | "Sua conta foi desativada. Contate o administrador." |

## Validação de Erros - Registro

| Erro | Mensagem |
|------|----------|
| `empty_fields` | "Preencha todos os campos." |
| `empty_username` | "Usuário é obrigatório." |
| `invalid_email` | "Email inválido." |
| `password_too_short` | "A senha deve ter pelo menos 6 caracteres." |
| `passwords_dont_match` | "As senhas não conferem." |
| `username_already_exists` | "Usuário já existe." |
| `email_already_exists` | "Email já cadastrado." |

## Variáveis de Sessão

Após login bem-sucedido, as seguintes variáveis são setadas:

```php
$_SESSION['user_id']     // ID do usuário
$_SESSION['user_email']  // Email do usuário
$_SESSION['user_name']   // Username (login)
$_SESSION['user_avatar'] // Caminho da avatar (se houver)
$_SESSION['user_role']   // Tipo de acesso: admin, gerente, operador, usuario
```

## Fluxo Completo (Novo Usuário)

```
1. Visita site.com
   ↓
2. Vê conteúdo público (home, blog, about, contact)
   ↓
3. Clica em [ADMIN] na navbar
   ↓
4. Acessa site.com/login/admin
   ↓
5. Clica em "Registre-se aqui"
   ↓
6. Preenche formulário e envia
   ↓
7. Sistema redireciona para site.com/login/admin?success=account_created
   ↓
8. Admin aprova conta (ativo=1, status='ativo')
   ↓
9. Usuário volta ao login e entra com suas credenciais
   ↓
10. Se role='usuario' → vê seu perfil
    Se role='admin/gerente/operador' → vê painel completo
```

## Melhorias Implementadas

✅ **Simplicidade**
- Um único ponto de entrada: `/login/admin`
- Fluxo claro: público → registrar → aguardar aprovação → login → área restrita

✅ **Segurança**
- Username + senha (não email, reduz exposição de emails)
- Verificação de `ativo` (aprovação obrigatória)
- Validação de role para acesso admin

✅ **UX**
- Link claro: "ADMIN" na navbar
- Mensagem de sucesso após registro
- Avatar display para usuários logados
- Dropdown menu com opções do usuário

✅ **Mantibilidade**
- Um único LoginController com métodos bem definidos
- RegisterController simplificado
- Views claras e bem documentadas

## Rotas Disponíveis

| Método | Rota | Função | Status |
|--------|------|--------|--------|
| GET | `/login/admin` | Exibir form login | ✅ |
| POST | `/login/authenticate-admin` | Processar login | ✅ |
| GET | `/register` | Exibir form registro | ✅ |
| POST | `/register/store` | Processar registro | ✅ |
| GET | `/login/logout` | Fazer logout | ✅ |

## Proteção de Rotas

Para proteger rotas admin, adicionar verificação no topo do controller:

```php
public function dashboard() {
    // Verifica autenticação
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login/admin");
        exit;
    }
    
    // Verifica role (admin only)
    if (!in_array($_SESSION['user_role'], ['admin', 'gerente', 'operador'])) {
        http_response_code(403);
        die("Acesso negado");
    }
    
    // ... resto do código
}
```

## Próximas Melhorias (Opcional)

1. **"Esqueci minha senha"** - Reset via email
2. **2FA** - Para contas de admin
3. **Logs de acesso** - Quem, quando, de onde
4. **Rate limiting** - Proteção contra força bruta (N tentativas)
5. **Email de aprovação** - Notificar usuário quando aprovado

## Arquivos Modificados

✅ `app/Controllers/Site/LoginController.php` - Simplificado (3 métodos)
✅ `app/Controllers/Site/RegisterController.php` - Redirecionamento atualizado
✅ `app/Views/templates/auth/admin-login.twig` - Link de registro adicionado
✅ `app/Views/templates/components/navbar.twig` - Apenas link ADMIN visível
✅ `app/Core/TwigEngine.php` - Sessão global adicionada
✅ `app/Views/templates/auth/login.twig` - Removido do fluxo ativo

