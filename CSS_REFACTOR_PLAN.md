# Plano de Refatoração CSS (Baixo Risco)

## Objetivo
Reduzir complexidade, conflito entre folhas e custo de manutenção, mantendo o modelo híbrido:
- núcleo global para tokens/componentes compartilhados;
- CSS por página apenas para layout específico da view.

## Diagnóstico atual (15/05/2026)
- Layout público carrega base global em `layouts/base.twig`:
  - `system/variables.css`
  - `system/components.css`
  - `institutional-theme.css`
  - `navbar-universal.css`
  - `footer-robust.css`
  - `main.css`
  - `system/forms-unified.css`
- Views públicas carregam CSS por página (`home.css`, `about.css`, `contact.css`, etc).
- Há arquivos legados grandes com provável sobreposição ou desuso:
  - `nav.css` (677 linhas)
  - `footer.css` (58 linhas)
  - `header.css` (165 linhas)
  - `style.css` (57 linhas)
  - `bombeiros-theme.css` (386 linhas)
  - `new_admin.css` (0 linhas)

## Diretriz de arquitetura
1. `tokens`: variáveis e escala visual apenas em `system/variables.css`.
2. `base`: regras estruturais globais em `main.css`.
3. `componentes`: blocos reutilizáveis em `system/components.css`.
4. `layout compartilhado`: navbar/footer em arquivos próprios (`navbar-universal.css`, `footer-robust.css`).
5. `página`: CSS de view limitado a composição local (sem redefinir token global).

## Plano em fases

### Fase 1: Inventário e congelamento
1. Catalogar quais arquivos CSS são de fato carregados por Twig.
2. Marcar arquivos legados como `deprecated` em comentário no topo.
3. Bloquear criação de novos CSS globais fora de `system/*`.

Critério de aceite:
- Lista de CSS ativos e legados publicada no repositório.

### Fase 2: Limpeza segura de legados
1. Remover referência residual (se existir) para:
   - `nav.css`, `footer.css`, `header.css`, `style.css`, `bombeiros-theme.css`, `new_admin.css`.
2. Mover esses arquivos para `public/assets/css/_legacy/` por 1 ciclo de release.
3. Após validação em produção, remover definitivamente.

Critério de aceite:
- Nenhuma view/carregador referenciando arquivos `_legacy`.
- Sem regressão visual em Home, About, Contact, Blog, Gallery, Institucional.

### Fase 3: Componentização progressiva
1. Extrair padrões repetidos de páginas para `system/components.css`:
   - hero/kicker/section-header;
   - cards recorrentes;
   - padrões de CTA.
2. Manter prefixos por contexto (`home-*`, `about-*`) apenas no que for exclusivo.
3. Evitar `white-space: nowrap` e larguras fixas sem media query para mobile.

Critério de aceite:
- Redução visível de duplicação entre CSS de página.
- Novos componentes documentados com exemplo de uso.

### Fase 4: Governança de CSS
1. Adotar convenção de nomes (BEM/CUBE) e ordem de declaração.
2. Introduzir Stylelint com regras mínimas:
   - bloqueio de cores hardcoded fora de tokens;
   - bloqueio de `!important` sem justificativa;
   - limite de profundidade de seletores.
3. Criar checklist de PR para frontend.

Critério de aceite:
- Lint rodando local/CI.
- PRs de frontend seguindo checklist.

### Fase 5: Build e cache-busting
1. Substituir `?v=manual` por hash de build (Vite/Webpack).
2. Manter ordem de import controlada por entrypoint.

Critério de aceite:
- Versionamento automático de assets.
- Sem cache quebrado após deploy.

## Ordem recomendada de execução
1. Fase 1
2. Fase 2
3. Fase 3 (Home -> About -> Contact -> Blog -> Gallery -> Institucional)
4. Fase 4
5. Fase 5

## Riscos e mitigação
- Risco: regressão visual em páginas menos acessadas.
  - Mitigação: checklist de smoke test por rota e viewport (`360x800`, `768x1024`, `1366x768`).
- Risco: remoção de CSS legado ainda usado indiretamente.
  - Mitigação: etapa `_legacy/` antes da exclusão final.

## Entregável mínimo da próxima sprint
1. Publicar inventário final de CSS ativo x legado.
2. Migrar arquivos legados para `_legacy/`.
3. Extrair ao menos 2 componentes repetidos para `system/components.css`.
