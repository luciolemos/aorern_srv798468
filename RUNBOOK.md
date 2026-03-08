# Runbook Operacional (AORE/RN)

Este documento é a referência operacional única para subir, validar, operar e recuperar o sistema.

## 1) Pré-requisitos

- PHP 8.2+ com extensões: `pdo`, `pdo_mysql`, `mbstring`, `json`, `fileinfo`.
- Composer 2.x.
- MySQL 5.7+ (ideal 8.x).
- Servidor web apontando para `public/`.

## 2) Bootstrap de ambiente

1. Instalar dependências:
   ```bash
   composer install
   ```
2. Criar `.env` a partir do exemplo:
   ```bash
   cp .env.example .env
   ```
3. Ajustar credenciais e chaves no `.env`:
   - `APP_ENV`, `APP_TIMEZONE`, `APP_URL`
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_TIMEZONE`
   - SMTP e reCAPTCHA (quando aplicável)
4. Aplicar schema base e seeds mínimas (ambiente novo):
   ```bash
   mysql -u <user> -p <db> < sql/schema.sql
   mysql -u <user> -p <db> < sql/seeds/002_aorern_minimal.sql
   ```
5. Executar migrations pendentes:
   ```bash
   php migrate migrate
   ```

## 3) Validação pós-subida

1. Lint de PHP:
   ```bash
   find app -type f -name '*.php' -print0 | xargs -0 -n1 php -l
   ```
2. Testes automatizados:
   ```bash
   ./vendor/bin/phpunit
   ```
3. Smoke test de filiação (fluxo crítico):
   ```bash
   bash scripts/smoke_membership_status_flow.sh https://SEU_DOMINIO/aorern
   ```
4. Quality gate unificado (phpunit + integrações + smoke):
   ```bash
   bash scripts/run_quality_gate.sh https://SEU_DOMINIO/aorern
   ```
   Modo apenas unitário:
   ```bash
   bash scripts/run_quality_gate.sh --unit-only
   ```
   Para incluir o smoke completo (área do associado + edição admin), informe a URL pública no 2º argumento:
   ```bash
   bash scripts/run_quality_gate.sh https://SEU_DOMINIO/aorern https://SEU_DOMINIO/aorern
   ```
   Para reaproveitar o gate sem repetir PHPUnit:
   ```bash
   bash scripts/run_quality_gate.sh --base-url https://SEU_DOMINIO/aorern --skip-phpunit
   ```
5. Atalhos com `make`:
   ```bash
   make test-unit
   make test-ci BASE_URL=https://SEU_DOMINIO/aorern
   make quality BASE_URL=https://SEU_DOMINIO/aorern
   make quality-full BASE_URL=https://SEU_DOMINIO/aorern FULL_SMOKE_URL=https://SEU_DOMINIO/aorern
   make ci-local
   make ci-local-full
   make preflight
   make install-hooks
   make release-check
   make release-tag VERSION=vX.Y.Z
   ```
   Observação: `ci-local` e `ci-local-full` usam por padrão `LOCAL_BASE_URL=https://127.0.0.1/aorern` (stack web local já disponível).
6. Fluxo antes do commit:
   ```bash
   make preflight
   ```
   Para automatizar via Git hook local:
   ```bash
   make install-hooks
   ```
7. Checklist de release em comando único:
   ```bash
   make release-check
   ```
   O comando executa:
   - `preflight` (unitário),
   - quality gate completo (integrações + smokes),
   - validação de migrations pendentes.
   Artefatos são gravados em `artifacts/` com rotação automática (padrão: manter 20). Para ajustar:
   ```bash
   ARTIFACT_KEEP=10 make release-check
   ```
8. Criação de tag de release:
   ```bash
   make release-tag VERSION=v1.0.0
   ```
   Regras:
   - exige working tree limpo;
   - roda `release-check`;
   - valida checksum do artefato;
   - cria tag anotada com referência ao artefato e SHA256.

## 4) Fluxos críticos que devem estar íntegros

- Solicitação de filiação (`pendente`).
- Solicitação de complementação documental (`complementacao`).
- Aprovação e criação/vínculo em `pessoal` (`aprovada`).
- Rejeição com notificação (`rejeitada`).
- Login no painel admin.

## 5) Uploads e permissões

- Uploads são gravados em `public/uploads/` (subpastas por módulo).
- Diretório precisa ter permissão de escrita para o usuário do PHP-FPM/Apache.
- Em ambiente novo, o sistema cria subpastas automaticamente quando necessário.

## 6) Deploy (resumo de execução)

1. Backup banco + arquivos.
2. `git pull` / atualização de artefatos.
3. `composer install --no-dev --optimize-autoloader`.
4. `php migrate migrate`.
5. Limpar/recarregar OPcache (reiniciar PHP-FPM).
6. Rodar validação da seção 3.

## 7) Rollback rápido

1. Restaurar backup do banco.
2. Restaurar código/artefatos da release anterior.
3. Reiniciar PHP-FPM/Apache.
4. Reexecutar validação da seção 3.

## 8) Observabilidade mínima

- Logs da aplicação: `logs/`.
- Erros web/PHP: logs do servidor.
- Em incidente de produção, registrar:
  - horário UTC
  - rota afetada
  - usuário/role (se autenticado)
  - mensagem de erro e stack trace
