create table posts (
    id int auto_increment primary key,
    titulo varchar(255) not null,
    slug varchar(255) not null,
    conteudo text not null,
    autor varchar(100) null,
    categoria_id int null,
    criado_em datetime default CURRENT_TIMESTAMP null,
    atualizado_em datetime default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP,
    constraint slug unique (slug),
    constraint fk_posts_categoria foreign key (categoria_id) references categorias_posts (id)
);
