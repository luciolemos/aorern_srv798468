# 💡 Exemplos de Uso - Componentes Reutilizáveis

## 1️⃣ Usando o Navbar (Site Público)

### Em `/app/Views/layouts/header.php`

```php
<?php
$current = $_GET['url'] ?? 'home';
$segments = explode('/', $current);
$mainRoute = $segments[0] ?? 'home';
$subRoute  = $segments[1] ?? null;

// Importa navbar centralizada (site público)
$navbar_type = 'public';
include 'components/navbar.php';
?>
```

**Resultado:**
- Menu com: INÍCIO, SOBRE, BLOG, CONTATO
- Logo AORE/RN
- Responsivo em mobile
- Laranja (#df6301)

---

## 2️⃣ Usando o Navbar + Sidebar (Admin)

### Em `/app/Views/dash.php`

```php
<?php
// Navbar Admin
$navbar_type = 'admin';
$show_sidebar_toggle = true; // Mostra hamburger em mobile
include 'layouts/components/navbar.php';
?>

<div class="admin-layout d-flex">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <?php
    // Sidebar Admin
    include 'layouts/components/sidebar.php';
    ?>
    
    <!-- Conteúdo principal aqui -->
</div>
```

**Resultado:**
- Navbar dark com botão "Sair"
- Sidebar com menu colapsável
- Hamburger em mobile
- Layout flexbox responsivo

---

## 3️⃣ Usando o Footer (Site Público)

### Em `/app/Views/home.php` (Final da página)

```php
</div>

<?php $footer_type = 'public'; include 'layouts/components/footer.php'; ?>
```

**Resultado:**
- 3 colunas: Sobre, Links, Contato
- Informações AORE/RN
- Copyright
- Links rápidos

---

## 4️⃣ Usando o Footer (Admin)

### Em `/app/Views/dash.php` (Final da página)

```php
<?php $footer_type = 'admin'; include 'layouts/components/footer.php'; ?>

</body>
</html>
```

**Resultado:**
- Footer minimalista
- Copyright
- Link desenvolvedor
- Escuro e limpo

---

## 5️⃣ Adicionando Item ao Menu da Sidebar

### Em `/app/Views/components/sidebar.php`

**Encontre a seção desejada:**

```php
<!-- GESTÃO DE CONTEÚDO -->
<li class="nav-item">
    <a class="nav-link text-white py-2 px-3 d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse" href="#gestaoCollapse" role="button">
        <span><i class="bi bi-folder me-2"></i> Gestão de Conteúdo</span>
        <i class="bi bi-chevron-down small"></i>
    </a>
    <div class="collapse" id="gestaoCollapse">
        <ul class="nav flex-column ms-3">
            <!-- ADICIONE AQUI -->
            <li class="nav-item">
                <a href="<?= BASE_URL ?>admin/novo-item"
                   class="nav-link text-white py-1 px-3 small <?= $subRoute === 'novo-item' ? 'bg-secondary' : '' ?>">
                    <i class="bi bi-star me-1"></i> Novo Item
                </a>
            </li>
        </ul>
    </div>
</li>
```

---

## 6️⃣ Criando Nova Página Pública com Footer

### Arquivo: `/app/Views/news.php`

```php
<!-- Início do header.php -->

<div class="container my-5">
    <h1>Notícias Importantes</h1>
    
    <p>Conteúdo da página aqui...</p>
</div>

<!-- Incluir footer automaticamente -->
<?php $footer_type = 'public'; include 'layouts/components/footer.php'; ?>
```

---

## 7️⃣ Modificando Cores do Navbar

### Em `/public/assets/css/navbar-universal.css`

```css
:root {
  --aorern-primary: #556b2f;      /* Altere aqui */
  --aorern-primary-dark: #3f4f22; /* Altere aqui */
  --aorern-white: #ffffff;
  --aorern-dark: #212529;
  --navbar-height: 56px;
}

/* Afeta automaticamente site + admin */
```

---

## 8️⃣ Ajustando Responsividade da Navbar

### Em `/public/assets/css/navbar-universal.css`

```css
/* Mudar altura do navbar */
.navbar {
  height: var(--navbar-height); /* Mude --navbar-height em :root */
}

/* Mudar tamanho do logo */
.navbar-logo {
  width: 40px;  /* Desktop */
  height: 40px;
}

@media (max-width: 576px) {
  .navbar-logo {
    width: 46px; /* Mobile */
    height: 46px;
  }
}
```

---

## 9️⃣ Controlando Rota Ativa na Sidebar

### Em qualquer controller do admin

```php
<?php
// AdminDashboardController.php

class AdminDashboardController extends Controller {
    public function index() {
        // Variáveis disponíveis na view
        $mainRoute = 'admin';
        $subRoute = 'dashboard';
        
        return $this->view('dash', [
            'mainRoute' => $mainRoute,
            'subRoute' => $subRoute
        ]);
    }
}
```

**Na view (dash.php):**
```php
<?php
// Variables passadas do controller
$mainRoute = $mainRoute ?? 'admin';
$subRoute = $subRoute ?? 'dashboard';

// Include components
include 'layouts/components/navbar.php';
include 'layouts/components/sidebar.php';
?>
```

---

## 🔟 Estrutura Recomendada para Nova Feature

Se quiser adicionar um novo componente:

### 1. Criar o componente

**Arquivo:** `/app/Views/components/meu-componente.php`

```php
<?php
/**
 * COMPONENTE: Meu Componente
 * 
 * Uso:
 * <?php
 *   $titulo = 'Exemplo';
 *   $items = ['A', 'B', 'C'];
 *   include 'components/meu-componente.php';
 * ?>
 */

$titulo = $titulo ?? 'Padrão';
$items = $items ?? [];
?>

<div class="meu-componente">
    <h3><?= $titulo ?></h3>
    <ul>
        <?php foreach ($items as $item): ?>
            <li><?= htmlspecialchars($item) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
```

### 2. Usar em qualquer view

```php
<?php
$titulo = 'Minha Lista';
$items = ['Item 1', 'Item 2', 'Item 3'];
include 'components/meu-componente.php';
?>
```

---

## 🎯 Dicas e Boas Práticas

### ✅ Faça
```php
// 1. Use componentes para evitar duplicação
include 'components/navbar.php';

// 2. Configure variáveis antes de incluir
$footer_type = 'public';
include 'components/footer.php';

// 3. Use HTML semanticamente
<footer class="bg-dark">...</footer>

// 4. Comente as variáveis esperadas
// Espera: $mainRoute, $subRoute
include 'components/sidebar.php';
```

### ❌ Evite
```php
// 1. Duplicar HTML
// ❌ Não faça navbar em header.php E em dash.php

// 2. Colocar lógica complexa em componentes
// Componentes devem ser simples

// 3. Usar variáveis globais
// Use passar como função/include

// 4. Esquecer de configurar variáveis
// Sempre set antes de include
```

---

## 📊 Padrão de Uso

### Site Público
```
header.php (navbar)
  → página.php (conteúdo)
footer.php (footer)
```

### Admin
```
dash.php (navbar + sidebar)
  → conteúdo dinâmico
footer.php (footer admin)
```

---

## 🔗 Referências

Para mais informações:
- **Componentes Detalhado:** `COMPONENTES.md`
- **Arquitetura Completa:** `ARQUITETURA.md`
- **Refactoring Summary:** `REFACTORING_SUMMARY.md`

---

**Última atualização:** 2024  
**Exemplos testados:** ✅ Sim
