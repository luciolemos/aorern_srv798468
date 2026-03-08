# 🏗️ ARQUITETURA: SITE + DASHBOARD

## PROBLEMA ATUAL
- **Site público** (`header.php`) e **Admin Dashboard** (`dash.php`) têm navbars duplicadas
- Mudanças em um não se propagam ao outro
- Navbars e sidebars com estilos espalhados em múltiplos arquivos

---

## SOLUÇÃO PROPOSTA: COMPONENTES CENTRALIZADOS

### 1️⃣ ESTRUTURA DE VIEWS

```
app/Views/
├── layouts/
│   ├── header.php          ← REMOVIDO (usar navbar.php)
│   ├── footer.php
│   ├── admin_header.php    ← REMOVIDO (usar navbar.php)
│   ├── admin_footer.php
│   ├── admin_sidebar.php   ← MANTER (é específico do admin)
│   └── main.php
│
├── components/
│   ├── navbar.php          ← NOVO: Navbar universal (público + admin)
│   ├── sidebar.php         ← NOVO: Sidebar reutilizável
│   └── footer.php          ← NOVO: Footer reutilizável
│
├── partials/
│   └── [específicas das páginas]
│
└── [páginas]
```

### 2️⃣ ESTRUTURA DE CSS

```
public/assets/css/
├── bootstrap.min.css       ← Framework base
├── bombeiros-theme.css     ← Cores institucionais
│
├── navbar-universal.css    ← NOVO: Navbar (público + admin)
├── sidebar-universal.css   ← NOVO: Sidebar admin
│
├── admin.css               ← Dashboard específico
├── header.css              ← Site público específico
├── main.css                ← Utilitários gerais
└── [outros]
```

### 3️⃣ INCLUSÃO EM LAYOUTS

**Site Público** (`main.php`):
```php
<?php 
$navbar_type = 'public';
include 'components/navbar.php';
?>
[conteúdo]
<?php include 'layouts/footer.php'; ?>
```

**Admin Dashboard** (`dash.php`):
```php
<?php 
$navbar_type = 'admin';
$show_sidebar_toggle = true;
include 'components/navbar.php';
?>
<!-- Sidebar -->
<?php include 'components/sidebar.php'; ?>
```

### 4️⃣ FLUXO DE CARREGAMENTO CSS

```
1. bootstrap.min.css          ← Base Bootstrap
2. bombeiros-theme.css        ← Cores institucionais
3. navbar-universal.css       ← Navbar (ambas)
4. admin.css / header.css      ← Específicos
5. main.css                    ← Utilitários
```

---

## BENEFÍCIOS

✅ **DRY** (Don't Repeat Yourself): Uma navbar para ambos os contextos
✅ **Manutenção**: Editar navbar em um lugar afeta ambas
✅ **Consistência**: Mesma altura, cores, responsive
✅ **Escalabilidade**: Componentes reutilizáveis
✅ **Performance**: CSS centralizado

---

## PRÓXIMOS PASSOS

1. Criar `components/navbar.php` ✅ FEITO
2. Criar `navbar-universal.css` ✅ FEITO
3. Atualizar `header.php` → usar componente
4. Atualizar `dash.php` → usar componente
5. Remover CSSs duplicados
6. Testar responsividade

---

## VARIÁVEIS COMPONENTIZADAS

### Navbar
- `$navbar_type`: 'public' | 'admin'
- `$logo_text`: Texto da marca
- `$show_sidebar_toggle`: true | false

### Sidebar
- `$sidebar_items`: Array de menus
- `$current_route`: Rota ativa
- `$is_admin`: true | false

### Footer
- `$footer_text`: Texto customizado
- `$show_credits`: true | false

---

## PADRÕES CSS

### Variáveis Globais
```css
--aorern-primary: #556b2f
--aorern-primary-dark: #3f4f22
--aorern-white: #ffffff
--aorern-dark: #212529
```

### Dimensões
- Navbar height: 56px
- Logo: 40px-46px (responsivo)
- Sidebar width: 260px

### Cores
- Navbar público: Laranja (#df6301)
- Navbar admin: Escuro (#212529)
- Texto: Branco (#ffffff)

---

## STATUS DE IMPLEMENTAÇÃO

- [x] Componente navbar.php criado
- [x] CSS navbar-universal.css criado
- [ ] Atualizar header.php
- [ ] Atualizar dash.php
- [ ] Remover CSSs duplicados
- [ ] Testar completo

