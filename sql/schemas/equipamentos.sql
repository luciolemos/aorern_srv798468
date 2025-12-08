create table equipamentos (
    id int auto_increment primary key,
    staff_id           varchar(20)                         not null,
    nome               varchar(100)                        not null,
    codigo             varchar(50)                         null,
    serial_number      varchar(50)                         null,
    marca              varchar(50)                         null,
    modelo             varchar(50)                         null,
    data_fabricacao    date                                null,
    estado             varchar(30)                         null,
    quantidade_estoque int                                 null,
    categoria_id       int                                 null,
    criado_em          timestamp default CURRENT_TIMESTAMP null,
    constraint equipamentos_ibfk_1
        foreign key (categoria_id) references categorias_equipamentos (id)
);

create index categoria_id
    on equipamentos (categoria_id);