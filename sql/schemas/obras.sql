create table obras (
    id int auto_increment primary key,
    numero_obra varchar(20) not null,
    natureza_obra varchar(100) not null,
    descricao text not null,
    endereco text not null,
    cep varchar(10) null,
    data_inicio date not null,
    data_termino date null,
    status enum ('Planejamento', 'Em Andamento', 'Pausada', 'Concluída') default 'Planejamento' null,
    prioridade enum ('Baixa', 'Média', 'Alta', 'Urgente') default 'Média' null,
    valor_estimado decimal(12, 2) null,
    observacoes text null,
    criado_em timestamp default CURRENT_TIMESTAMP null,
    constraint numero_obra unique (numero_obra)
);

