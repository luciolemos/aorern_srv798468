-- Seed idempotente: somente solicitações de filiação (membership_applications)
-- Não altera users, pessoal, diretoria ou outras tabelas.
--
-- Execução:
--   mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < sql/seeds/003_membership_applications_only.sql

START TRANSACTION;

SET @pwd_hash := '$2y$12$q0UeQVw0jOH4SqjobtjRq.qSppPAm.0LDedZAkB/Lbt/uZYFRhUce'; -- Senha@123
SET @default_avatar := 'assets/images/conscrito.png';

-- Limpa solicitações existentes antes de popular os dados de teste
DELETE FROM membership_applications;
ALTER TABLE membership_applications AUTO_INCREMENT = 1;

INSERT INTO membership_applications (
    nome_completo, nome_mae, nome_pai, username_desejado, email, password_hash, cpf,
    cam, rg, data_nascimento, telefone, cidade, uf, ano_npor, posto_graduacao, numero_militar, nome_guerra,
    turma_npor, arma_quadro, situacao_militar, avatar, documentos_json, observacoes, aceite_termo,
    status, status_associativo, user_id, pessoal_id, observacoes_admin, aprovado_em, rejeitado_em,
    last_notification_type, last_notification_status, last_notification_at, last_notification_error
) VALUES
(
    'Carlos Alberto da Silva', 'Maria das Dores Silva', 'José Alberto da Silva', 'carlossilva1968', 'carlossilva1968@aorern.test', @pwd_hash, '11122233344',
    'CAM-1968-001', '10102976455/SSP-CE', '1968-03-12', '84999990001', 'Natal', 'RN', '1968', 'Aluno NPOR', '01', 'Carlos',
    'Turma 1968', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação seed para teste de aprovação.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Raimundo Navarro Neto', 'Ana Navarro Neto', 'Raimundo Neto', 'navarroneto1975', 'navarroneto1975@aorern.test', @pwd_hash, '22233344455',
    'CAM-1975-002', '20102976455/SSP-RN', '1975-09-21', '84999990002', 'Mossoró', 'RN', '1975', 'Aluno NPOR', '02', 'Navarro',
    'Turma 1975', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação seed para teste de rejeição.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Fernando Medeiros Costa', 'Clara Medeiros Costa', 'Paulo Costa', 'fernandocosta1980', 'fernandocosta1980@aorern.test', @pwd_hash, '33344455566',
    'CAM-1980-003', '30102976455/SSP-RN', '1980-11-03', '84999990003', 'Parnamirim', 'RN', '1980', 'Aluno NPOR', '03', 'Medeiros',
    'Turma 1980', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação seed em complementação documental.', 1,
    'complementacao', 'provisorio', NULL, NULL, 'Favor complementar documento de identidade.', NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Antônio Bezerra Lima', 'Ivone Lima', 'Antônio Bezerra', 'antonio1969', 'antonio1969@aorern.test', @pwd_hash, '44455566677',
    'CAM-1969-004', '40102976455/SSP-RN', '1969-01-09', '84999990004', 'Caicó', 'RN', '1969', 'Aluno NPOR', '04', 'Bezerra',
    'Turma 1969', 'Infantaria', NULL, @default_avatar, NULL, 'Cadastro inicial aguardando avaliação.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Marcos Paulo Furtado', 'Luiza Furtado', 'Arnaldo Furtado', 'marcos1970', 'marcos1970@aorern.test', @pwd_hash, '55566677788',
    'CAM-1970-005', '50102976455/SSP-RN', '1970-05-15', '84999990005', 'Currais Novos', 'RN', '1970', 'Aluno NPOR', '05', 'Marcos',
    'Turma 1970', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação com documentação básica.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'João Ricardo de Souza', 'Márcia Souza', 'Ricardo Souza', 'joaoricardo1971', 'joaoricardo1971@aorern.test', @pwd_hash, '66677788899',
    'CAM-1971-006', '60102976455/SSP-RN', '1971-07-28', '84999990006', 'Assu', 'RN', '1971', 'Aluno NPOR', '06', 'Ricardo',
    'Turma 1971', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação pendente de triagem.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Eduardo Nascimento Alves', 'Tereza Alves', 'João Alves', 'eduardo1972', 'eduardo1972@aorern.test', @pwd_hash, '77788899900',
    'CAM-1972-007', '70102976455/SSP-RN', '1972-10-02', '84999990007', 'Santa Cruz', 'RN', '1972', 'Aluno NPOR', '07', 'Eduardo',
    'Turma 1972', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação recebida com anexos pendentes.', 1,
    'complementacao', 'provisorio', NULL, NULL, 'Anexar comprovante adicional de formação militar.', NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Felipe Carvalho Tavares', 'Denise Carvalho', 'Hélio Tavares', 'felipe1973', 'felipe1973@aorern.test', @pwd_hash, '88899900011',
    'CAM-1973-008', '80102976455/SSP-RN', '1973-12-19', '84999990008', 'Macaíba', 'RN', '1973', 'Aluno NPOR', '08', 'Felipe',
    'Turma 1973', 'Infantaria', NULL, @default_avatar, NULL, 'Aguardando decisão da comissão.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Gustavo Pereira Campos', 'Lúcia Campos', 'Adriano Campos', 'gustavo1974', 'gustavo1974@aorern.test', @pwd_hash, '99900011122',
    'CAM-1974-009', '90102976455/SSP-RN', '1974-04-06', '84999990009', 'São Gonçalo do Amarante', 'RN', '1974', 'Aluno NPOR', '09', 'Gustavo',
    'Turma 1974', 'Infantaria', NULL, @default_avatar, NULL, 'Perfil apto para análise final.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Henrique Mota Fernandes', 'Sônia Mota', 'Carlos Fernandes', 'henrique1976', 'henrique1976@aorern.test', @pwd_hash, '00011122233',
    'CAM-1976-010', '00102976455/SSP-RN', '1976-08-30', '84999990010', 'Pau dos Ferros', 'RN', '1976', 'Aluno NPOR', '10', 'Henrique',
    'Turma 1976', 'Infantaria', NULL, @default_avatar, NULL, 'Aguardando validação cadastral.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Igor Batista Moura', 'Eliane Moura', 'Paulo Batista', 'igor1977', 'igor1977@aorern.test', @pwd_hash, '10111222334',
    'CAM-1977-011', '10112976455/SSP-RN', '1977-03-11', '84999990011', 'Nova Cruz', 'RN', '1977', 'Aluno NPOR', '11', 'Igor',
    'Turma 1977', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação em processamento.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Júlio César Andrade', 'Marta Andrade', 'Jorge Andrade', 'julio1978', 'julio1978@aorern.test', @pwd_hash, '20222333445',
    'CAM-1978-012', '20112976455/SSP-RN', '1978-06-17', '84999990012', 'Ceará-Mirim', 'RN', '1978', 'Aluno NPOR', '12', 'Júlio',
    'Turma 1978', 'Infantaria', NULL, @default_avatar, NULL, 'Aguardando revisão documental.', 1,
    'complementacao', 'provisorio', NULL, NULL, 'Reenviar RG em melhor resolução.', NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Leandro Farias Rocha', 'Patrícia Rocha', 'Marcelo Farias', 'leandro1979', 'leandro1979@aorern.test', @pwd_hash, '30333444556',
    'CAM-1979-013', '30112976455/SSP-RN', '1979-09-25', '84999990013', 'João Câmara', 'RN', '1979', 'Aluno NPOR', '13', 'Leandro',
    'Turma 1979', 'Infantaria', NULL, @default_avatar, NULL, 'Solicitação pronta para deliberação.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Mateus Oliveira Nunes', 'Sandra Nunes', 'Rogério Oliveira', 'mateus1981', 'mateus1981@aorern.test', @pwd_hash, '40444555667',
    'CAM-1981-014', '40112976455/SSP-RN', '1981-02-14', '84999990014', 'Apodi', 'RN', '1981', 'Aluno NPOR', '14', 'Mateus',
    'Turma 1981', 'Infantaria', NULL, @default_avatar, NULL, 'Cadastro com documentação completa.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
),
(
    'Nataniel Gomes Freire', 'Helena Freire', 'Roberto Gomes', 'nataniel1982', 'nataniel1982@aorern.test', @pwd_hash, '50555666778',
    'CAM-1982-015', '50112976455/SSP-RN', '1982-11-08', '84999990015', 'Touros', 'RN', '1982', 'Aluno NPOR', '15', 'Nataniel',
    'Turma 1982', 'Infantaria', NULL, @default_avatar, NULL, 'Aguardando conferência final.', 1,
    'pendente', 'provisorio', NULL, NULL, NULL, NULL, NULL,
    NULL, NULL, NULL, NULL
);

COMMIT;
