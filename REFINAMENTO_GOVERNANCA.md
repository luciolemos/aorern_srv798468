# Refinamento de Governanca Tecnica (Semana 1-2)

## Mudancas aplicadas

1. Identidade e dominio
- `composer.json` padronizado para `luciolemos/aorern_srv798468`.
- `homepage` alinhada para `https://github.com/luciolemos/aorern_srv798468`.
- Exemplos de banco no `README.md`/`ARCHITECTURE.md` atualizados para `aorern_db`.

2. Base path e consistencia de rota admin
- Removidos aliases herdados `cbmrn/mvc` de:
  - `config/config.php`
  - `app/Middleware/AuthMiddleware.php`
  - `app/Middleware/PermissionMiddleware.php`
  - `app/Core/ExceptionHandler.php`
- A deteccao de rota admin agora considera `BASE_URL` de forma consistente.

3. Governanca de modulos legados
- Nova configuracao central: `app/Config/AdminGovernance.php`.
- Feature flag: `FEATURE_LEGACY_ADMIN_MODULES` (default `0`) em `.env.example`.
- `AdminHelper` passa a expor estado legada e itens permitidos por role.
- Sidebar admin mostra grupo `COMPATIBILIDADE LEGADA` somente quando a flag estiver ativa e o role tiver permissao.
- Alertas de modulo legado continuam visiveis ao acessar tela legada diretamente.

## Inventario de roteamento (baseline)

1. Roteamento em producao hoje
- Fluxo principal por convencao em `app/Core/App.php` (resolve controller/metodo por segmentos da URL).
- `app/Core/Router.php` existe como base declarativa, mas nao e o caminho principal no bootstrap atual.

2. Diretriz de migracao
- Manter `App.php` como fallback temporario.
- Introduzir rotas declarativas por grupos (`site`, `admin`) e migrar endpoints criticos primeiro:
  - autenticacao
  - dashboard
  - filiacoes
  - usuarios/roles

3. Proximo passo tecnico recomendado
- Criar `config/routes.php` com tabela inicial.
- Registrar `Router::dispatch()` no `public/index.php` antes do fallback para `App`.
- Medir cobertura com testes de integracao para login/redirecionamento por role.

## Padronizacao PT-BR (status)
1. Rotas declarativas principais em PT-BR para comunicacao/plataforma:
- `admin/publicacoes*`
- `admin/categorias-editoriais*`
- `admin/plataforma/*`

2. Compatibilidade sem quebra:
- aliases legados de `posts`, `post-categories`, `status` e `system/*` permanecem ativos.

3. Governanca de nomenclatura:
- plano consolidado em `PLANO_NOMENCLATURA_PTBR.md`, incluindo tabela de equivalencia novo/legado.
