DROP TABLE IF EXISTS livro_ocorrencias;

CREATE TABLE livro_ocorrencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protocolo VARCHAR(30) NOT NULL,
    data_ocorrencia DATETIME NOT NULL,
    municipio_codigo INT UNSIGNED NOT NULL,
    municipio_nome VARCHAR(150) NOT NULL,
    subgrupamento ENUM('1º SGB','2º SGB','3º SGB','4º SGB','5º SGB','6º SGB','7º SGB','8º SGB','9º SGB','10º SGB') NOT NULL,
    tipo_ocorrencia ENUM('Busca e Salvamento','Resgate Marítimo','Incêndio Estrutural','Incêndio Florestal','Defesa Civil','Suporte Pré-Hospitalar','Outros') NOT NULL,
    descricao LONGTEXT NOT NULL,
    relatorio_conclusao LONGTEXT NULL,
    status ENUM('aberta','em_andamento','concluida','arquivada') DEFAULT 'aberta',
    responsavel_id INT UNSIGNED NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_livro_protocolo (protocolo),
    KEY idx_livro_data (data_ocorrencia),
    KEY idx_livro_municipio (municipio_codigo),
    KEY idx_livro_tipo (tipo_ocorrencia),
    KEY idx_livro_subgrupamento (subgrupamento),
    KEY idx_livro_status (status),
    CONSTRAINT fk_livro_municipio FOREIGN KEY (municipio_codigo) REFERENCES municipios_ibge(codigo),
    CONSTRAINT fk_livro_responsavel FOREIGN KEY (responsavel_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
