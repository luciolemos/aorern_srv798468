Seria uma mudança de eixo do painel: sair de um admin operacional herdado e virar um admin de `entidade associativa`, `comunicação institucional`, `memória` e `gestão de associados`.

## Fluxo atual de acesso

### Fluxograma textual
1. O ex-aluno do NPOR acessa `/register`.
2. Ele envia uma `solicitação de filiação` com dados pessoais, dados NPOR, senha e anexos.
3. O sistema grava esse pedido em `membership_applications` com `status = pendente`.
4. Nenhuma conta de acesso ao painel é criada nesse momento.
5. A diretoria/secretaria analisa a fila em `admin/solicitacoes-filiacao`.
6. O pedido pode ser marcado como:
   - `pendente`
   - `complementacao`
   - `aprovada`
   - `rejeitada`
7. Se a diretoria pedir `complementacao`, o pedido continua ativo e o solicitante recebe orientação por e-mail.
8. Se a diretoria `rejeitar`, o fluxo é encerrado sem criação de conta.
9. Se a diretoria `aprovar`, o sistema:
   - cria o usuário em `users`
   - cria ou atualiza o cadastro em `pessoal`
   - vincula a solicitação ao usuário e ao associado
   - define o `status_associativo`
10. Depois da aprovação, o associado faz login em `/login/admin`.
11. Se o `role` for `usuario`, o sistema redireciona para `/associado`.
12. Se o `role` for `admin`, `gerente` ou `operador`, o sistema redireciona para `/admin/dashboard`.

### Política de perfis e permissões

#### `usuario`
- Perfil padrão do associado aprovado.
- Não é perfil de operação interna do painel.
- Destino após login: `/associado`.
- Pode manter seus dados de conta, trocar senha e acompanhar sua filiação.

#### `operador`
- Perfil interno para apoio administrativo.
- Destino após login: `/admin/dashboard`.
- Deve receber apenas permissões operacionais necessárias.

#### `gerente`
- Perfil interno com alcance mais amplo sobre conteúdos e gestão.
- Destino após login: `/admin/dashboard`.
- Pode supervisionar módulos e equipes sem receber privilégios totais de administração.

#### `admin`
- Perfil máximo do sistema.
- Destino após login: `/admin/dashboard`.
- Controla usuários, aprovações, bloqueios, roles e módulos sensíveis.

### Regra institucional recomendada
- Todo ex-aluno aprovado começa como `usuario`, salvo decisão expressa da diretoria de conceder acesso interno.
- Acesso ao painel administrativo não deve ser automático para associados.
- Se um associado também exercer função interna na AORE/RN, a diretoria pode promover seu `role` para `operador`, `gerente` ou `admin`.
- A promoção de `role` é uma decisão administrativa separada da aprovação da filiação.

**Como eu desenharia**
1. `Dashboard`
   - resumo de associados
   - pedidos de filiação pendentes
   - publicações pendentes
   - agenda institucional próxima
   - mensagens recebidas pelo site
   - atalhos para selo, hino, documentos oficiais

2. `Associados`
   - cadastro de associados
   - situação: ativo, licenciado, honorário, pendente, falecido
   - turma/origem: `NPOR`, `CPOR`, ano, arma/quadro
   - contatos, profissão, cidade
   - histórico associativo
   - emissão de ficha/carteira/declaração

3. `Filiação e Cadastro`
   - solicitações de ingresso
   - revisão documental
   - aprovação/reprovação
   - workflow simples de secretaria

4. `Comunicação`
   - posts/notícias
   - categorias
   - banners e destaques da home
   - mensagens do formulário de contato
   - mailing/listas institucionais
   - modelos de e-mail com cabeçalho oficial

5. `Institucional`
   - missão, valores, visão
   - selo/brasão
   - hino
   - documentos oficiais
   - política de privacidade
   - termos de uso
   - downloads oficiais da marca

6. `Agenda e Eventos`
   - solenidades
   - encontros de turma
   - reuniões
   - convites
   - calendário institucional
   - presença/confirmação

7. `Honrarias e Memória`
   - medalhas e distinções
   - homenageados
   - acervo histórico
   - galerias por evento
   - linha do tempo da associação

8. `Diretoria e Governança`
   - cargos
   - composição da diretoria
   - mandatos
   - atas e documentos internos
   - conselhos/comissões

9. `Configurações`
   - contatos institucionais
   - WhatsApp
   - SMTP
   - usuários do painel
   - permissões
   - aparência institucional básica

**O que sairia do painel atual**
- `K9`
- `ocorrências`
- `equipamentos`
- módulos de resposta operacional
- termos como `livro de ocorrências`, `tipos de ocorrência`, `frota`, `status operacional`

**Como ficaria o menu lateral**
- Painel
- Associados
- Filiação
- Comunicação
- Institucional
- Agenda e Eventos
- Honrarias e Memória
- Diretoria
- Usuários e Permissões
- Configurações

**Estrutura de dados mínima**
- `associados`
- `solicitacoes_filiacao`
- `diretoria_mandatos`
- `cargos_diretoria`
- `eventos`
- `confirmacoes_evento`
- `honrarias`
- `homenageados`
- `documentos_institucionais`
- `downloads_brand`
- `mensagens_contato`
- `galerias`
- `galeria_itens`

**Minha recomendação prática**
Não tentar “adaptar tudo de uma vez”. Eu faria em 3 fases:

1. `Fase 1`
   - limpar menu admin
   - esconder módulos herdados
   - renomear labels
   - criar `Dashboard`, `Associados`, `Comunicação`, `Institucional`

2. `Fase 2`
   - criar cadastros reais de associados, diretoria, eventos e mensagens

3. `Fase 3`
   - migrar/remover tabelas e controllers herdados que não fazem sentido

**Se eu fosse executar agora**
Eu começaria por:
1. reestruturar o menu lateral do admin;
2. renomear o dashboard e cards principais;
3. esconder módulos herdados sem apagar nada;
4. criar placeholders para `Associados`, `Comunicação`, `Institucional` e `Eventos`.

Se quiser, eu posso fazer essa `Fase 1` agora no código.
