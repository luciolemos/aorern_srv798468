# Plano de Padronizacao PT-BR (sem quebra de URL)

## Objetivo
Padronizar nomenclatura de dominio em PT-BR no admin, preservando compatibilidade com rotas e links existentes durante a migracao.

## Diretrizes
1. Dominio funcional em PT-BR:
- `documentos`, `solicitacoes-filiacao`, `usuarios`, `pessoal`, `diretoria`, `mandatos`, `galeria`, `patrocinadores`.

2. Termos tecnicos podem permanecer em EN:
- nomes de classes internas, bibliotecas e campos tecnicos de infraestrutura.

3. Compatibilidade obrigatoria:
- manter aliases de rotas antigas enquanto houver links externos, favoritos de usuarios e referencias em docs.

## Etapas seguras

1. Inventario de nomes atuais
- mapear pastas/views/controllers e rotas com mistura PT/EN.
- produzir tabela `atual -> alvo`.

2. Camada de alias de rotas
- para cada rota renomeada, criar alias legado em `config/routes.php`.
- logar acessos em alias para medir uso residual.

3. Migração de templates
- atualizar links em Twig para rotas alvo (PT-BR).
- manter alias ativos por janela de transicao.

4. Migração de controllers/views (opcional por fase)
- renomear classes/pastas internas em pequenos lotes.
- evitar renomeacao massiva em um unico PR.

5. Encerramento de aliases
- remover alias somente apos 2 ciclos de release sem uso relevante.

## Primeira onda recomendada
1. `post-categories` -> manter slug tecnico, rotulo visivel em PT-BR.
2. `gallery`/`gallery_categories` -> padronizar exibicao para `galeria`/`categorias de acervo`.
3. `users` internos -> manter classe `User`, mas consolidar UX em `usuarios`.

## Tabela de equivalencia (ativo)
1. `admin/publicacoes` <-> `admin/posts` (alias legado ativo)
2. `admin/publicacoes/cadastrar` <-> `admin/posts/create`
3. `admin/publicacoes/salvar` <-> `admin/posts/store`
4. `admin/publicacoes/editar/{id}` <-> `admin/posts/edit/{id}`
5. `admin/publicacoes/atualizar/{id}` <-> `admin/posts/update/{id}`
6. `admin/categorias-editoriais` <-> `admin/post-categories`
7. `admin/categorias-editoriais/cadastrar` <-> `admin/post-categories/create`
8. `admin/categorias-editoriais/salvar` <-> `admin/post-categories/store`
9. `admin/categorias-editoriais/editar/{id}` <-> `admin/post-categories/edit/{id}`
10. `admin/categorias-editoriais/atualizar/{id}` <-> `admin/post-categories/update/{id}`
11. `admin/plataforma/status` <-> `admin/status`
12. `admin/plataforma/guia-usuario` <-> `admin/system/guia-usuario`
13. `admin/plataforma/versoes` <-> `admin/system/versions`
14. `admin/plataforma/informacoes-tecnicas` <-> `admin/system/info`

## Status atual da segunda onda
1. Navegacao/admin sidebar apontando para slugs PT-BR.
2. Rotas legadas mantidas como alias para compatibilidade.
3. Estados de menu (`subRoute`) aceitam chaves novas e antigas na transicao.

## Criterio de pronto
1. Menus e titulos totalmente em PT-BR.
2. Rotas principais institucionais em PT-BR.
3. Aliases legados cobertos por teste automatizado.
