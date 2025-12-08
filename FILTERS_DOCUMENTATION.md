# 🔍 Sistema de Filtros Avançados - Documentação

## Visão Geral

Implementação de um painel profissional de filtros avançados na página de gerenciamento de posts (Admin). O sistema permite filtrar por múltiplos critérios simultaneamente com suporte a combinações.

---

## 📋 Funcionalidades

### 1. **Busca por Título/Conteúdo** 🔎
- Campo: `q` (query)
- Comportamento: Busca em **título** e **conteúdo** do post
- Placeholder: "Título ou conteúdo..."
- Aplicado: Sempre (sem restrições de role)

### 2. **Filtro por Status** 📊
- Campo: `status`
- Opções:
  - `""` - Todos os Status (padrão)
  - `draft` - Rascunho
  - `pending` - Pendente de Revisão
  - `published` - Publicado
  - `rejected` - Rejeitado
- Aplicado: Sempre (sem restrições de role)
- Nota: Para usuários regulares, apenas seus próprios posts de qualquer status são vistos

### 3. **Filtro por Categoria** 🏷️
- Campo: `category`
- Comportamento: Lista dinâmica de categorias do banco
- Carregado: `POST::listar()` via `PostCategoryModel`
- Aplicado: Sempre (sem restrições de role)

### 4. **Filtro por Autor** 👤
- Campo: `author`
- Comportamento: Lista dinâmica de autores com posts
- Carregado: `POST::listarAutoresUnicos()` via query JOIN
- **Restrição**: Apenas visível para `admin` e `gerente` (não para `usuario`)
- Motivo: Usuários regulares veem apenas seus próprios posts

### 5. **Filtro por Visibilidade** 👁️
- Campo: `visibility`
- Opções:
  - `""` - Todos
  - `visible` - 👁️ Visível (no blog)
  - `hidden` - 👁️‍🗨️ Oculto (não aparece no blog)
- Comportamento: 
  - Filtra coluna `is_hidden` da tabela `posts`
  - `visible` = `is_hidden = 0`
  - `hidden` = `is_hidden = 1`
- Nota: Apenas filtra posts com `status = 'published'`

### 6. **Botão "Limpar Filtros"** ❌
- Aparece: Quando há qualquer filtro ativo
- Ação: Redireciona para `/admin/posts` (sem parâmetros)
- Ícone: `bi-arrow-counterclockwise` (refresh)

---

## 🔧 Arquitetura Técnica

### Backend (PHP)

#### **Controller: `PostsController::index()`**
```php
public function index(): void {
    // Captura parâmetros GET
    $q = $request->query('q', '');           // Busca
    $status = $request->query('status', '');  // Status
    $category = $request->query('category', '');  // Categoria
    $author = $request->query('author', '');      // Autor
    $visibility = $request->query('visibility', '');  // Visibilidade
    
    // Rota lógica baseada em role
    if ($userRole === 'usuario') {
        // Usuários: apenas seus próprios posts com filtros
        $result = $this->post->paginarPorAutor(
            $userId, $page, $perPage, 
            $q, $status, $category, $visibility
        );
    } else {
        // Admin/Gerente: todos os posts com filtros completos
        $result = $this->post->paginarComFiltros(
            $page, $perPage,
            $q, $status, $category, $author, $visibility
        );
    }
    
    // Retorna para template com dados de filtro
}
```

#### **Model: `Post::paginarPorAutor()` (Refatorado)**
```php
public function paginarPorAutor(
    ?int $userId, 
    int $page = 1, 
    int $perPage = 10, 
    ?string $busca = null,
    ?string $status = null,        // NOVO
    ?string $category = null,       // NOVO
    ?string $visibility = null      // NOVO
): array
```

Aplica filtros:
- `$userId` - Posts do usuário
- `$busca` - Título/conteúdo
- `$status` - Status específico
- `$category` - Categoria específica
- `$visibility` - Visibilidade (hidden/visible)

#### **Model: `Post::paginarComFiltros()` (NOVO)**
```php
public function paginarComFiltros(
    int $page = 1,
    int $perPage = 10,
    ?string $busca = null,
    ?string $status = null,
    ?string $category = null,
    ?string $author = null,
    ?string $visibility = null
): array
```

Aplica filtros para admin/gerente:
- `$busca` - Título/conteúdo
- `$status` - Status específico (padrão: `pending` e `published`)
- `$category` - Categoria específica
- `$author` - Filtro por user_id
- `$visibility` - Visibilidade (hidden/visible)

#### **Model: `Post::listarAutoresUnicos()` (NOVO)**
```php
public function listarAutoresUnicos(): array {
    // Retorna [user_id, name] de todos os autores com posts
}
```

Query SQL:
```sql
SELECT DISTINCT p.user_id, u.name 
FROM posts p
LEFT JOIN users u ON u.id = p.user_id
WHERE p.user_id IS NOT NULL
ORDER BY u.name ASC
```

### Frontend (Twig)

#### **Template: `admin/posts/index.twig`**

Painel de filtros em card com estrutura:
```
┌─ Filtros Avançados (header) ─────────────────────────┐
│  [Limpar Filtros] (se há filtros ativos)            │
├──────────────────────────────────────────────────────┤
│  Row 1: [Busca] [Status] [Categoria] [Autor*]        │
│  Row 2: [Visibilidade] [Aplicar Filtros] btn         │
└──────────────────────────────────────────────────────┘
* Autor: apenas para admin/gerente (condicional Twig)
```

Variáveis Twig recebidas:
- `q` - Valor de busca atual
- `status` - Status selecionado
- `category` - Categoria selecionada
- `author` - Autor selecionado
- `visibility` - Visibilidade selecionada
- `categories` - Array de todas as categorias
- `allAuthors` - Array de autores únicos
- `userRole` - Role do usuário (para condicional)

---

## 🔄 Fluxo de Funcionamento

### Exemplo 1: Usuário Regular (usuario)
```
1. Acessa: GET /admin/posts
2. Controller detecta: role === 'usuario'
3. Chama: paginarPorAutor($userId, ..., $category, $visibility)
4. Query: WHERE user_id = ? AND ... AND is_hidden FILTER
5. Retorna: Apenas posts do usuário (qualquer status)
6. Template: Mostra filtros (SEM autor, pois já está filtrado)
```

### Exemplo 2: Admin com Múltiplos Filtros
```
1. Acessa: GET /admin/posts?status=published&visibility=hidden&category=2
2. Controller detecta: role === 'admin'
3. Chama: paginarComFiltros(..., 'published', 2, null, 'hidden')
4. Query: WHERE status='published' AND categoria_id=2 AND is_hidden=1
5. Retorna: Posts publicados, ocultos, da categoria 2
6. Template: Mostra botão "Limpar Filtros" em vermelho
```

### Exemplo 3: Busca + Filtros
```
1. Acessa: GET /admin/posts?q=tutorial&status=draft
2. Aplica: WHERE titulo LIKE '%tutorial%' AND status='draft'
3. Retorna: Rascunhos com "tutorial" no título
```

---

## 📊 Estrutura de URLs

### Padrão
```
GET /admin/posts?q=BUSCA&status=STATUS&category=CAT&author=USER&visibility=VIS&page=NUM
```

### Exemplos
```
# Todos os posts
/admin/posts

# Busca simples
/admin/posts?q=tutorial

# Posts publicados e ocultos
/admin/posts?status=published&visibility=hidden

# Posts de uma categoria específica
/admin/posts?category=5

# Posts rejeitados do autor ID=3
/admin/posts?status=rejected&author=3

# Combinar múltiplos
/admin/posts?q=guia&status=pending&category=2&author=3
```

### Limpeza
```
# Botão "Limpar Filtros" redireciona para:
/admin/posts
```

---

## 🎨 UI/UX

### Painel de Filtros
- **Estilo**: Card com border-0 e shadow-sm
- **Header**: Bg-light com título + ícone funnel + botão limpar
- **Grid**: 4 colunas em desktop (col-lg-3), 2 em tablet (col-md-6), 1 mobile (col-12)
- **Espaço**: gap-3 entre elementos
- **Responsividade**: 
  - Desktop: 4 selects em 1 linha
  - Tablet: 2 selects por linha
  - Mobile: 1 select por linha

### Campos
- **Busca**: Input com placeholder descritivo
- **Selects**: Opção vazia como padrão ("Todos os...") + valores do banco
- **Botão**: "Aplicar Filtros" com ícone search

### Condicionalidades
- Autor: Oculto para `usuario`
- Limpar: Visível apenas com filtros ativos
- Visibilidade: Funciona com qualquer status (SQL filtra apenas published)

---

## 🔐 Segurança

### Controle de Acesso
1. **Middleware**: `PermissionMiddleware::authorize('posts:index')`
2. **Filtros de Query**: Aplicados no nível SQL (não UI)
3. **Autor**: Admin/Gerente podem filtrar, usuários não (condicional + SQL)

### SQL Injection Prevention
- Todas as queries usam prepared statements com named parameters
- Valores de filtro escapados no SQL (LIKE, IN, =)

### CSRF Protection
- Form usa método GET (sem CSRF necessário)
- Dados sensíveis não são postes via form

---

## 🧪 Testes

### Testes Manuais
- [ ] Buscar por termo que existe
- [ ] Buscar por termo que não existe
- [ ] Filtrar por cada status
- [ ] Filtrar por cada categoria
- [ ] Filtrar por autor (admin apenas)
- [ ] Filtrar por visibilidade (hidden/visible)
- [ ] Combinar múltiplos filtros
- [ ] Limpar filtros
- [ ] Verificar paginação com filtros
- [ ] Testar responsividade mobile

### Testes de Acesso
- [ ] Usuário vê filtro de autor? (Não)
- [ ] Usuário vê apenas seus posts? (Sim)
- [ ] Admin vê filtro de autor? (Sim)
- [ ] Admin vê posts de outros usuários? (Sim)

---

## 📝 Notas de Desenvolvimento

### Mudanças Principais
1. `PostsController::index()` - Adicionado captura de 5 parâmetros de filtro
2. `Post::paginarPorAutor()` - Refatorado para aceitar 3 filtros adicionais
3. `Post::paginarComFiltros()` - **NOVO** método com suporte completo a filtros
4. `Post::listarAutoresUnicos()` - **NOVO** método para popular dropdown
5. `admin/posts/index.twig` - Substituído formulário simples por painel avançado

### Otimizações SQL
- Lazy loading: Categorias e autores carregados via controller
- JOIN com users: Apenas quando necessário (admin/gerente)
- Named parameters: Evita confusão com múltiplos placeholders

### Melhorias Futuras
- [ ] Filtro por data (criado_em range)
- [ ] Filtro por usuário que criou vs publicado
- [ ] Salvar filtros favoritos
- [ ] Exportar resultados (CSV/PDF)
- [ ] Busca avançada com operadores (AND, OR, NOT)
- [ ] Autocomplete no campo de busca
- [ ] Busca por tag/label

---

## 📞 Suporte

Qualquer dúvida ou issue:
- Verificar logs em `/coverage/` ou `/logs/`
- Testar queries SQL diretamente no DB
- Validar campos de formulário com DevTools
