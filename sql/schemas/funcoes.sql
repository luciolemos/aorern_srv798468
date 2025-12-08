create table funcoes (
    id int auto_increment primary key,
    staff_id varchar(20) not null,
    nome varchar(100) not null,
    criado_em timestamp default CURRENT_TIMESTAMP null,
    constraint nome unique (nome)
);