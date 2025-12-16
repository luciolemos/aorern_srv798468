DROP TABLE IF EXISTS livro_ocorrencia_tipos;

CREATE TABLE livro_ocorrencia_tipos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    descricao VARCHAR(255) NULL,
    badge_color VARCHAR(10) NULL,
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_livro_tipos_nome (nome),
    UNIQUE KEY uq_livro_tipos_slug (slug),
    KEY idx_livro_tipos_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
