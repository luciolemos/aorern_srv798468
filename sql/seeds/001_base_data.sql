SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE posts;
TRUNCATE TABLE pessoal;
TRUNCATE TABLE equipamentos;
TRUNCATE TABLE obras;
TRUNCATE TABLE funcoes;
TRUNCATE TABLE categorias_equipamentos;
TRUNCATE TABLE categorias_posts;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

-- Usuários base
INSERT INTO users (username, email, password, role, status, ativo, ultimo_login)
VALUES
('admin',    'admin@example.com',    '$2y$12$R.6qwqUnKUam4PutfWdvTOvD6iWVIWv/O6biYFIpRG8wDms.hC8ju', 'admin',   'ativo',    1, NOW()),
('gerente',  'gerente@example.com',  '$2y$12$R.6qwqUnKUam4PutfWdvTOvD6iWVIWv/O6biYFIpRG8wDms.hC8ju', 'gerente', 'ativo',    1, NOW()),
('operador', 'operador@example.com', '$2y$12$R.6qwqUnKUam4PutfWdvTOvD6iWVIWv/O6biYFIpRG8wDms.hC8ju', 'operador','ativo',    1, NOW()),
('reporter', 'reporter@example.com', '$2y$12$R.6qwqUnKUam4PutfWdvTOvD6iWVIWv/O6biYFIpRG8wDms.hC8ju', 'usuario', 'pendente', 0, NULL);

-- Categorias de posts
INSERT INTO categorias_posts (staff_id, nome, descricao, badge_color, icone) VALUES
('CATPOST-202501010001', 'Operações',    'Atualizações operacionais e relatórios oficiais.', '#df6301', 'bi-flag'),
('CATPOST-202501010002', 'Treinamentos', 'Capacitações internas e certificações.',        '#006989', 'bi-mortarboard'),
('CATPOST-202501010003', 'Cobertura',    'Cobertura de eventos e ações de campo.',        '#198754', 'bi-broadcast-pin'),
('CATPOST-202501010004', 'Transparência','Prestação de contas e relatórios públicos.',    '#6f42c1', 'bi-journal-richtext');

-- Categorias de equipamentos
INSERT INTO categorias_equipamentos (staff_id, nome) VALUES
('CATEQP-202501010001', 'Ferramentas Manuais'),
('CATEQP-202501010002', 'Equipamentos de Proteção Individual'),
('CATEQP-202501010003', 'Veículos e Resgate');

-- Funções
INSERT INTO funcoes (staff_id, nome) VALUES
('FUNC-202501010001', 'Chefe de Operações'),
('FUNC-202501010002', 'Comandante de Guarnição'),
('FUNC-202501010003', 'Socorrista'),
('FUNC-202501010004', 'Analista de Inteligência');

-- Obras
INSERT INTO obras (
    numero_obra, natureza_obra, descricao, endereco, cep,
    data_inicio, data_termino, status, prioridade, valor_estimado, observacoes
) VALUES
('OBRA-2025-001', 'Reforma de Quartel', 'Requalificação estrutural do 2º SGB/2º GBM.', 'Av. Prudente de Morais, 2410 - Natal/RN', '59064-620', '2025-01-02', NULL, 'Em Andamento', 'Alta', 450000.00, 'Projeto financiado via convênio estadual.'),
('OBRA-2025-002', 'Centro de Treinamento', 'Nova torre de treinamento para operações em altura.', 'BR-101, Km 153 - Parnamirim/RN', '59148-000', '2024-09-15', NULL, 'Planejamento', 'Média', 275000.00, 'Projeto aguardando licitação.');

-- Equipamentos
INSERT INTO equipamentos (
    staff_id, nome, codigo, serial_number, descricao, marca, modelo, data_fabricacao, estado, quantidade_estoque, categoria_id
) VALUES
('EQP-202501010001', 'Kit Desencarcerador', 'EQP-001', 'SN-45821', 'Conjunto hidráulico para resgate veicular.', 'Holmatro', 'Serie X', '2023-06-15', 'Operacional', 2, 3),
('EQP-202501010002', 'Capacete Estrutural Gallet', 'EQP-002', 'SN-99811', 'Capacete com viseira e proteção auricular.', 'MSA', 'F1XF', '2024-03-20', 'Operacional', 20, 2),
('EQP-202501010003', 'Detector Multigás', 'EQP-003', 'SN-77421', 'Monitoramento de ambientes confinados.', 'Dräger', 'X-am 5600', '2022-11-02', 'Manutenção', 4, 1);

-- Pessoal
INSERT INTO pessoal (
    staff_id, nome, cpf, nascimento, telefone, foto, funcao_id, obra_id, data_admissao, status, jornada, observacoes
) VALUES
('FIREMAN-202501010001', 'Cap. Lucas Andrade',   '12345678901', '1987-04-10', '84999990000', NULL, 1, NULL, '2010-05-12', 'Ativo',    '24x72', 'Responsável pelo planejamento operacional.'),
('FIREMAN-202501010002', 'Ten. Marina Costa',    '98765432100', '1990-09-22', '84988889999', NULL, 2, 1,    '2014-08-03', 'Ativo',    '24x72', 'Coordena equipes em campo.'),
('FIREMAN-202501010003', 'Sgt. Bruno Ferreira',  '11122233344', '1985-01-18', '84977776666', NULL, 3, NULL, '2008-02-17', 'Férias',   '24x72', 'Instrutor chefe de resgate veicular.'),
('FIREMAN-202501010004', 'Cb. Ana Vasconcelos',  '55566677788', '1994-12-05', '84966665555', NULL, 4, 2,    '2018-10-10', 'Afastado', '12x36', 'Analista de dados operacionais.');

-- Posts de exemplo
INSERT INTO posts (
    user_id, titulo, slug, conteudo, capa_url, categoria_id, status, reject_reason, is_hidden, published_at
) VALUES
(1, 'Plano Operacional 2025',       'plano-operacional-2025',       '<p>Diretrizes estratégicas para o ano.</p>',                         NULL, 1, 'published', NULL, 0, NOW()),
(2, 'Relatório de Ocorrências Q1',  'relatorio-ocorrencias-q1',     '<p>Resumo das principais ocorrências do trimestre.</p>',            NULL, 4, 'published', NULL, 0, NOW()),
(2, 'Treinamento de Resgate',       'treinamento-resgate-veicular', '<p>Agenda e requisitos do treinamento.</p>',                        'https://example.com/capa-treinamento.jpg', 2, 'pending', NULL, 0, NULL),
(3, 'Checklist de EPIs',            'checklist-epis',               '<p>Checklist atualizado dos EPIs obrigatórios por guarnição.</p>', NULL, 3, 'in_review', NULL, 0, NULL),
(4, 'Cobertura Operação Atlântico', 'cobertura-operacao-atlantico', '<p>Relato da operação conjunta com a Defesa Civil.</p>',          NULL, 3, 'rejected', 'Aguardando revisão de dados.', 1, NULL),
(1, 'Guia de Comunicação',          'guia-comunicacao-interna',     '<p>Boas práticas para comunicação nas guarnições.</p>',            NULL, 4, 'draft', NULL, 0, NULL);
