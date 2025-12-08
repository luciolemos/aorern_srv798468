# 📋 Fluxo Profissional de Posts - Documentação Completa

## 📊 Visão Geral do Workflow

O sistema implementa um fluxo de aprovação de posts em 5 status, com roles específicas e permissões granulares.

```
┌─────────────────────────────────────────────────────────────────────┐
│                         FLUXO DE POSTS                              │
└─────────────────────────────────────────────────────────────────────┘

┌──────────┐     ┌─────────┐     ┌──────────┐     ┌───────────┐
│  DRAFT   │────▶│ PENDING │────▶│PUBLISHED │────▶│HIDDEN (*)│
│Rascunho  │     │Revisão  │     │Publicado │     │ Ocultado  │
└──────────┘     └─────────┘     └──────────┘     └───────────┘
     △                │                                    │
     │                │                                    │
     │                ▼                                    │
     │           ┌─────────┐                              │
     │           │REJECTED │                              │
     └───────────│Rejeitado│◀─────────────────────────────┘
                 └─────────┘
                     ▲
                     │
              (usuario corrige)
```

---

## 🎭 Roles e Permissões

### 1. **USUARIO** (Autor)
- Cria posts (salvos como `draft`)
- Edita seus próprios rascunhos
- Submete para revisão (`draft` → `pending`)
- Recebe rejeições com motivo
- Edita posts rejeitados e resubmete

**Permissões:**
- `posts:create` ✅
- `posts:edit` (próprios rascunhos/rejeitados) ✅
- `posts:submit` (próprios drafts) ✅
- `posts:approve` ❌
- `posts:reject` ❌
- `posts:delete` ❌

---

### 2. **OPERADOR** (Visualizador)
- Visualiza posts pendentes e publicados
- Sem poder de decisão (apenas leitura no admin)

**Permissões:** Nenhuma ação de modificação

---

### 3. **GERENTE** (Revisor)
- Revisa posts pendentes
- Rejeita com motivo
- Vê apenas posts `pending` e `published` (não rascunhos privados)
- Edita posts em revisão

**Permissões:**
- `posts:review` ✅
- `posts:reject` ✅
- `posts:edit` (apenas pending) ✅
- `posts:approve` ❌ (não aprova, só rejeita)

---

### 4. **ADMIN** (Administrador)
- Aprovação final (publica posts)
- Edita qualquer post
- Oculta/exibe posts sem mudar status
- Deleta posts
- Rejeita posts (com motivo)

**Permissões:**
- `posts:create` ✅
- `posts:edit` (qualquer post) ✅
- `posts:submit` ✅
- `posts:review` ✅
- `posts:approve` ✅
- `posts:reject` ✅
- `posts:delete` ✅
- `posts:hide` ✅

---

## 🔄 Estados e Transições

### **DRAFT** (Rascunho)
- **Criado por:** Usuario ao criar/salvar rascunho
- **Visível para:** Apenas o autor no admin (via `paginarPorAutor()`)
- **Ações possíveis:**
  - ✏️ Editar (salvar como rascunho)
  - ➡️ Submeter para revisão (transição → `pending`)
  - 🗑️ Deletar (admin)
- **Visível no blog público?** ❌

---

### **PENDING** (Pendente de Revisão)
- **Criado por:** Usuario ao submeter ou gerente recusando e permitindo resubmissão
- **Visível para:** Admin e Gerente (via `paginarPorStatus(['pending', 'published'])`)
- **Ações possíveis:**
  - 👁️ Revisar (modal preview)
  - ✅ Aprovar/Publicar (admin)
  - ❌ Rejeitar (admin/gerente) + motivo obrigatório
  - ✏️ Editar (gerente)
- **Visível no blog público?** ❌

---

### **PUBLISHED** (Publicado)
- **Criado por:** Admin ao aprovar
- **Data:** Registra `published_at` automaticamente
- **Visível para:** Admin e Gerente no admin; **Todos** no blog (se `is_hidden=0`)
- **Ações possíveis:**
  - 👁️‍🗨️ Ocultar (admin) → marca `is_hidden=1` (não muda status)
  - ✏️ Editar/Atualizar (admin)
  - 🗑️ Deletar (admin)
  - ❌ Rejeitar (admin/gerente) → transição → `rejected`
- **Visível no blog público?** ✅ (se `is_hidden=0`)

---

### **REJECTED** (Rejeitado)
- **Criado por:** Admin ou Gerente ao rejeitar
- **Registro:** Salva motivo em `reject_reason`
- **Visível para:** Admin, Gerente, e **Usuario (autor)**
- **Ações possíveis:**
  - 👁️ Ver motivo (alert box)
  - ✏️ Editar (autor corrige)
  - ➡️ Submeter novamente (autor limpa status e resubmete)
  - 🗑️ Deletar (admin)
- **Visível no blog público?** ❌

---

### **HIDDEN** (Ocultado) ⭐
- **Flag:** `is_hidden = 1` (sem mudar `status`)
- **Status mantém:** `published`
- **Criado por:** Admin ao clicar "Ocultar do blog"
- **Visível para:** Admin/Gerente (com botão "Exibir no blog")
- **Ações possíveis:**
  - 👁️ Exibir (admin) → marca `is_hidden=0`
  - ✏️ Editar (admin)
  - 🗑️ Deletar (admin)
- **Visível no blog público?** ❌ (filtro `COALESCE(is_hidden,0)=0`)
- **Nota:** Status permanece `published`, mas não aparece no site

---

## 📱 Fluxo Passo-a-Passo por Usuário

### **Caso 1: Usuario cria e submete post**

```
1. Usuario clica "Novo Post" (admin/posts/create)
   └─ Preenche: Título, Slug, Categoria, Capa (opt), Conteúdo
   
2. Escolhe ação ao salvar:
   └─ "Salvar como Rascunho" → status = 'draft' (salvo, não enviado)
   └─ "Submeter para Revisão" → status = 'pending' (enviado para gerente/admin)
   
3. Post aparece em:
   └─ Admin: /admin/posts (apenas usuario vê seu rascunho)
   └─ Blog: NÃO aparece
   
4. Usuario edita rascunho:
   └─ Botões: "Salvar como Rascunho" ou "Submeter para Revisão"
   └─ Pode mudar de rascunho para pending sem rejeição
   
5. Gerente/Admin vê em /admin/posts:
   └─ Status "Pendente de Revisão"
   └─ Botões: "Revisar" (👁️), "Aprovar" (✅), "Rejeitar" (❌)
```

---

### **Caso 2: Admin aprova post**

```
1. Admin clica "Revisar" (modal popup)
   └─ Mostra: Título, Autor, Data, Preview conteúdo (500 chars)
   
2. Admin clica "Aprovar"
   └─ Transição: pending → published
   └─ Registra: published_at = agora()
   └─ Toast: "✅ Post publicado com sucesso!"
   
3. Post aparece em:
   └─ Admin: Status "Publicado", botões "Editar", "Ocultar"
   └─ Blog: Lista + página individual + navegação anterior/próximo
   
4. Admin pode:
   └─ Editar conteúdo/título → "Atualizar Publicado"
   └─ Ocultar do blog → "Ocultar do Blog" (is_hidden=1)
   └─ Deletar → remove completamente
   └─ Rejeitar → volta para rejected (causa surprise!)
```

---

### **Caso 3: Gerente/Admin rejeita post**

```
1. Clica "Rejeitar" (modal popup)
   └─ Abre formulário de rejeição
   
2. Preenche "Motivo da Rejeição" (obrigatório)
   └─ Ex: "Título muito genérico, reformule"
   
3. Clica "Confirmar Rejeição"
   └─ Transição: pending/published → rejected
   └─ Registra: reject_reason = motivo
   └─ Toast: "✅ Post rejeitado!"
   
4. Post aparece em:
   └─ Admin: Status "Rejeitado"
   └─ Usuario (autor): Visível, com alert mostrando motivo
   
5. Usuario pode:
   └─ Ver motivo (alert box)
   └─ Editar (botão "Reabrir e Corrigir")
   └─ Submeter novamente (após editar)
   
6. Ao reabrir e editar:
   └─ Motivo é LIMPO (reject_reason = null)
   └─ Usuario escolhe: "Salvar Rascunho" ou "Submeter para Revisão"
   └─ Se submeter: volta para pending (gerente/admin revisam novamente)
```

---

### **Caso 4: Admin oculta post publicado**

```
1. Admin vê post publicado em /admin/posts
   └─ Botão "Ocultar do Blog" (👁️‍🗨️)
   
2. Confirma ocultação
   └─ Flag: is_hidden = 1 (status continua = published)
   └─ Toast: "👁️ Post ocultado do blog (continua publicado)."
   
3. Post fica:
   └─ Admin: Visível com botão "Exibir no Blog"
   └─ Blog: SUMIU (filtro is_hidden=0)
   
4. Usuario (autor): NÃO vê mais em seu painel
   
5. Admin pode reexibir:
   └─ Clica "Exibir no Blog"
   └─ Flag: is_hidden = 0
   └─ Post volta ao blog imediatamente
```

---

## 🔍 Filtros no Admin

| Role | Ver em /admin/posts | Fonte SQL |
|------|-------------------|-----------|
| **usuario** | Próprios posts (todos status) | `paginarPorAutor($userId)` |
| **operador** | Pendentes + Publicados | `paginarPorStatus(['pending','published'])` |
| **gerente** | Pendentes + Publicados | `paginarPorStatus(['pending','published'])` |
| **admin** | Pendentes + Publicados | `paginarPorStatus(['pending','published'])` |

**Nota:** Rascunhos privados do usuario NÃO aparecem para admin/gerente.

---

## 🌐 Filtros no Blog Público

```sql
-- listarPublico() retorna:
SELECT * FROM posts 
WHERE status = 'published' 
  AND COALESCE(is_hidden, 0) = 0  -- ← Posts ocultos NÃO aparecem
ORDER BY criado_em DESC
```

**Queries afetadas:**
- `listarPublico()` - Lista de posts com busca/categoria
- `encontrarPorSlug()` - Página individual
- `encontrarAnterior()` - Link "Post Anterior"
- `encontrarProximo()` - Link "Próximo Post"

---

## 📝 Campos da Tabela `posts`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | Identificador único |
| `titulo` | VARCHAR(200) | Título do post |
| `slug` | VARCHAR(200) | URL-friendly (único) |
| `conteudo` | LONGTEXT | Conteúdo (Quill.js) |
| `user_id` | INT (FK) | Autor (usuario que criou) |
| `categoria_id` | INT (FK) | Categoria do post |
| `capa_url` | VARCHAR(512) | URL da imagem de capa |
| `status` | ENUM | `draft`, `pending`, `published`, `rejected` |
| `reject_reason` | TEXT | Motivo da rejeição (se rejected) |
| `published_at` | DATETIME | Data/hora de publicação (quando status→published) |
| `is_hidden` | TINYINT(1) | Flag para ocultar do blog (0=visível, 1=oculto) |
| `criado_em` | DATETIME | Timestamp criação |
| `atualizado_em` | DATETIME | Timestamp última atualização |
| `autor` | VARCHAR(100) | Nome do autor (snap) |

---

## 🔐 Checklist de Segurança

- ✅ **Acesso a nivel de query:** Posts privados (draft) não aparecem para admin
- ✅ **CSRF protection:** Todos formulários com token (`CsrfHelper`)
- ✅ **Permissões middleware:** `PermissionMiddleware::authorize()` em cada ação
- ✅ **Propriedade:** Usuarios só editam seus próprios posts (exceto admin)
- ✅ **Status validation:** Transições permitidas apenas para status válidos
- ✅ **Blog público:** Apenas `published` e `is_hidden=0` visíveis

---

## 🎨 UI/UX - Botões por Contexto

### **Lista Admin (/admin/posts)**

| Status | Usuario | Gerente/Admin | Botões |
|--------|---------|---------------|--------|
| `draft` | ✅ vê | ❌ não vê | Editar, Submeter |
| `pending` | ❌ não vê | ✅ vê | Revisar, Aprovar*, Rejeitar |
| `published` | ❌ não vê | ✅ vê | Editar, Ocultar/Exibir, Deletar* |
| `rejected` | ✅ vê | ✅ vê | Editar, Submeter |

\* `Aprovar` apenas admin, `Deletar` apenas admin

---

### **Formulário Edição (/admin/posts/edit/{id})**

| Status | Botões |
|--------|--------|
| `draft` | "Salvar Rascunho", "Submeter para Revisão" |
| `pending` | "Atualizar" |
| `published` | "Atualizar Publicado", "Ocultar/Exibir do Blog" |
| `rejected` | "Salvar Rascunho", "Submeter para Revisão" |

---

## 🚀 Endpoints Controller

```
POST   /admin/posts/create          - Exibe formulário novo post
POST   /admin/posts/store           - Salva novo post
GET    /admin/posts/edit/{id}       - Exibe formulário edição
POST   /admin/posts/update/{id}     - Salva edição
POST   /admin/posts/submit/{id}     - Submete rascunho (legado)
POST   /admin/posts/approve/{id}    - Aprova (pending→published)
POST   /admin/posts/reject/{id}     - Rejeita (qualquer→rejected)
POST   /admin/posts/hide/{id}       - Oculta (published, is_hidden=1)
POST   /admin/posts/show/{id}       - Exibe (published, is_hidden=0)
POST   /admin/posts/unpublish/{id}  - Despublica (published→draft) [REMOVIDO]
POST   /admin/posts/delete/{id}     - Deleta post
GET    /admin/posts                 - Lista filtrada por role
```

---

## 📚 Exemplo Real

### **Cenário: Fernando (usuario) escreve post sobre Docker**

**Dia 1 - Fernando cria:**
```
1. Clica "Novo Post"
2. Preenche: Titulo="Docker 101", Slug="docker-101", Categoria="DevOps"
3. Clica "Salvar como Rascunho"
   └─ status='draft', user_id=fernando_id
4. Aparece em /admin/posts (apenas para Fernando)
```

**Dia 2 - Fernando edita e submete:**
```
1. Clica "Editar" no seu rascunho
2. Muda título para "Guia Completo Docker"
3. Clica "Submeter para Revisão"
   └─ status='pending', reject_reason=NULL
4. Toast: "✅ Post submetido para revisão!"
5. Desaparece da lista de Fernando, aparece em lista de Gerente/Admin
```

**Dia 3 - Gerente (Maria) revisa:**
```
1. Vê "Docker 101" com status "Pendente de Revisão"
2. Clica "Revisar" (modal com preview)
3. Lê conteúdo, acha problema
4. Clica "Rejeitar"
5. Escreve motivo: "Imagens muito antigas, atualize para Docker 2024"
6. Toast: "✅ Post rejeitado!"
```

**Dia 4 - Fernando corrige:**
```
1. Vê post rejeitado em /admin/posts
2. Alert mostra: "Motivo: Imagens muito antigas..."
3. Clica "Reabrir e Corrigir" (editar)
4. Atualiza screenshots e conteúdo
5. Clica "Submeter para Revisão" novamente
   └─ reject_reason é LIMPO
   └─ status='pending' de novo
6. Volta para fila de Gerente/Admin
```

**Dia 5 - Admin (Luciolemos) aprova:**
```
1. Vê "Docker 101" (revisado) em status "Pendente"
2. Clica "Revisar" - satisfeito com mudanças
3. Clica "Aprovar"
   └─ status='published', published_at='2025-12-05 10:30:00'
   └─ Toast: "✅ Post publicado!"
4. Post APARECE no blog public (/blog)
5. Fernando vê em /admin/posts com status "Publicado"
```

**Dia 10 - Admin resolve ocultar:**
```
1. Admin descobre que Docker 3.0 foi lançado
2. Quer dar tempo para Fernando atualizar
3. Clica "Ocultar do Blog"
   └─ is_hidden=1 (status continua 'published')
   └─ Toast: "👁️ Post ocultado do blog"
4. Post SOME do /blog (filtro is_hidden=0)
5. Admin vê "Exibir no Blog" (para trazer de volta depois)
```

**Dia 15 - Admin reexibe:**
```
1. Fernando atualizou (email manual)
2. Admin clica "Exibir no Blog"
   └─ is_hidden=0
3. Post VOLTA ao /blog imediatamente
```

---

## 💡 Notas Importantes

1. **Status != Visibilidade**: Um post pode ser `published` mas `is_hidden=1` (oculto)
2. **Motivo de rejeição**: Obrigatório, aparece em alert para usuario
3. **Usuario não vê rascunhos de outros**: Filtro `paginarPorAutor($userId)`
4. **Admin não vê rascunhos privados**: Filtro `paginarPorStatus(['pending','published'])`
5. **Blog público:** Filtro `status='published' AND is_hidden=0`
6. **Navegação anterior/próximo**: Também respeita `is_hidden`
7. **Limpar reject_reason:** Feito automaticamente ao submeter novamente

---

## 🔗 Arquivos Principais

```
app/
├── Controllers/Admin/PostsController.php    - Lógica workflow
├── Models/Post.php                          - Queries filtradas
├── Config/Permissions.php                   - ACL com 50+ rules
├── Views/templates/admin/posts/
│   ├── index.twig                           - Lista com modais
│   ├── create.twig                          - Form novo post
│   └── edit.twig                            - Form edição
├── Views/templates/site/pages/
│   ├── blog.twig                            - Lista pública
│   └── post.twig                            - Post individual
└── Helpers/
    ├── CsrfHelper.php                       - Token CSRF
    └── PermissionMiddleware.php             - Autorização
```

---

**Última atualização:** 2025-12-08  
**Versão:** 1.0  
**Status:** ✅ Completo e testado
