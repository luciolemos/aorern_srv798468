create table pessoal (
    id int auto_increment primary key,
    staff_id varchar(30) null,
    nome varchar(100) not null,
    cpf varchar(14) not null,
    nascimento date null,
    telefone varchar(20) null,
    foto varchar(255) null,
    funcao_id int not null,
    obra_id int not null,
    data_admissao date not null,
    status enum ('Ativo', 'Afastado', 'Férias', 'Demitido') default 'Ativo' null,
    jornada varchar(20) null,
    observacoes text null,
    criado_em timestamp default CURRENT_TIMESTAMP null,
    constraint cpf unique (cpf),
    constraint staff_id unique (staff_id),
    constraint fk_pessoal_funcao foreign key (funcao_id) references funcoes (id)
)
    charset = utf8mb4;

