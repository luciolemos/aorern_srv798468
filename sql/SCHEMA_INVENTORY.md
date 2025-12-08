# Inventário do Esquema Atual

Este documento lista cada tabela necessária segundo os Models, indicando colunas esperadas e situação nos scripts SQL existentes.

## 1. users
- **Campos usados:** id, username, email, password, avatar, role, status, ativo, ultimo_login, created_at, updated_at.
- **Situação atual:** não existe em `sql/schema.sql` nem em `sql/schemas/*`. Necessário criar tabela completa e seed para usuário admin.

## 2. posts
- **Campos usados:** id, user_id, titulo, slug, conteudo, capa_url, categoria_id, status, reject_reason, is_hidden, criado_em, atualizado_em, published_at.
- **Situação atual:** `sql/schema.sql` possui versão incompleta (sem user_id, status, is_hidden, published_at, capa_url etc.). Migrations PHP adicionam alguns campos, mas dependem de colunas inexistentes (`status_if_exists`). Necessário reescrever criação base com todos os campos.

## 3. categorias_posts
- **Campos:** id, staff_id, nome, descricao, badge_color, criado_em, atualizado_em.
- **Situação atual:** existe apenas via migration PHP (não em `schema.sql`). Deve ser incluída no schema inicial.

## 4. categorias_equipamentos
- **Campos:** id, staff_id, nome, criado_em.
- **Situação atual:** presente em `schema.sql`. O script em `sql/schemas/categorias_equipamentos.sql` adiciona constraint unique para nome; alinhar as duas versões.

## 5. equipamentos
- **Campos usados:** id, staff_id, nome, codigo, serial_number, marca, modelo, data_fabricacao, estado, quantidade_estoque, categoria_id, criado_em.
- **Situação atual:** `schema.sql` usa `numero_serie` e inclui `descricao`; `sql/schemas/equipamentos.sql` usa `serial_number` e não possui `descricao`. Model espera `serial_number` e não utiliza `descricao`. Escolher padrão (sugestão: seguir model).

## 6. funcoes
- **Campos:** id, staff_id, nome, criado_em.
- **Situação atual:** `schema.sql` não possui `staff_id`; `sql/schemas/funcoes.sql` possui. Models exigem `staff_id`. Ajustar.

## 7. pessoal
- **Campos usados:** id, staff_id, nome, cpf, nascimento, telefone, avatar (antes foto), funcao_id, obra_id, data_admissao, status (enum), jornada, observacoes, criado_em.
- **Situação atual:** `schema.sql` possui versão simplificada (sem cpf, avatar, obra_id, enum, etc.). `sql/schemas/pessoal.sql` mais completa, mas falta FK obra_id (usada em PessoalModel). Ajustar e garantir referência para `funcoes` e `obras`.

## 8. obras
- **Campos:** id, numero_obra, natureza_obra, descricao, endereco, cep, data_inicio, data_termino, status, prioridade, valor_estimado, observacoes, criado_em.
- **Situação atual:** existe apenas em `sql/schemas/obras.sql`. Incluir no schema principal.

## 9. outras dependências
- **Tabela `config`/`system_versions`/`system_info`:** ver `config/config.php` e helpers; confirmar se existem no banco atual.
- **Seeds:** atualmente apenas categorias/equipamentos/funções/pessoal. Faltam seeds para `users` e `categorias_posts`.

## Próximos Passos
1. Consolidar `schema.sql` com todas as tabelas acima, na ordem correta (FKs depois das tabelas referenciadas).
2. Atualizar seeds ou criar diretorio `sql/seeds/` com dados mínimos.
3. Revisar migrations PHP para alinhá-las com o novo estado inicial ou substituí-las por scripts SQL numerados.
