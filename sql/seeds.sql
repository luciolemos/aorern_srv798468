-- Categorias de equipamentos
INSERT INTO categorias_equipamentos (staff_id, nome) VALUES
('CAT-20250528193800', 'Máquinas elétricas'),
('CAT-20250528193801', 'Equipamentos de precisão'),
('CAT-20250528193802', 'Ferramentas manuais');

-- Funções
INSERT INTO funcoes (nome) VALUES
('Engenheiro'),
('Técnico de Manutenção'),
('Operador');

-- Equipamentos
INSERT INTO equipamentos (staff_id, codigo, nome, numero_serie, descricao, categoria_id, marca, modelo, data_fabricacao, estado, quantidade_estoque) VALUES
('EQP-20250528194001', 'EQP001', 'Furadeira Elétrica', '123-XYZ-001', 'Furadeira para perfuração leve.', 1, 'Bosch', 'FUR-450', '2023-01-15', 'Ativo', 5),
('EQP-20250528194002', 'EQP002', 'Trena a Laser', '456-LZR-002', 'Mede até 60m com precisão.', 2, 'Stanley', 'TL-60', '2022-11-20', 'Ativo', 3);

-- Bombeiros
INSERT INTO pessoal (staff_id, nome, email, telefone, funcao_id, status) VALUES
('FIREMAN-20250528195001', 'Lucas Silva', 'lucas@example.com', '(11) 99999-9999', 1, 'Ativo'),
('FIREMAN-20250528195002', 'Marina Costa', 'marina@example.com', '(11) 98888-8888', 2, 'Férias');
