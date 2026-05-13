# Inventário de Rotas Pendentes para Migração Declarativa

Objetivo: listar endpoints que ainda dependem do routing por convenção em `app/Core/App.php` e priorizar a migração para `config/routes.php`.

## Escopo já migrado (status atual)
Coberto no declarativo:

1. Núcleo público e autenticação:
- `home`, `blog`, `login`, `register`.

2. P0 concluído:
- `about`, `contact` (`GET`/`POST`), `galeria`, bloco `institucional/*`.
- jornada `associado/*`.

3. Núcleo admin institucional:
- `publicacoes`, `categorias-editoriais`, `usuarios`, `solicitacoes-filiacao`, `pessoal`, `diretoria`, `mandatos`, `documentos`, `galeria`, `patrocinadores`, `plataforma`, `perfil`, `alterar-senha`, `configuracoes`.
- aliases legados mapeados e mantidos.

4. P1 concluído (legado operacional em uso):
- `livro-tipos/*`, `livro-ocorrencias/*`, `livro-ocorrencias/municipios`.
- `categorias/*`, `funcoes/*`, `equipamentos/*`, `obras/*`.
- `dogs/*`, `dog-breeds/*` (incluindo aliases `delete`/`destroy`).

5. P2 concluído:
- hubs admin: `admin/institucional`, `admin/memoria`, `admin/eventos`.
- docs: `admin/docs/*` e `site/docs/*`.
- auxiliares: `termos`, `privacidade`, `readme`, `esquadrao`, `coverage`, `coverage/relatorio`, `ocorrencias/mapa-municipios`.

## Pendências Prioridade P0 (alto uso/impacto)

1. Concluído.

## Pendências Prioridade P1 (admin legado ainda referenciado em menu/telas)

1. Concluído.

## Pendências Prioridade P2 (institucional interno/apoio)

1. Concluído.

## Pendências Prioridade P3 (candidatas a arquivamento)

1. Módulo `Admin\\OpusController`:
- rotas antigas (`cad_*`, `list_*`, `opus_manager`, `user_manager`, etc.)
- recomendação: manter fora do declarativo principal até decisão explícita de manter/descontinuar.

2. Fallback residual em `App.php`:
- manter temporariamente ativo para qualquer URL não mapeada.
- recomendação: instrumentar log de fallback por 1-2 ciclos e remover quando não houver uso relevante.

## Ordem prática de execução

1. Decidir destino do `OpusController`:
- manter e declarar explicitamente, ou descontinuar e retirar do menu/escopo.

2. Medir uso real do fallback:
- registrar URLs que ainda batem em `App.php` e classificar por impacto.

3. Planejar remoção gradual do fallback:
- quando logs indicarem uso residual desprezível e cobertura de testes estiver estável.

## Critério de conclusão da migração

1. Todas as URLs referenciadas por menu/templates/admin/site resolvidas via `Router::dispatch`.
2. `App.php` restrito a fallback residual explícito, monitorado por logs.
3. Testes cobrindo: login, redirecionamento por role, rotas PT-BR + aliases legados, jornadas públicas e módulos operacionais legados.
