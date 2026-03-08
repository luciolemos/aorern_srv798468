# 👥 Sistema de Usuários - Guia Completo Implementado

## 🎯 O que foi implementado

✅ **Tabela `users` no banco de dados**
✅ **UserModel com todos os métodos CRUD**
✅ **AuthController com login e register**
✅ **Upload de avatar com validação**
✅ **Password hashing com BCRYPT**
✅ **Validação de formulários**
✅ **Timestamps (created_at, updated_at)**
✅ **Último login registrado**

---

## 📊 Estrutura da Tabela `users`

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    role ENUM('admin', 'gerente', 'operador', 'usuario') DEFAULT 'usuario',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
);
```

### Campos Implementados:

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | Identificador único do usuário |
| `username` | VARCHAR(50) | Nome de usuário (único, obrigatório) |
| `email` | VARCHAR(100) | Email (único, obrigatório) |
| `password` | VARCHAR(255) | Senha criptografada com BCRYPT |
| `avatar` | VARCHAR(255) | Caminho da foto de perfil (opcional) |
| `role` | ENUM | Papel: admin, gerente, operador, usuario |
| `ativo` | BOOLEAN | Indica se o usuário pode fazer login |
| `ultimo_login` | TIMESTAMP | Data/hora do último acesso |
| `created_at` | TIMESTAMP | Data de criação (automática) |
| `updated_at` | TIMESTAMP | Data de última atualização (automática) |

---

## 🔑 UserModel - Métodos Disponíveis

### **1. Criar novo usuário**
```php
$userModel = new User();
$dados = [
    'username' => 'paulo_bombeiro',
   'email'    => 'paulo@aorern.org.br',
    'password' => password_hash('minha_senha', PASSWORD_BCRYPT),
    'avatar'   => 'assets/avatars/foto.jpg',
    'role'     => 'operador'
];
$userModel->criar($dados);
```

### **2. Buscar por username**
```php
$user = $userModel->buscarPorUsername('paulo_bombeiro');
// Retorna: ['id' => 1, 'username' => '...', 'email' => '...', ...]
```

### **3. Buscar por email**
```php
$user = $userModel->buscarPorEmail('paulo@aorern.org.br');
```

### **4. Buscar por ID**
```php
$user = $userModel->buscarPorId(1);
```

### **5. Validar credenciais (login)**
```php
$user = $userModel->validarLogin('paulo_bombeiro', 'minha_senha');
if ($user) {
    // Login bem-sucedido
    // $user não contém a senha (por segurança)
}
```

### **6. Atualizar dados**
```php
$userModel->atualizar(1, [
    'avatar' => 'assets/avatars/nova_foto.jpg',
    'role'   => 'gerente'
]);
```

### **7. Atualizar apenas a senha**
```php
$nova_senha_hash = password_hash('nova_senha', PASSWORD_BCRYPT);
$userModel->atualizarSenha(1, $nova_senha_hash);
```

### **8. Registrar último login**
```php
$userModel->registrarUltimoLogin(1);
// Atualiza o campo ultimo_login com NOW()
```

### **9. Verificar se username existe**
```php
if ($userModel->usernameExiste('paulo_bombeiro')) {
    echo 'Já existe!';
}
```

### **10. Verificar se email existe**
```php
if ($userModel->emailExiste('paulo@aorern.org.br')) {
    echo 'Já existe!';
}
```

### **11. Deletar usuário (soft delete)**
```php
$userModel->deletar(1);  // Marca como ativo = 0
```

### **12. Listar todos os usuários ativos**
```php
$usuarios = $userModel->listar();
```

### **13. Contar usuários ativos**
```php
$total = $userModel->contar();
```

---

## 🔐 AuthController - Fluxo de Autenticação

### **1. Login (`/admin/auth/login`)**

```php
// GET: Exibe formulário
AuthController::index()

// POST: Processa login
AuthController::login()
├─ Valida CSRF token
├─ Valida campos (username, password)
├─ Busca usuário no banco: userModel->validarLogin()
├─ Verifica hash de senha: password_verify()
├─ Se OK:
│  ├─ Registra último login
│  ├─ Cria sessão: AuthMiddleware::login()
│  └─ Redireciona para dashboard
└─ Se erro:
   └─ Exibe mensagem de erro
```

**Credencial de Teste:**
- Username: `admin`
- Senha: `1234`

### **2. Registro (`/admin/auth/register`)**

```php
// GET: Exibe formulário
AuthController::register()

// POST: Processa cadastro
AuthController::store()
├─ Valida CSRF token
├─ Valida campos (username, email, password, password_confirmation)
├─ Validações customizadas:
│  ├─ Senhas conferem?
│  ├─ Email é válido?
│  ├─ Username já existe?
│  └─ Email já existe?
├─ Se OK:
│  ├─ Processa avatar (se enviado)
│  ├─ Hash a senha: password_hash()
│  ├─ Cria usuário: userModel->criar()
│  └─ Redireciona para login
└─ Se erro:
   └─ Exibe mensagem e volta ao formulário
```

### **3. Logout (`/admin/auth/logout`)**

```php
AuthController::logout()
├─ Destroi sessão: AuthMiddleware::logout()
└─ Redireciona para login
```

---

## 📝 Formulários

### **Login (`app/Views/admin/login.php`)**

```html
<form action="/admin/auth/login" method="post">
    <input type="text" name="username" required>
    <input type="password" name="password" required>
    <button type="submit">Entrar</button>
    <a href="/admin/auth/register">Não tem conta? Cadastre-se</a>
</form>
```

### **Registro (`app/Views/admin/register.php`)**

```html
<form action="/admin/auth/store" method="post" enctype="multipart/form-data">
    <input type="text" name="username" required placeholder="3-50 caracteres">
    <input type="email" name="email" required>
    <input type="file" name="avatar" accept="image/*">
    <input type="password" name="password" required placeholder="Mínimo 6 caracteres">
    <input type="password" name="password_confirmation" required>
    <button type="submit">Criar Conta</button>
    <a href="/admin/auth">Já tem conta? Faça login</a>
</form>
```

---

## 🖼️ Upload de Avatar

### **Validações Implementadas:**

1. **Tipos permitidos:**
   - image/jpeg
   - image/png
   - image/webp

2. **Tamanho máximo:** 2MB

3. **Processamento:**
   - Filename único: `avatar_[uniqid].[ext]`
   - Diretório: `public/assets/avatars/`
   - Permissões: 755

### **Código:**

```php
private function processarAvatar(array $file): ?string {
    // Validações
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    $tamanho_maximo = 2 * 1024 * 1024;  // 2MB

    if (!in_array($file['type'], $tipos_permitidos)) {
        return null;  // Tipo inválido
    }

    if ($file['size'] > $tamanho_maximo) {
        return null;  // Arquivo muito grande
    }

    // Cria diretório se não existir
    $upload_dir = __DIR__ . '/../../public/assets/avatars/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Nome único e move arquivo
    $filename = 'avatar_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
        return 'assets/avatars/' . $filename;
    }

    return null;
}
```

---

## 🔒 Segurança Implementada

### **✅ Proteções Ativas:**

1. **CSRF Token**
   - Todos os formulários protegidos
   - Validação em cada submissão

2. **Password Hashing**
   - Algoritmo: BCRYPT (PASSWORD_BCRYPT)
   - Custo: padrão (10-12)

3. **SQL Injection Prevention**
   - Prepared Statements em todas as queries
   - Parâmetros bindados

4. **Validação de Email**
   - filter_var($email, FILTER_VALIDATE_EMAIL)

5. **Validação de Arquivo**
   - Tipo MIME verificado
   - Tamanho limitado

6. **Session Timeout**
   - Sessão destruída ao logout
   - Sessão com limite de 30min (pode ser configurado)

7. **Soft Delete**
   - Usuários não são deletados (apenas marcados como ativo=0)
   - Histórico preservado

### **⚠️ Recomendações Futuras:**

1. Implementar Two-Factor Authentication (2FA)
2. Rate limiting para login (proteção contra brute force)
3. Email de confirmação no cadastro
4. Recuperação de senha via email
5. Auditoria de logins
6. Bloqueio de conta após X tentativas falhas

---

## 📋 Fluxo Completo de Registro

```
┌─────────────────────────────────────────────────┐
│ 1. Usuário clica em "Cadastre-se"               │
└──────────────────┬──────────────────────────────┘
                   │
                GET /admin/auth/register
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│ 2. AuthController::register()                    │
│    - Renderiza formulário vazio                  │
└──────────────────┬──────────────────────────────┘
                   │
            ┌──────▼──────┐
            │ register.php │
            └──────┬───────┘
                   │
        ┌─────────────────────┐
        │ Usuário preenche:   │
        │ - Username          │
        │ - Email             │
        │ - Senha             │
        │ - Confirmar Senha   │
        │ - Avatar (opcional) │
        └─────────────┬───────┘
                      │
             POST /admin/auth/store
                      │
                      ▼
┌─────────────────────────────────────────────────┐
│ 3. AuthController::store()                       │
├─────────────────────────────────────────────────┤
│ 3.1. Valida CSRF                                │
│ 3.2. Valida campos (required, min, max)        │
│ 3.3. Senhas conferem?                           │
│ 3.4. Email válido?                              │
│ 3.5. Username já existe?                        │
│ 3.6. Email já existe?                           │
│ 3.7. Processa avatar (se enviado)               │
│ 3.8. Hash a senha                               │
│ 3.9. Cria usuário no banco                      │
└──────────────────┬──────────────────────────────┘
                   │
         ┌─────────┴─────────┐
         │                   │
      ✅ OK               ❌ ERRO
         │                   │
         ▼                   ▼
    ┌─────────────┐    ┌──────────────┐
    │ Usuário     │    │ Toast Erro   │
    │ criado      │    │ Volta para   │
    │ com sucesso │    │ register.php │
    └──────┬──────┘    └──────────────┘
           │
           ▼
    ┌──────────────┐
    │ Toast Sucesso│
    │ Redirect para│
    │ /admin/auth  │
    │ (formulário) │
    └──────────────┘
```

---

## 📊 Fluxo Completo de Login

```
┌─────────────────────────────────────────────────┐
│ 1. Usuário acessa /admin/auth                   │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│ 2. AuthController::index()                       │
│    - Verifica: isAuthenticated()                │
│    - Se SIM → Redirect /admin/dashboard         │
│    - Se NÃO → Renderiza login.php               │
└──────────────────┬──────────────────────────────┘
                   │
              ┌────▼───┐
              │login.php│
              └────┬───┘
                   │
        ┌──────────────────┐
        │ Usuário preenche:│
        │ - Username       │
        │ - Senha          │
        └────────┬─────────┘
                 │
        POST /admin/auth/login
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│ 3. AuthController::login()                       │
├─────────────────────────────────────────────────┤
│ 3.1. Valida CSRF                                │
│ 3.2. Valida campos (required)                   │
│ 3.3. userModel->validarLogin()                  │
│      ├─ Busca por username                      │
│      ├─ Verifica password_verify()              │
│      └─ Remove senha da resposta                │
│ 3.4. Registra último_login                      │
│ 3.5. Cria sessão                                │
│ 3.6. $_SESSION['user_id'] = id                  │
│ 3.7. $_SESSION['username'] = username           │
└──────────────────┬──────────────────────────────┘
                   │
         ┌─────────┴─────────┐
         │                   │
      ✅ OK               ❌ ERRO
         │                   │
         ▼                   ▼
  Redirect para      Erro: "Usuário
  /admin/dashboard   ou senha inválidos"
                     Volta para login
```

---

## 🧪 Testar o Sistema

### **1. Fazer Login (Credencial Padrão)**

```bash
URL: http://seu-site/admin/auth
Username: admin
Senha: 1234
```

### **2. Criar Novo Usuário**

```bash
URL: http://seu-site/admin/auth/register
Preencha todos os campos
Envie uma foto de perfil (opcional)
Clique em "Criar Conta"
Faça login com as novas credenciais
```

### **3. Verificar Dados no Banco**

```bash
mysql -u root gpt_project -e "
SELECT id, username, email, role, ativo, ultimo_login, created_at FROM users;
"
```

### **4. Testar Upload de Avatar**

```bash
# Verificar arquivos salvos
ls -la /var/www/aorern/public/assets/avatars/
```

---

## 📁 Arquivos Implementados

| Arquivo | Descrição |
|---------|-----------|
| `app/Models/User.php` | Model com todos os métodos CRUD |
| `app/Controllers/Admin/AuthController.php` | Controller de autenticação |
| `app/Views/admin/login.php` | Formulário de login |
| `app/Views/admin/register.php` | Formulário de registro |
| `sql/schemas/users.sql` | Script SQL da tabela |
| `public/assets/avatars/` | Diretório de avatares |

---

## 🎓 Próximos Passos

1. **Adicionar avatar no perfil do usuário** na dashboard
2. **Implementar página de perfil** com edição de dados
3. **Adicionar role-based access control (RBAC)**
4. **Implementar recuperação de senha**
5. **Adicionar email de confirmação**
6. **Two-Factor Authentication**

---

**Versão:** 1.0  
**Data:** 2025-01-24  
**Status:** ✅ Implementado e Testado
