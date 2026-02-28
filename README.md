# 🚀 Lucio Lemos - Sistema MVC Profissional

**Plataforma empresarial completa com fluxo de publicação, gerenciamento de equipamentos, autenticação multi-role e blog profissional.**

---

## 📑 Índice

1. [Visão Geral](#visão-geral)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Arquitetura](#arquitetura)
4. [Instalação & Setup](#instalação--setup)
5. [Módulos Principais](#módulos-principais)
   - [Home & Apresentação](#-home--apresentação)
   - [Blog Público](#-blog-público)
   - [Painel Admin](#-painel-admin)
   - [Gestão de Usuários](#-gestão-de-usuários)
   - [Gestão de Equipamentos](#-gestão-de-equipamentos)
6. [Fluxo de Posts](#fluxo-de-posts)
7. [Sistema de Permissões](#sistema-de-permissões)
8. [Banco de Dados](#banco-de-dados)
9. [Segurança](#segurança)
10. [Testes](#testes)
11. [Estrutura de Pastas](#estrutura-de-pastas)

---

## 🎯 Visão Geral

Sistema MVC moderno construído em **PHP puro** (sem frameworks) com arquitetura profissional, oferecendo:

- ✅ **Blog profissional** com fluxo de aprovação em 5 etapas
- ✅ **Painel administrativo** multi-role (usuario/operador/gerente/admin)
- ✅ **Gestão de equipamentos** com categorias e filtros avançados
- ✅ **Autenticação unificada** com segurança CSRF e sessions
- ✅ **Editor WYSIWYG** (Quill.js) para criação de conteúdo
- ✅ **100% responsivo** (Bootstrap 5.3.3)
- ✅ **Cobertura de testes** 82%+ com PHPUnit

**Base de usuários:** Lucio Lemos (admin), Fernando (usuario), Maria (gerente)

---

## 🛠️ Stack Tecnológico

| Camada | Tecnologia |
|--------|-----------|
| **Backend** | PHP 8.2+ (POO, namespaces) |
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla) |
| **Framework CSS** | Bootstrap 5.3.3 |
| **Editor** | Quill.js 2.0.0 (local bundles) |
| **Banco de Dados** | MySQL 5.7+ |
| **Templating** | Twig 3.x |
| **Testes** | PHPUnit 11.x |
| **Build** | Composer 2.x |
| **Versionamento** | Git (GitHub) |

---

## 🏗️ Arquitetura

```
┌────────────────────────────────────────────────────────────────┐
│                       REQUISIÇÃO HTTP                          │
└───────────────────────────┬──────────────────────────────────────┘
                            │
                    ┌───────▼────────┐
                    │  public/index  │  (entry point)
                    └───────┬────────┘
                            │
                    ┌───────▼────────┐
                    │    Router      │  (app/Core/Router)
                    │   (Matching)   │
                    └───────┬────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
    ┌───▼──┐            ┌──▼──┐            ┌──▼──┐
    │ Site │            │Admin │           │Auth │
    └───┬──┘            └──┬──┘            └──┬──┘
        │                  │                  │
   ┌────▼──────┐   ┌───────▼────────┐   ┌────▼─────┐
   │ Controller │   │ Middleware     │   │Permission│
   │  (lógica)  │   │  (validação)   │   │Middleware│
   └────┬──────┘   └───────┬────────┘   └────┬─────┘
        │                  │                  │
   ┌────▼──────┐           │                  │
   │   Model   │◄──────────┴──────────────────┘
   │(queries)  │
   └────┬──────┘
        │
   ┌────▼──────────────────┐
   │   Database (MySQL)    │
   └───────────────────────┘
        │
   ┌────▼──────┐
   │   View    │  (Twig template)
   │ (render)  │
   └────┬──────┘
        │
   ┌────▼──────────────────┐
   │ HTTP Response (HTML)  │
   └───────────────────────┘
```

---

## ⚙️ Instalação & Setup

### 1. **Clonar Repositório**
```bash
git clone https://github.com/luciolemos/cbmrn_srv798468.git
cd cbmrn_srv798468
```

### 2. **Instalar Dependências**
```bash
composer install
```

### 3. **Configurar Ambiente**

**`config/config.php`:**
```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'luciolemos_mvc');
define('DB_USER', 'root');
define('DB_PASS', '');

// URLs
define('BASE_URL', 'http://localhost/cbmrn/');
define('SITE_URL', 'http://localhost/cbmrn/');
```

### 4. **Criar Banco de Dados**
```bash
# MySQL
mysql -u root -p
> CREATE DATABASE luciolemos_mvc;
> USE luciolemos_mvc;
> source sql/schema.sql;
> source sql/seeds/001_base_data.sql;

Ou execute diretamente apontando o banco já criado:

```bash
mysql -u luciolemos -p mvc < sql/schema.sql
mysql -u luciolemos -p mvc < sql/seeds/001_base_data.sql
```
```

### 5. **Configurar VirtualHost (Apache)**
```apache
<VirtualHost *:80>
    ServerName cbmrn.local
    ServerAlias www.cbmrn.local
    DocumentRoot /var/www/cbmrn/public
    
    <Directory /var/www/cbmrn/public>
        AllowOverride All
        Order Allow,Deny
        Allow from all
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php/$1 [L]
        </IfModule>
    </Directory>
</VirtualHost>
```

### 6. **Testar**
```bash
# Desenvolvimento
php -S localhost:8000 -t public

# Ou navegar para http://cbmrn.local
```

---

## 📦 Módulos Principais

### 🏠 **HOME & APRESENTAÇÃO**

**Rota:** `GET /` → `HomeController@index()`  
**View:** `app/Views/templates/site/home.twig`

**Conteúdo:**
- Hero section com CTA "Ver Blog"
- Últimos 6 posts publicados em grid responsivo
- Seção "Sobre" (link para /sobre)
- Newsletter signup (contato)
- Footer com links

**Dados:**
```php
// BlogController retorna
$posts = $postModel->listarPublico(null, null, 1, 6);
// Filtra: status='published' AND is_hidden=0
```

**Recursos:**
- ✅ Sem autenticação (público)
- ✅ Posts mostram: capa, título, categoria, autor, data
- ✅ Click no post → `/blog/{slug}`

---

### 📚 **BLOG PÚBLICO**

**Rota:** `GET /blog` → `BlogController@index()`  
**Rota:** `GET /blog/{slug}` → `BlogController@post()`  
**Views:** 
- `app/Views/templates/site/blog.twig` (lista)
- `app/Views/templates/site/post.twig` (individual)

**Funcionalidades - Lista:**
- 📄 Paginação (7 posts/página)
- 🔍 Busca por título/conteúdo
- 🏷️ Filtro por categoria
- 📊 Exibição: capa, título, autor, data, resumo (300 chars)

**Funcionalidades - Post Individual:**
- 🖼️ Imagem de capa full-width
- 📰 Conteúdo completo (Quill HTML preservado)
- 👤 Info autor (nome, data, categoria)
- ◀️ Link "Post Anterior"
- ▶️ Link "Próximo Post"
- 💬 Seção comentários (integração futura)

**Query SQL:**
```sql
SELECT * FROM posts 
WHERE status='published' 
  AND is_hidden=0
ORDER BY criado_em DESC
LIMIT 7 OFFSET 0
```

**Notas:**
- ❌ Posts em `draft`, `pending`, `rejected` NÃO aparecem
- ❌ Posts com `is_hidden=1` NÃO aparecem
- ✅ Navegação anterior/próximo respeita filtros

---

### 👤 **SOBRE / CONTATO**

**Rota:** `GET /sobre` → `AboutController@index()`  
**Rota:** `GET /contato` → `ContactController@index()`  
**Rota:** `POST /contato` → `ContactController@store()`  

**About (`/sobre`):**
- Texto sobre Lucio Lemos
- Link para blog
- CTA para contato

**Contato (`/contato`):**
- Formulário: nome, email, assunto, mensagem
- Validação client/server
- Email enviado para admin
- Toast de sucesso/erro

---

### 🔐 **ADMIN - AUTENTICAÇÃO**

**Rota:** `GET /login/admin` → `AuthController@index()`  
**Rota:** `POST /login/admin` → `AuthController@login()`  
**Rota:** `GET /logout` → `AuthController@logout()`  

**Credenciais de Teste:**
```
Usuário: luciolemos
Senha: senha123

Usuário: fernando
Senha: senha123

Usuário: maria
Senha: senha123
```

**Fluxo Login:**
1. Username + Password → validação BD
2. Sessão criada: `user_id`, `user_role`, `user_name`, `user_email`, `user_avatar`
3. Redireciona para `/admin/dashboard`
4. Middleware `AuthMiddleware` protege rotas admin

**Middleware Stack:**
```php
AuthMiddleware ↓ (isLoggedIn)
PermissionMiddleware ↓ (posts:edit, posts:approve, etc)
Handler (controller method)
```

---

### 🎛️ **ADMIN - DASHBOARD**

**Rota:** `GET /admin/dashboard` → `DashboardController@index()`  
**View:** `app/Views/templates/admin/dash.twig`

**Widgets por Role:**
- **Admin:** Posts pendentes, Usuários inativos, Equipamentos baixo estoque
- **Gerente:** Posts pendentes de revisão
- **Usuario:** Meus posts (rascunhos + submetidos)
- **Operador:** (sem dados críticos)

**Cards:**
```
📊 Total de Posts: 24
⏳ Pendentes: 3
❌ Rejeitados: 1
👥 Usuários: 5
🔧 Equipamentos: 18
```

---

### 📝 **ADMIN - GESTÃO DE POSTS** ⭐

**Rota:** `GET /admin/posts` → `PostsController@index()`  
**Rota:** `GET /admin/posts/create` → `PostsController@create()`  
**Rota:** `POST /admin/posts/store` → `PostsController@store()`  
**Rota:** `GET /admin/posts/edit/{id}` → `PostsController@edit()`  
**Rota:** `POST /admin/posts/update/{id}` → `PostsController@update()`  
**Rota:** `POST /admin/posts/approve/{id}` → `PostsController@approve()`  
**Rota:** `POST /admin/posts/reject/{id}` → `PostsController@reject()`  
**Rota:** `POST /admin/posts/hide/{id}` → `PostsController@hide()`  
**Rota:** `POST /admin/posts/show/{id}` → `PostsController@show()`  

**Veja documentação completa:** `POST_WORKFLOW.md`

**Resumo:**
- 5 status: `draft`, `pending`, `published`, `rejected`, `hidden` (flag)
- 4 roles: usuario (autor), operador (visualizador), gerente (revisor), admin (aprovador)
- Workflow: draft → pending → published → hidden ou rejected
- Usuario corrige rejeitados e resubmete

---

### 👥 **ADMIN - GESTÃO DE USUÁRIOS**

**Rota:** `GET /admin/usuarios` → `UsuariosController@index()`  
**Rota:** `GET /admin/usuarios/create` → `UsuariosController@create()`  
**Rota:** `POST /admin/usuarios/store` → `UsuariosController@store()`  
**Rota:** `GET /admin/usuarios/edit/{id}` → `UsuariosController@edit()`  
**Rota:** `POST /admin/usuarios/update/{id}` → `UsuariosController@update()`  
**Rota:** `POST /admin/usuarios/ativar/{id}` → `UsuariosController@ativar()`  

**Campos:**
```
├─ Nome (varchar 100)
├─ Email (varchar 120, unique)
├─ Username (varchar 50, unique)
├─ Senha (bcrypt)
├─ Role (enum: usuario, operador, gerente, admin)
├─ Status (enum: inativo, ativo, suspenso)
├─ Avatar (URL)
├─ Criado em (datetime)
└─ Atualizado em (datetime)
```

**Roles:**
| Role | Descrição | Permissões |
|------|-----------|-----------|
| **usuario** | Autor de conteúdo | Criar/editar próprios drafts, submeter |
| **operador** | Visualizador | Ver posts pending/published |
| **gerente** | Revisor | Revisar/rejeitar posts |
| **admin** | Administrador | Todas as ações |

**Fluxo Novo Usuário:**
1. Admin preenche: nome, email, username, role
2. Senha gerada aleatória e enviada por email
3. Status = `inativo` (requer ativação)
4. Usuario ativa clicando link (email)
5. Status = `ativo`

---

### 🔧 **ADMIN - GESTÃO DE EQUIPAMENTOS**

**Rota:** `GET /admin/equipamentos` → `EquipamentosController@index()`  
**Rota:** `GET /admin/equipamentos/create` → `EquipamentosController@create()`  
**Rota:** `POST /admin/equipamentos/store` → `EquipamentosController@store()`  
**Rota:** `GET /admin/equipamentos/edit/{id}` → `EquipamentosController@edit()`  
**Rota:** `POST /admin/equipamentos/update/{id}` → `EquipamentosController@update()`  

**Campos:**
```
├─ Nome (varchar 150)
├─ Descrição (text)
├─ Categoria (FK → categorias_equipamentos)
├─ Quantidade (int)
├─ Locação (varchar 100)
├─ Status (enum: ativo, inativo, manutenção)
├─ Foto (URL)
└─ Data de Aquisição (date)
```

**Funcionalidades:**
- ✅ CRUD completo
- ✅ Filtro por categoria
- ✅ Busca por nome
- ✅ Alert estoque baixo (<5 unidades)
- ✅ Status (ativo/inativo/manutenção)
- ✅ Paginação (10 por página)

**Categoria (modelo relacional):**
```sql
CREATE TABLE categorias_equipamentos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(100),
  descricao TEXT,
  icone VARCHAR(50)
);
```

---

### 👨‍💼 **ADMIN - GESTÃO DE PESSOAL**

**Rota:** `GET /admin/pessoal` → `PessoalController@index()`  
**Rota:** `GET /admin/pessoal/create` → `PessoalController@create()`  
**Rota:** `POST /admin/pessoal/store` → `PessoalController@store()`  

**Campos:**
```
├─ Nome (varchar 150)
├─ Função (FK → funcoes)
├─ Email (varchar 120)
├─ Telefone (varchar 20)
├─ Data de Admissão (date)
├─ Salário (decimal 10,2)
├─ Status (enum: ativo, inativo, afastado)
└─ Foto (URL)
```

**Modelo Função:**
```sql
CREATE TABLE funcoes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(100),
  descricao TEXT,
  departamento VARCHAR(100)
);
```

---

### 📊 **ADMIN - WORKS (OBRAS)**

**Rota:** `GET /admin/obras` → `ObrasController@index()`  

**Campos:**
```
├─ Título (varchar 150)
├─ Descrição (text)
├─ Local (varchar 200)
├─ Data início (date)
├─ Data fim (date, nullable)
├─ Status (enum: planejado, em_progresso, concluído)
├─ Orçamento (decimal 12,2)
├─ Responsável (FK → pessoal)
└─ Galeria fotos (JSON array)
```

---

### 📰 **ADMIN - CATEGORIAS**

**Rota:** `GET /admin/categorias` → `CategoriasController@index()`  

**Para Posts:**
```sql
CREATE TABLE categorias_posts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(100),
  descricao TEXT,
  badge_color VARCHAR(7),  -- #FF5733
  icone VARCHAR(50)        -- bi-star
);
```

**Para Equipamentos:** (já mencionado acima)

---

## 🔄 Fluxo de Posts (Resumo)

Para documentação completa, veja **`POST_WORKFLOW.md`**

```
USUARIO              GERENTE/ADMIN           ADMIN
   │                    │                     │
   ├─ Cria draft ──────▶│                     │
   │                    │                     │
   ├─ Submete ─────────▶│ Revisa              │
   │                    │ (modal preview)     │
   │◀─ Rejeitado ───────│                     │
   │                    │                     │
   ├─ Corrige          │                     │
   ├─ Resubmete ──────▶│ Aprova ────────────▶│
   │                    │                    │ Publica
   │                    │                    │ (status=published)
   │                    │                    │
   │                    │                    ├─ Oculta (is_hidden=1)
   │                    │                    │ OU
   │                    │                    ├─ Deleta
```

---

## 🔐 Sistema de Permissões

**Arquivo:** `app/Config/Permissions.php`

**Permissões:**
```php
'usuario' => [
    'posts:create' => true,
    'posts:edit' => 'own-draft-or-rejected',  // Próprios
    'posts:submit' => true,
],
'gerente' => [
    'posts:review' => true,
    'posts:reject' => true,
    'posts:edit' => 'pending-only',
],
'admin' => [
    '*' => true,  // Todas permissões
],
```

**Middleware:**
```php
// No controller
PermissionMiddleware::authorize('posts:approve');

// Throws 403 se permissão negada
```

---

## 💾 Banco de Dados

### **Diagrama ER**

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│   users     │      │    posts     │      │ categorias_ │
├─────────────┤      ├──────────────┤      │   posts     │
│ id (PK)     │      │ id (PK)      │      ├─────────────┤
│ username    │      │ titulo       │◀─────│ id (PK)     │
│ email       │      │ slug         │      │ nome        │
│ password    │      │ conteudo     │      │ badge_color │
│ role        │      │ user_id (FK) │────┐ │ icone       │
│ status      │      │ status       │    │ └─────────────┘
│ avatar      │      │ reject_reason│    │
└─────────────┘      │ published_at │    │ ┌──────────────┐
       △              │ is_hidden    │    └─│ (FK: users)  │
       │              │ criado_em    │      └──────────────┘
       │              └──────────────┘
       │
    (user_id)

┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│  equipamentos    │  │  pessoal         │  │  obras           │
├──────────────────┤  ├──────────────────┤  ├──────────────────┤
│ id (PK)          │  │ id (PK)          │  │ id (PK)          │
│ nome             │  │ nome             │  │ titulo           │
│ descricao        │  │ email            │  │ descricao        │
│ categoria_id(FK) │  │ funcao_id (FK)   │  │ data_inicio      │
│ quantidade       │  │ data_admissao    │  │ data_fim         │
│ status           │  │ status           │  │ responsavel(FK)  │
│ foto             │  │ foto             │  │ orcamento        │
└──────────────────┘  └──────────────────┘  └──────────────────┘
```

### **Tabelas Principais**

```sql
-- Users (autenticação + roles)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(120) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('usuario','operador','gerente','admin') DEFAULT 'usuario',
  status ENUM('inativo','ativo','suspenso') DEFAULT 'inativo',
  avatar VARCHAR(255),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Posts (blog com workflow)
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(200) NOT NULL,
  slug VARCHAR(200) UNIQUE NOT NULL,
  conteudo LONGTEXT NOT NULL,
  user_id INT NOT NULL,
  categoria_id INT,
  capa_url VARCHAR(512),
  status ENUM('draft','pending','published','rejected') DEFAULT 'draft',
  reject_reason TEXT,
  published_at DATETIME,
  is_hidden TINYINT(1) DEFAULT 0,
  autor VARCHAR(100),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (categoria_id) REFERENCES categorias_posts(id)
);

-- Categorias de Posts
CREATE TABLE categorias_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT,
  badge_color VARCHAR(7),
  icone VARCHAR(50),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Equipamentos
CREATE TABLE equipamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  descricao TEXT,
  categoria_id INT,
  quantidade INT DEFAULT 0,
  locacao VARCHAR(100),
  status ENUM('ativo','inativo','manutencao') DEFAULT 'ativo',
  foto VARCHAR(255),
  data_aquisicao DATE,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias_equipamentos(id)
);

-- Pessoal
CREATE TABLE pessoal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  funcao_id INT,
  email VARCHAR(120),
  telefone VARCHAR(20),
  data_admissao DATE,
  salario DECIMAL(10,2),
  status ENUM('ativo','inativo','afastado') DEFAULT 'ativo',
  foto VARCHAR(255),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (funcao_id) REFERENCES funcoes(id)
);

-- Obras
CREATE TABLE obras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  descricao TEXT,
  local VARCHAR(200),
  data_inicio DATE,
  data_fim DATE,
  status ENUM('planejado','em_progresso','concluido') DEFAULT 'planejado',
  orcamento DECIMAL(12,2),
  responsavel_id INT,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (responsavel_id) REFERENCES pessoal(id)
);
```

---

## 🔒 Segurança

### ✅ **Implementado**

1. **CSRF Protection**
   - Token por sessão (validade 1 hora)
   - Verificação em todos POST/PUT/DELETE
   - `CsrfHelper::verifyOrDie()`

2. **SQL Injection Prevention**
   - Prepared statements (PDO)
   - Parameterized queries
   - Validação server-side

3. **Password Security**
   - Hash bcrypt (cost=12)
   - Salt automático
   - Validação mínimo 8 caracteres

4. **Session Management**
   - Session timeout 1 hora
   - Regeneração após login
   - HttpOnly cookies
   - Secure flag (HTTPS)

5. **Access Control**
   - AuthMiddleware em rotas admin
   - PermissionMiddleware com ACL
   - Role-based endpoint authorization
   - Database-level query filtering

6. **Input Validation**
   - Server-side com classe `Validator`
   - Sanitização HTML (striptags onde necessário)
   - Whitelist de valores (status enum)

7. **Error Handling**
   - Exceções genéricas (sem leaks de info)
   - Logs em arquivo
   - 404 pages customizadas

---

## ✅ Testes

**Framework:** PHPUnit 11.x  
**Cobertura:** 82%+

### **Executar Testes**
```bash
./vendor/bin/phpunit
# Ou
php run-tests.sh
```

### **Relatório de Cobertura**
```bash
./vendor/bin/phpunit --coverage-html coverage/html
# Abra localmente: coverage/html/index.html
```

### **Testes Principais**

**`tests/Controllers/HomeControllerTest.php`**
- GET / retorna 200
- Posts renderizados corretamente

**`tests/Controllers/BlogControllerTest.php`**
- GET /blog retorna posts publicados
- Filtro por categoria
- Paginação

**`tests/Controllers/AdminControllerTest.php`**
- Login com credenciais válidas
- Acesso negado sem autenticação
- POST com CSRF válido

**`tests/Controllers/PostsControllerTest.php`**
- Usuario cria draft
- Submissão para review
- Admin aprova → published
- Rejeição com motivo
- Ocultação (is_hidden)

---

## 📁 Estrutura de Pastas

```
mvc/
├── app/
│   ├── Config/
│   │   ├── Permissions.php          # ACL rules
│   │   └── config.php               # Settings
│   │
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── PostsController.php       ⭐ Fluxo posts
│   │   │   ├── UsuariosController.php
│   │   │   ├── EquipamentosController.php
│   │   │   ├── PessoalController.php
│   │   │   ├── ObrasController.php
│   │   │   └── [outras controllers]
│   │   │
│   │   └── Site/
│   │       ├── HomeController.php
│   │       ├── BlogController.php        ⭐ Blog público
│   │       ├── AboutController.php
│   │       ├── ContactController.php
│   │       └── [outras controllers]
│   │
│   ├── Core/
│   │   ├── App.php                  # Bootstrap
│   │   ├── Router.php               # URL routing
│   │   ├── Controller.php           # Base controller
│   │   ├── Database.php             # PDO connection
│   │   └── Request.php              # HTTP request
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php       # Login required
│   │   └── PermissionMiddleware.php # ACL check
│   │
│   ├── Models/
│   │   ├── Post.php                 # Blog queries
│   │   ├── User.php                 # User queries
│   │   ├── Equipamento.php
│   │   ├── Pessoal.php
│   │   └── [outros models]
│   │
│   ├── Helpers/
│   │   ├── CsrfHelper.php           # CSRF tokens
│   │   ├── AdminHelper.php
│   │   ├── RouteHelper.php
│   │   ├── FormatHelper.php
│   │   ├── Toast.php                # Flash messages
│   │   └── SystemVersions.php
│   │
│   └── Views/
│       ├── templates/
│       │   ├── site/
│       │   │   ├── home.twig             # Homepage
│       │   │   ├── blog.twig             # Lista posts
│       │   │   ├── post.twig             # Post individual
│       │   │   ├── about.twig
│       │   │   ├── contact.twig
│       │   │   └── layouts/
│       │   │       └── base_site.twig
│       │   │
│       │   └── admin/
│       │       ├── dash.twig             # Dashboard
│       │       ├── posts/
│       │       │   ├── index.twig        # Lista posts
│       │       │   ├── create.twig       # Novo post
│       │       │   ├── edit.twig         # Editar post
│       │       │   └── [outros]
│       │       ├── usuarios/
│       │       │   ├── index.twig
│       │       │   ├── create.twig
│       │       │   └── [outros]
│       │       ├── equipamentos/
│       │       ├── pessoal/
│       │       ├── obras/
│       │       └── layouts/
│       │           └── base_admin.twig
│       │
│       └── 404.twig                  # Error page
│
├── public/
│   ├── index.php                    # Entry point
│   ├── assets/
│   │   ├── css/
│   │   │   ├── bootstrap.min.css
│   │   │   ├── admin.css            # Quill styling
│   │   │   ├── site.css
│   │   │   └── [outros]
│   │   ├── js/
│   │   │   ├── bootstrap.bundle.min.js
│   │   │   ├── quill.js             # Editor local
│   │   │   ├── aos.js               # Animations
│   │   │   └── [scripts]
│   │   └── images/
│   │       └── [imagens]
│   │
│   └── .htaccess                    # URL rewrite rules
│
├── sql/
│   ├── schema.sql                   # Estrutura tabelas completa
│   ├── seeds/
│   │   └── 001_base_data.sql        # Dados iniciais (inclui mapa operacional)
│   └── migrations/
│       └── Migration_*.php          # Schema updates
│
├── tests/
│   ├── Controllers/
│   │   ├── HomeControllerTest.php
│   │   ├── BlogControllerTest.php
│   │   ├── AdminControllerTest.php
│   │   ├── ContactControllerTest.php
│   │   └── [outros testes]
│   └── bootstrap.php
│
├── vendor/                          # Composer packages
├── coverage/                        # PHPUnit coverage report
├── .gitignore
├── composer.json
├── composer.lock
├── phpunit.xml
├── run-tests.sh
├── POST_WORKFLOW.md                 # Documentação workflow
├── README.md                        # Este arquivo
└── estrutura.md

```

---

## 🚀 Deploy

### **Produção (Apache)**

1. **Clonar em `/var/www/`**
   ```bash
   git clone https://github.com/luciolemos/cbmrn_srv798468.git /var/www/cbmrn
   cd /var/www/cbmrn
   ```

2. **Instalar dependências**
   ```bash
   composer install --no-dev
   chmod -R 755 app/Views
   ```

3. **Configurar banco**
   ```bash
   mysql -u root -p < sql/schema.sql
   mysql -u root -p < sql/seeds/001_base_data.sql
   ```

4. **Permissões**
   ```bash
   chown -R www-data:www-data /var/www/cbmrn
   chmod -R 755 /var/www/cbmrn/public
   chmod -R 775 /var/www/cbmrn/app/Views
   ```

5. **SSL (Let's Encrypt)**
   ```bash
   certbot certonly --apache -d mvc.example.com
   ```

---

## 📞 Suporte

- **GitHub Issues:** [Reportar bugs](https://github.com/luciolemos/cbmrn_srv798468/issues)
- **Email:** lucio@example.com
- **Docs:** [Veja POST_WORKFLOW.md](./POST_WORKFLOW.md)

---

## 📄 Licença

MIT License - Veja LICENSE.md

---

## 👨‍💻 Autor

**Lucio Lemos**  
Desenvolvedor Full Stack | PHP | JavaScript | MySQL

- 🔗 GitHub: [@luciolemos](https://github.com/luciolemos)
- 💼 LinkedIn: [Lucio Lemos](https://linkedin.com/in/luciolemos)
- 🌐 Website: [luciolemos.dev](https://luciolemos.dev)

---

**Versão:** 1.0.0  
**Última atualização:** 2025-12-08  
**Status:** ✅ Production Ready
