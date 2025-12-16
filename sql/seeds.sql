SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE equipamentos;
TRUNCATE TABLE pessoal;
TRUNCATE TABLE funcoes;
TRUNCATE TABLE categorias_equipamentos;

SET FOREIGN_KEY_CHECKS = 1;

-- Categorias de equipamentos
INSERT INTO categorias_equipamentos (staff_id, nome) VALUES
('CATEQP-20250528193800', 'Máquinas elétricas'),
('CATEQP-20250528193801', 'Equipamentos de precisão'),
('CATEQP-20250528193802', 'Ferramentas manuais');

-- Funções
INSERT INTO funcoes (staff_id, nome) VALUES
('FUNC-20250528193800', 'Engenheiro Chefe'),
('FUNC-20250528193801', 'Coordenador de Manutenção'),
('FUNC-20250528193802', 'Operador de Resgate');

-- Equipamentos
INSERT INTO equipamentos (
	staff_id,
	nome,
	codigo,
	serial_number,
	descricao,
	categoria_id,
	marca,
	modelo,
	data_fabricacao,
	estado,
	quantidade_estoque
) VALUES
('EQP-20250528194001', 'Furadeira Elétrica', 'EQP001', '123-XYZ-001', 'Furadeira para perfuração leve.', 1, 'Bosch', 'FUR-450', '2023-01-15', 'Operacional', 5),
('EQP-20250528194002', 'Trena a Laser', 'EQP002', '456-LZR-002', 'Mede até 60m com precisão.', 2, 'Stanley', 'TL-60', '2022-11-20', 'Operacional', 3);

-- Bombeiros
INSERT INTO pessoal (
	staff_id,
	nome,
	cpf,
	nascimento,
	telefone,
	foto,
	funcao_id,
	obra_id,
	data_admissao,
	status,
	jornada,
	observacoes
) VALUES
('FIREMAN-20250528195001', 'Lucas Silva', '12345678901', '1987-04-10', '11999999999', NULL, 1, NULL, '2010-05-12', 'Ativo', '24x72', 'Responsável pelo planejamento operacional.'),
('FIREMAN-20250528195002', 'Marina Costa', '98765432100', '1990-09-22', '11988888888', NULL, 2, NULL, '2014-08-03', 'Férias', '24x72', 'Coordena equipes em campo.');
