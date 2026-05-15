# Guia CSS AORE/RN (Tokenização e Governança)

Este guia define o padrão para criação de `hero`, `card`, `botão` e `form` sem hardcode de cor.

## 1) Fonte única de tokens

- Arquivo oficial: `public/assets/css/system/variables.css`
- Arquivo oficial de componentes-base: `public/assets/css/system/components.css`
- Não criar novos tokens em folhas de página (`home.css`, `contact.css`, etc.).
- Não recriar arquivo legado de navegação: `nav.css` foi descontinuado.

## 2) Regras obrigatórias

- Cores:
  - Use tokens (`var(--color-*)`, `var(--site-*)`, `var(--navbar-*)`).
  - Proibido hardcode (`#fff`, `#556b2f`, etc.) em componentes novos.
- Espaçamento:
  - Use escala (`--space-1` até `--space-8`) e tokens já existentes de botão.
- Borda/radius:
  - Use `--radius-sm|md|lg|pill` e `--button-radius-*`.
- Tipografia:
  - Use `--font-body`, `--font-heading` e tokens de cor de texto (`--site-text-*`).

## 3) Padrões por componente

- Hero:
  - Base: `.site-hero` (em `components.css`).
  - Background: `--site-hero-background`.
  - Cor de texto: `--color-white`.
- Card:
  - Base recomendada: `.feature-card` ou `.card-elevated`.
  - Fundo: `--surface-elevated`/`--color-white`.
  - Borda/sombra: `--site-card-border` e `--site-card-shadow` (ou `--shadow-soft`).
- Botão:
  - Use `.btn-theme`, `.btn-theme-outline`, `.btn-theme-muted`, `.btn-theme-surface`.
  - Não criar gradiente local de botão fora de tokens globais.
- Formulário:
  - Use `forms-unified.css` + tokens globais.
  - Placeholder/foreground devem usar contraste e tokens de texto.

## 4) Processo para novas cores

1. Adicionar token em `variables.css` com nome semântico.
2. Aplicar token no componente-base (`components.css` ou `navbar-universal.css`).
3. Só depois consumir o componente na página específica.

## 5) Checklist rápido antes de merge

- `rg -n "#[0-9a-fA-F]{3,8}\\b" public/assets/css` sem novos hardcodes no escopo alterado.
- Nenhum novo token local em CSS de página.
- Hero/card/botão/form do escopo usando classes/tokens globais.
