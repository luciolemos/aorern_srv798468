-- Seed mínimo e idempotente para ambiente AORE/RN
-- Objetivo:
-- 1) Garantir usuário admin1968 ativo
-- 2) Garantir funções institucionais padrão
-- 3) Popular base com 3 associados de exemplo
--
-- Execução:
--   mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < sql/seeds/002_aorern_minimal.sql

START TRANSACTION;

-- 1) Usuário administrador padrão
-- Hash legado do seed base (altere a senha após primeiro acesso, se necessário)
SET @admin_hash := '$2y$12$R.6qwqUnKUam4PutfWdvTOvD6iWVIWv/O6biYFIpRG8wDms.hC8ju';

-- Normaliza admin antigo (username "admin") para o padrão atual
UPDATE users
SET username = 'admin1968',
    email = 'admin@example.com',
    role = 'admin',
    status = 'ativo',
    ativo = 1
WHERE username = 'admin';

-- Garante existência do admin1968
INSERT INTO users (username, email, password, role, status, ativo)
SELECT 'admin1968', 'admin@example.com', @admin_hash, 'admin', 'ativo', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM users
    WHERE username = 'admin1968'
       OR email = 'admin@example.com'
);

-- Reforça perfil e status do admin1968 já existente
UPDATE users
SET role = 'admin',
    status = 'ativo',
    ativo = 1
WHERE username = 'admin1968';

-- 2) Funções institucionais da Diretoria
INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-001', 'Presidente AORE/RN'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Presidente AORE/RN');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-002', 'Vice-presidente AORE/RN'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Vice-presidente AORE/RN');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-003', 'Diretor Administrativo'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor Administrativo');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-004', 'Diretor de Esportes'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor de Esportes');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-005', 'Diretor Jurídico'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor Jurídico');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-006', 'Diretor Financeiro'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor Financeiro');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-007', 'Diretor Social'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor Social');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-008', 'Diretor Cultural'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor Cultural');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-009', 'Diretor de Comunicações'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor de Comunicações');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-010', 'Diretor de Assuntos Militares'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Diretor de Assuntos Militares');

INSERT INTO funcoes (staff_id, nome)
SELECT 'FUNC-AORE-011', 'Associado AORE/RN'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM funcoes WHERE nome = 'Associado AORE/RN');

UPDATE funcoes
SET nome = 'Associado AORE/RN',
    staff_id = 'FUNC-AORE-011'
WHERE nome = 'Associado';

-- 3) Associados de exemplo
SET @f_presidente := (SELECT id FROM funcoes WHERE nome = 'Presidente AORE/RN' LIMIT 1);
SET @f_vice       := (SELECT id FROM funcoes WHERE nome = 'Vice-presidente AORE/RN' LIMIT 1);
SET @f_admin      := (SELECT id FROM funcoes WHERE nome = 'Diretor Administrativo' LIMIT 1);

-- Atualiza se já existir (por CPF)
UPDATE pessoal
SET staff_id = 'SE-196801',
    nome = 'Carlos Alberto da Silva',
    nascimento = '1968-03-12',
    telefone = '84999990001',
    funcao_id = @f_presidente,
    obra_id = NULL,
    data_admissao = '2026-01-10',
    status = 'Ativo',
    status_associativo = 'efetivo',
    jornada = NULL,
    observacoes = 'Registro de exemplo para validação de fluxos administrativos.'
WHERE cpf = '11122233344';

UPDATE pessoal
SET staff_id = 'SE-197502',
    nome = 'Raimundo Navarro Neto',
    nascimento = '1975-09-21',
    telefone = '84999990002',
    funcao_id = @f_vice,
    obra_id = NULL,
    data_admissao = '2026-01-10',
    status = 'Ativo',
    status_associativo = 'veterano',
    jornada = NULL,
    observacoes = 'Registro de exemplo para validação de fluxos administrativos.'
WHERE cpf = '22233344455';

UPDATE pessoal
SET staff_id = 'SE-198003',
    nome = 'Fernando Medeiros Costa',
    nascimento = '1980-11-03',
    telefone = '84999990003',
    funcao_id = @f_admin,
    obra_id = NULL,
    data_admissao = '2026-01-10',
    status = 'Ativo',
    status_associativo = 'aluno',
    jornada = NULL,
    observacoes = 'Registro de exemplo para validação de fluxos administrativos.'
WHERE cpf = '33344455566';

-- Insere se não existir
INSERT INTO pessoal (
    staff_id, nome, cpf, nascimento, telefone, foto, funcao_id, obra_id, data_admissao, status, status_associativo, jornada, observacoes
)
SELECT
    'SE-196801', 'Carlos Alberto da Silva', '11122233344', '1968-03-12', '84999990001', NULL,
    @f_presidente, NULL, '2026-01-10', 'Ativo', 'efetivo', NULL,
    'Registro de exemplo para validação de fluxos administrativos.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM pessoal WHERE cpf = '11122233344');

INSERT INTO pessoal (
    staff_id, nome, cpf, nascimento, telefone, foto, funcao_id, obra_id, data_admissao, status, status_associativo, jornada, observacoes
)
SELECT
    'SE-197502', 'Raimundo Navarro Neto', '22233344455', '1975-09-21', '84999990002', NULL,
    @f_vice, NULL, '2026-01-10', 'Ativo', 'veterano', NULL,
    'Registro de exemplo para validação de fluxos administrativos.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM pessoal WHERE cpf = '22233344455');

INSERT INTO pessoal (
    staff_id, nome, cpf, nascimento, telefone, foto, funcao_id, obra_id, data_admissao, status, status_associativo, jornada, observacoes
)
SELECT
    'SE-198003', 'Fernando Medeiros Costa', '33344455566', '1980-11-03', '84999990003', NULL,
    @f_admin, NULL, '2026-01-10', 'Ativo', 'aluno', NULL,
    'Registro de exemplo para validação de fluxos administrativos.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM pessoal WHERE cpf = '33344455566');

COMMIT;
