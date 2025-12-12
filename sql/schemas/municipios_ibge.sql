DROP TABLE IF EXISTS municipios_ibge;

CREATE TABLE municipios_ibge (
    codigo INT UNSIGNED PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    uf CHAR(2) NOT NULL,
    uf_nome VARCHAR(40) NOT NULL,
    regiao VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_municipios_nome_uf (nome, uf),
    KEY idx_municipios_uf (uf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
