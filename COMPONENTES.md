# 🏗️ Arquitetura de Componentes Reutilizáveis

## 📋 Visão Geral

Este projeto agora utiliza uma arquitetura baseada em **componentes reutilizáveis** para garantir:
- ✅ Consistência visual entre site público e admin
- ✅ Manutenção centralizada (uma mudança = atualiza tudo)
- ✅ Código DRY (Don't Repeat Yourself)
- ✅ Escalabilidade futura

---

## 🎯 Componentes Principais

### 1. **Navbar Universal** `components/navbar.php`

**Localização:** `/app/Views/components/navbar.php`

**Propósito:** Barra de navegação única para site público e admin

**Variáveis de Configuração:**
```php
$navbar_type = 'public';  // 'public' ou 'admin'
$show_sidebar_toggle = false;  // true em admin (mobile)
include 'components/navbar.php';
```

**Contextos de Uso:**
- **Site Público** (`header.php`): Menu com INÍCIO, SOBRE, BLOG, CONTATO
- **Admin Dashboard** (`dash.php`): Menu com ADMIN com botão "Sair"

**Recursos:**
- Navbar responsiva
- Hamburger menu em mobile
- Logo com scaling automático (40px → 46px)
- Overlay de sombra para mobile
- Cores tema CBMRN (laranja #df6301)

---

### 2. **Sidebar Admin** `components/sidebar.php`

**Localização:** `/app/Views/components/sidebar.php`

**Propósito:** Menu lateral do dashboard com seções colapsáveis

**Variáveis de Configuração:**
```php
$mainRoute = 'admin';  // Route principal ativa
$subRoute = 'dashboard';  // Sub-route ativa
include 'components/sidebar.php';
```

**Estrutura de Menu:**
- **Início** → Dashboard principal
- **Gestão de Conteúdo**
  - Posts
  - Categorias
- **Gestão de Pessoal**
  - Usuários
- **Documentação**
  - Estrutura, Virtual Host, Composer, Fluxos, Diagramas, etc.
- **Configurações**
  - Status, Versões, Info

**Recursos:**
- Seções colapsáveis (collapse)
- Indicador de rota ativa
- Gradient background escuro com laranja
- Responsive (collapse em mobile)

---

### 3. **Footer** `components/footer.php`

**Localização:** `/app/Views/components/footer.php`

**Propósito:** Rodapé reutilizável para site e admin

**Variáveis de Configuração:**
```php
$footer_type = 'public';  // 'public' ou 'admin'
include 'components/footer.php';
```

**Variantes:**

**Public Footer:**
- Seções: Sobre, Links Úteis, Contato
- Informações do projeto
- Copyright

**Admin Footer:**
- Minimal
- Copyright only

---

## 🎨 Estilos Centralizados

### Arquivo Principal: `navbar-universal.css`

**Localização:** `/public/assets/css/navbar-universal.css`

**O que controla:**
- Estilos de navbar (site + admin)
- Estilos do hamburger menu
- Overlay de sombra
- Responsividade (mobile, tablet, desktop)
- Cores e transições

**Variáveis CSS (Customizáveis):**
```css
:root {
  --cbmrn-orange: #df6301;
  --cbmrn-orange-dark: #b54f01;
  --cbmrn-white: #ffffff;
  --cbmrn-dark: #212529;
  --navbar-height: 56px;
}
```

**Breakpoints:**
```
@media (max-width: 768px) /* Tablet/Mobile */
@media (max-width: 576px) /* Mobile pequeno */
```

---

## 📁 Estrutura de Diretórios

```
app/Views/
├── components/                 ← Componentes reutilizáveis
│   ├── navbar.php            ✓ Navbar universal
│   ├── sidebar.php           ✓ Sidebar admin
│   └── footer.php            ✓ Footer
├── layouts/
│   ├── header.php            ← Início da página pública
│   ├── components/           ← Includes (symlink/ref para components)
│   └── footer.html           ← Footer HTML estático (legado)
├── home.php                  ✓ Com footer public
├── blog.php                  ✓ Com footer public
├── contact.php               ✓ Com footer public
├── about.php                 ✓ Com footer public
├── dash.php                  ✓ Com navbar admin + sidebar + footer admin
├── 404.php
├── post.php
└── ...

public/assets/css/
├── navbar-universal.css      ← Estilos centralizados (NOVO)
├── admin.css                 ← Overrides do admin
├── header.css                ← Overrides do site público
├── bombeiros-theme.css       ← Tema institucional
└── main.css                  ← Utilitários globais
```

---

## 🔄 Fluxo de Inclusão

### Site Público

```
1. index.php (public/index.php)
   ↓
2. Router → Encontra controller/view
   ↓
3. header.php (include 'components/navbar.php')
   ↓
4. View específica (home.php, blog.php, etc.)
   ↓
5. footer.php (include 'components/footer.php' com $footer_type='public')
```

### Admin Dashboard

```
1. index.php (public/index.php)
   ↓
2. Router → Admin routes
   ↓
3. dash.php (include 'components/navbar.php')
           (include 'components/sidebar.php')
   ↓
4. Conteúdo específico (renderizado em main-content)
   ↓
5. footer.php (include 'components/footer.php' com $footer_type='admin')
```

---

## 🛠️ Como Usar

### Modificar o Navbar (Site + Admin simultaneamente)

```bash
# Editar: /app/Views/components/navbar.php
# OU
# Editar: /public/assets/css/navbar-universal.css

# Uma mudança afeta AUTOMATICAMENTE:
# ✓ Site público (header.php)
# ✓ Admin dashboard (dash.php)
```

### Adicionar Item ao Menu do Admin

```php
// Em: /app/Views/components/sidebar.php
// Encontre a seção desejada e adicione:

<li class="nav-item">
    <a href="<?= BASE_URL ?>admin/nova-secao"
       class="nav-link text-white py-1 px-3 small <?= $subRoute === 'nova-secao' ? 'bg-secondary' : '' ?>">
        <i class="bi bi-icone-aqui me-1"></i> Nova Seção
    </a>
</li>
```

### Adicionar Nova Página Pública

```php
// Em: /app/Views/sua-pagina.php
// Adicione no FINAL:

<?php $footer_type = 'public'; include 'layouts/components/footer.php'; ?>
```

---

## 📊 Vantagens da Arquitetura

| Aspecto | Antes | Depois |
|--------|-------|--------|
| **Navbar duplicado** | 3 implementações | 1 componente |
| **CSS navbar** | 2 arquivos | 1 arquivo |
| **Manutenção** | Difícil (sync manual) | Automática (uma mudança) |
| **Footer** | Não existia | Componentizado |
| **Sidebar** | Hardcoded no dash | Componente reutilizável |
| **Consistência** | Inconsistente | Garantida |

---

## 🎯 Próximos Passos (Futuro)

- [ ] Criar componente para breadcrumbs
- [ ] Criar componente para pagination
- [ ] Criar componente para forms
- [ ] Criar componente para alerts/toasts
- [ ] Criar storybook para documentação visual

---

## 📝 Checklist de Integração

✅ Navbar component criado e testado
✅ Navbar CSS centralizado
✅ Header.php refatorado
✅ Dash.php refatorado (navbar + sidebar + footer)
✅ Footer component criado
✅ Todas as páginas públicas incluem footer
✅ Admin dashboard inclui sidebar como component
✅ Sem duplicação de código
✅ Responsivo em mobile
✅ CSRF e flash messages funcionando

---

## 📞 Suporte

Para questões sobre componentes, consulte:
- Este arquivo (`COMPONENTES.md`)
- `ARQUITETURA.md` (visão geral técnica)
- Código-fonte em `/app/Views/components/`

---

**Última atualização:** 2024  
**Status:** ✅ Componentes centralizados e funcionando
