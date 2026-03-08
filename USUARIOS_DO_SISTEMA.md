# 👤 Usuários do Sistema AORE/RN - Explicação Completa

## 🎯 Resumo Executivo

**⚠️ IMPORTANTE:** O sistema diferencia entre:
1. **Bombeiros (Pessoal)** - Cadastrados na tabela `pessoal` (dados da corporação)
2. **Usuários do Sistema** - Cadastrados na tabela `users` (credenciais de login)

Atualmente, o sistema **NÃO possui uma tabela `users` implementada**. A autenticação é **hardcoded** (fixa no código).

---

## 🔐 Sistema de Autenticação Atual

### ❌ Como NÃO está funcionando:
```php
// Não existe tabela users no banco
// Não há validação contra banco de dados
```

### ✅ Como está funcionando agora:
```php
// Credenciais hardcoded no AuthController
if ($user === 'admin' && $pass === '1234') {
    // Login bem-sucedido
}
```

**Arquivo:** `app/Controllers/Admin/AuthController.php` (linhas 49-50)

---

## 📋 Fluxo de Login

### **1. Usuário Acessa `/admin/auth`**

```
GET /admin/auth
│
└─► AuthController::index()
    └─► Exibe formulário de login
```

### **2. Formulário de Login**

**Arquivo:** `app/Views/admin/login.php`

```html
<form action="/admin/auth/login" method="post">
    <!-- CSRF Token (proteção) -->
    
    <input name="username" required>     <!-- Campo: Usuário -->
    <input name="password" type="password" required>  <!-- Campo: Senha -->
    
    <button type="submit">Entrar</button>
</form>
```

### **3. Submissão (POST /admin/auth/login)**

```
POST /admin/auth/login
│
└─► AuthController::login()
    │
    ├─ 1️⃣ Valida CSRF token
    ├─ 2️⃣ Verifica campos (username, password)
    ├─ 3️⃣ Compara com valores hardcoded
    │   └─ username === 'admin' && password === '1234'
    │
    ├─ ✅ SE correto:
    │   ├─ AuthMiddleware::login(1, 'admin', [...])
    │   ├─ $_SESSION['user_id'] = 1
    │   ├─ $_SESSION['username'] = 'admin'
    │   └─ Redireciona para /admin/dashboard
    │
    └─ ❌ SE incorreto:
        └─ Exibe erro "Usuário ou senha inválidos"
```

---

## 🔑 Credenciais Atuais

### **Única Credencial Válida:**

| Campo | Valor |
|-------|-------|
| **Usuário** | `admin` |
| **Senha** | `1234` |

**Localização no código:**
```php
// app/Controllers/Admin/AuthController.php:49-50
if ($user === 'admin' && $pass === '1234') {
    // Login aceito
}
```

⚠️ **CRÍTICO:** Esta é uma implementação **temporária** apenas para desenvolvimento!

---

## 🗄️ Tabela de Usuários - Estrutura Proposta

Atualmente **NÃO existe** a tabela `users` no banco. Aqui está a estrutura recomendada:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- password_hash()
    nome VARCHAR(100),
    role VARCHAR(50) DEFAULT 'admin',  -- admin, gerente, operador
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Campos:**
- `id`: Identificador único do usuário
- `username`: Nome de usuário (único)
- `email`: Email (único)
- `password`: Senha criptografada com `password_hash()`
- `nome`: Nome completo do usuário
- `role`: Papel do usuário (admin, gerente, operador)
- `ativo`: Indica se o usuário pode fazer login
- `ultimo_login`: Data/hora do último acesso
- `criado_em`: Data de criação
- `atualizado_em`: Data de última atualização

---

## 📊 Diferença: Bombeiro vs Usuário

### **Tabela `pessoal` (Bombeiros)**
```sql
SELECT * FROM pessoal;
```

Contém: **Dados dos Bombeiros da corporação**
- Nome completo
- CPF
- Data de nascimento
- Telefone
- Função (Soldado, Sargento, etc)
- Obra associada
- Data de admissão
- Status (Ativo, Afastado, etc)
- Jornada de trabalho

### **Tabela `users` (Usuários do Sistema) - Proposta**
```sql
SELECT * FROM users;
```

Deveria conter: **Credenciais de acesso ao sistema**
- Username (ex: "paulobombeiro")
- Email (ex: "paulo@aorern.org.br")
- Senha criptografada
- Papel/permissão (admin, gerente, operador)
- Data do último login

### **Relacionamento Proposto:**
```sql
-- Opção 1: Um bombeiro pode ser usuário do sistema
ALTER TABLE users ADD COLUMN pessoal_id INT;
ALTER TABLE users ADD FOREIGN KEY (pessoal_id) REFERENCES pessoal(id);

-- Ou Opção 2: Usuarios independentes
-- Nenhuma relação obrigatória
```

---

## 🔄 Fluxo de Autenticação (Diagrama)

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Navegador (Usuário não autenticado)                          │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                GET /admin/auth
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. AuthController::index()                                       │
│    - Verifica: AuthMiddleware::isAuthenticated()                │
│    - Se autenticado → Redireciona para /admin/dashboard         │
│    - Se não autenticado → Exibe formulário de login             │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. Formulário de Login renderizado                              │
│    app/Views/admin/login.php                                    │
│    - Campo: username                                            │
│    - Campo: password                                            │
│    - CSRF token protegendo o formulário                         │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                 POST /admin/auth/login
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│ 4. AuthController::login()                                       │
│    - Valida CSRF: CsrfHelper::verifyOrDie()                     │
│    - Valida campos: username e password preenchidos             │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│ 5. Verificação de Credenciais                                   │
│                                                                 │
│    if ($user === 'admin' && $pass === '1234') {               │
│        ✅ Login bem-sucedido                                    │
│    } else {                                                     │
│        ❌ Erro: Usuário ou senha inválidos                     │
│    }                                                             │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                  ┌────────┴────────┐
                  │                 │
              ✅ SIM              ❌ NÃO
                  │                 │
                  ▼                 ▼
        ┌─────────────────┐  ┌───────────────────────┐
        │ Login bem-sucesso│  │ Erro: Usuário inválido│
        └────────┬────────┘  └──────────┬────────────┘
                 │                      │
                 ▼                      ▼
    AuthMiddleware::login()    Exibe login.php com erro
    │
    ├─ $_SESSION['user_id'] = 1
    ├─ $_SESSION['username'] = 'admin'
    ├─ $_SESSION['user'] = 'admin'
    ├─ $_SESSION['last_activity'] = time()
    │
    └─ Redireciona para /admin/dashboard
                 │
                 ▼
        ┌──────────────────────┐
        │ Dashboard (Protegido) │
        │ - Sidebar visível    │
        │ - Menu bombeiros  │
        │ - Seção admin        │
        │ - KPIs e estatísticas│
        └──────────────────────┘
```

---

## 🛡️ Middleware de Autenticação

### **AuthMiddleware::requireAuth()**

Bloqueia qualquer controller/rota que exija autenticação:

```php
// Usado em todos os controllers admin
public function __construct() {
    AuthMiddleware::requireAuth();  // ← Verifica se $_SESSION['user_id'] existe
}
```

**Se não autenticado:**
```
302 Found → Redireciona para /admin/auth
```

**Se autenticado:**
```
200 OK → Executa o controller normalmente
```

---

## 📝 Código Relevante

### **1. Login (AuthController)**
```php
// app/Controllers/Admin/AuthController.php

public function login() {
    // Verifica CSRF
    CsrfHelper::verifyOrDie();
    
    $user = $request->post('username');
    $pass = $request->post('password');

    // ⚠️ HARDCODED - Deve ser substituído por banco de dados
    if ($user === 'admin' && $pass === '1234') {
        // Cria sessão autenticada
        AuthMiddleware::login(1, 'admin', [
            'name' => 'Administrador',
            'username' => $user
        ]);
        
        header('Location: ' . BASE_URL . 'admin/dashboard');
        exit;
    }
    
    $error = 'Usuário ou senha inválidos.';
    $this->view('admin/login', compact('error'), 'auth');
}
```

### **2. Logout (AuthController)**
```php
public function logout() {
    AuthMiddleware::logout();
    header('Location: ' . BASE_URL . 'admin/auth');
    exit;
}
```

### **3. Verificação de Sessão**
```php
// app/Middleware/AuthMiddleware.php

public static function requireAuth() {
    if (!self::isAuthenticated()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . 'admin/auth');
        exit;
    }
}

public static function isAuthenticated(): bool {
    return isset($_SESSION['user_id']);
}
```

---

## 🚀 Próximos Passos (Recomendações)

### **1. Implementar Tabela de Usuários (Priority: ALTA)**

```bash
# Executar no banco de dados
mysql -u luciolemos -p gpt_project < criar_tabela_users.sql
```

### **2. Atualizar AuthController**

Substituir hardcoded por:
```php
$userModel = new UserModel();
$user = $userModel->buscarPorUsername($username);

if ($user && password_verify($password, $user['password'])) {
    // Login bem-sucedido
} else {
    // Erro
}
```

### **3. Implementar Password Hashing**

```php
// Ao registrar/atualizar senha
$senha_hash = password_hash($senha_plana, PASSWORD_BCRYPT);

// Ao verificar
password_verify($senha_plana, $senha_hash)
```

### **4. Adicionar Papel de Usuário (RBAC)**

```php
// Role-Based Access Control
if ($user['role'] !== 'admin') {
    die("Acesso negado");
}
```

### **5. Registrar Último Login**

```php
$userModel->atualizarUltimoLogin($user_id, time());
```

---

## 📊 Status Atual do Sistema

| Feature | Status | Nota |
|---------|--------|------|
| Login com formulário | ✅ Implementado | Funciona, mas com credenciais hardcoded |
| Logout | ✅ Implementado | Destroi sessão |
| Proteção de rotas (AuthMiddleware) | ✅ Implementado | Funciona em todos os controllers admin |
| Tabela `users` | ❌ Não existe | Precisa ser criada |
| Múltiplos usuários | ❌ Não suportado | Apenas 'admin/1234' funciona |
| Password hashing | ❌ Não implementado | Senhas em texto plano (não seguro) |
| Papel de usuário (RBAC) | ❌ Não implementado | Todos têm acesso admin total |
| Registrar último login | ❌ Não implementado | Não há histórico de acessos |
| Recuperação de senha | ❌ Não implementado | Sem suporte |
| Two-Factor Authentication | ❌ Não implementado | Sem suporte |

---

## ⚠️ Questões de Segurança

### **🔴 CRÍTICOS:**
1. ✋ Credenciais hardcoded no código (qualquer pessoa pode ver)
2. 🔓 Sem criptografia de senha (texto plano)
3. 👤 Sem suporte a múltiplos usuários
4. 📋 Sem auditoria de logins

### **🟡 IMPORTANTES:**
1. Sem validação de força de senha
2. Sem timeout de sessão configurável
3. Sem bloqueio após tentativas falhas
4. Sem recuperação de senha

### **🟢 BOM:**
1. ✅ CSRF token protegendo formulário
2. ✅ Validação de campos
3. ✅ Autenticação obrigatória em rotas admin
4. ✅ Logout seguro

---

## 🎓 Conclusão

**Quem são os usuários do sistema?**

Atualmente: **Apenas uma pessoa** (`admin` com senha `1234`)

**Próximo passo:** Implementar tabela `users` com suporte a múltiplos usuários, senhas criptografadas e papéis de acesso.

---

**Última atualização:** 2025-01-24  
**Versão:** 1.0  
**Status:** Desenvolvimento (Credenciais temporárias)
