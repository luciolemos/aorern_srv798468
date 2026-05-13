# Checklist de Deploy Hostinger (AORE/RN)

## 1) Preparacao
1. Confirmar release/branch e executar localmente:
   - `make preflight`
   - `make quality BASE_URL=<URL_ALVO>`
2. Garantir backup:
   - banco de dados
   - `public/uploads/`
3. Confirmar `.env` do alvo.

## 2) Configuracao de ambiente (`.env`)

### Desenvolvimento atual (subdiretorio)
Use quando o sistema estiver em `https://srv798468.hstgr.cloud/aorern/`:
```env
APP_ENV=production
APP_URL=https://srv798468.hstgr.cloud/aorern
```

### Producao final (dominio raiz)
Use quando o sistema estiver em `https://aorern.org/`:
```env
APP_ENV=production
APP_URL=https://aorern.org
```

Observacao:
1. `APP_URL` deve ser explicito no Hostinger para evitar inconsistencias de base path.
2. Nao usar barra final (o sistema normaliza internamente).

## 3) Publicacao
```bash
git pull
composer install --no-dev --optimize-autoloader
php migrate migrate
```

## 4) Permissoes
1. Verificar escrita em:
   - `public/uploads/`
   - `logs/`
2. Confirmar que upload de avatar/documentos funciona no painel.

## 5) Validacao pos-deploy
1. Lint PHP:
```bash
find app -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```
2. Testes unitarios:
```bash
make test-unit
```
3. Smoke de filiacao (ajuste URL conforme ambiente):
```bash
bash scripts/smoke_membership_status_flow.sh https://srv798468.hstgr.cloud/aorern
# ou
bash scripts/smoke_membership_status_flow.sh https://aorern.org
```
4. Verificar manualmente:
   - login (`/login/admin`)
   - painel (`/admin/dashboard`)
   - publicacoes (`/admin/publicacoes`)
   - categorias editoriais (`/admin/categorias-editoriais`)
   - plataforma (`/admin/plataforma/versoes`)

## 6) Compatibilidade de rotas
1. Rotas PT-BR novas estao ativas.
2. Aliases legados permanecem ativos (sem quebra):
   - `posts`, `post-categories`
   - `status`, `system/*`
   - `profile`, `settings`

## 7) Rollback rapido
1. Restaurar banco.
2. Restaurar codigo da release anterior.
3. Reiniciar processo PHP (para limpar OPcache).
4. Reexecutar validacao da secao 5.
