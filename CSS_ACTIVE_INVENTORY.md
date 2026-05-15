# Inventário de CSS Ativo x Legado (Fases 1 e 2)

Data: 2026-05-15
Escopo: referências diretas em `app/Views/**/*.twig`.

## Ativos (referenciados)
- `admin-error-pages.css`
- `admin.css`
- `admin-dashboard.css`
- `about.css`
- `associado.css`
- `auth.css`
- `blog.css`
- `contact.css`
- `error-pages.css`
- `esquadrao.css`
- `footer-robust.css`
- `gallery.css`
- `home.css`
- `institutional-theme.css`
- `institutional.css`
- `main.css`
- `navbar-universal.css`
- `system/components.css`
- `system/forms-unified.css`
- `system/variables.css`

## Legados migrados para `_legacy/` (Fase 2)
- `bombeiros-theme.css`
- `footer.css`
- `header.css`
- `nav.css`
- `new_admin.css`
- `style.css`

## Observações
- Arquivos legados movidos para `public/assets/css/_legacy/` em 2026-05-15.
- Nenhuma referência direta encontrada em `app/Views/**/*.twig` após a migração.
- Antes da remoção definitiva, validar possíveis usos indiretos (docs, testes manuais, snippets externos).
