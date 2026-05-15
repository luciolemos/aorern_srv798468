# Deploy (AORE/RN)

Este guia está alinhado com a stack atual (Twig + migrations + `.env`).
Para operação contínua, use junto com [`RUNBOOK.md`](/var/www/aorern/RUNBOOK.md).
Checklist específico Hostinger: [`CHECKLIST_DEPLOY_HOSTINGER.md`](/var/www/aorern/CHECKLIST_DEPLOY_HOSTINGER.md).

## Ambiente e URL base

Defina `APP_URL` explicitamente no `.env` conforme o cenário:

- Desenvolvimento em subdiretório:
  - `APP_URL=https://srv798468.hstgr.cloud/aorern`
- Produção em domínio raiz:
  - `APP_URL=https://aorern.org`

Evite depender de detecção automática de path em hospedagem compartilhada.

## 1) Pré-deploy

1. Confirmar branch/release.
2. Backup:
   - Banco de dados.
   - Diretório `public/uploads/` (arquivos enviados).
3. Verificar `.env` do servidor de destino.

## 2) Publicação

```bash
git pull
composer install --no-dev --optimize-autoloader
php migrate migrate
```

## 3) Pós-deploy imediato

```bash
find app -type f -name '*.php' -print0 | xargs -0 -n1 php -l
./vendor/bin/phpunit
bash scripts/smoke_membership_status_flow.sh https://SEU_DOMINIO/aorern
```

## 4) Serviços e cache

- Reiniciar PHP-FPM (ou processo PHP equivalente) para recarregar OPcache.
- Validar permissões de escrita em `public/uploads/` e `logs/`.

## 5) Rollback

1. Restaurar dump do banco.
2. Restaurar release anterior.
3. Reiniciar serviços PHP/web.
4. Reexecutar validação pós-deploy.
