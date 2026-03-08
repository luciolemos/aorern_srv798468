-- Migração: expande opções de status_associativo em membership_applications e pessoal

ALTER TABLE membership_applications
    MODIFY COLUMN status_associativo ENUM('provisorio','efetivo','honorario','fundador','benemerito','veterano','aluno')
    NOT NULL DEFAULT 'provisorio';

ALTER TABLE pessoal
    MODIFY COLUMN status_associativo ENUM('provisorio','efetivo','honorario','fundador','benemerito','veterano','aluno')
    NOT NULL DEFAULT 'provisorio';
