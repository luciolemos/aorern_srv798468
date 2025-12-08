-- Portal Administrativo - Schema consolidado
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS pessoal;
DROP TABLE IF EXISTS equipamentos;
DROP TABLE IF EXISTS obras;
DROP TABLE IF EXISTS funcoes;
DROP TABLE IF EXISTS categorias_equipamentos;
DROP TABLE IF EXISTS categorias_posts;
DROP TABLE IF EXISTS users;

-- Tabela: users
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL,
    email VARCHAR(120) NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    role ENUM('admin','gerente','operador','usuario') DEFAULT 'usuario',
    status ENUM('pendente','ativo','bloqueado') DEFAULT 'pendente',
    ativo TINYINT(1) DEFAULT 0,
    ultimo_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_status (status),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: categorias_posts
CREATE TABLE categorias_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT NULL,
    badge_color VARCHAR(7) NOT NULL DEFAULT '#df6301',
    icone VARCHAR(50) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_categorias_posts_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: categorias_equipamentos
CREATE TABLE categorias_equipamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cat_equip_staff (staff_id),
    UNIQUE KEY uq_cat_equip_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: funcoes
CREATE TABLE funcoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL,
    nome VARCHAR(120) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_funcoes_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: obras
CREATE TABLE obras (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_obra VARCHAR(20) NOT NULL,
    natureza_obra VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    endereco TEXT NOT NULL,
    cep VARCHAR(10) NULL,
    data_inicio DATE NOT NULL,
    data_termino DATE NULL,
    status ENUM('Planejamento','Em Andamento','Pausada','Concluída') DEFAULT 'Planejamento',
    prioridade ENUM('Baixa','Média','Alta','Urgente') DEFAULT 'Média',
    valor_estimado DECIMAL(12,2) NULL,
    observacoes TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_obras_numero (numero_obra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: equipamentos
CREATE TABLE equipamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    codigo VARCHAR(50) NULL,
    serial_number VARCHAR(50) NULL,
    descricao TEXT NULL,
    marca VARCHAR(100) NULL,
    modelo VARCHAR(100) NULL,
    data_fabricacao DATE NULL,
    estado VARCHAR(30) NULL,
    quantidade_estoque INT DEFAULT 0,
    categoria_id INT UNSIGNED NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_equip_staff (staff_id),
    KEY idx_equip_categoria (categoria_id),
    CONSTRAINT fk_equip_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_equipamentos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: pessoal
CREATE TABLE pessoal (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    nascimento DATE NULL,
    telefone VARCHAR(20) NULL,
    foto VARCHAR(255) NULL,
    funcao_id INT UNSIGNED NOT NULL,
    obra_id INT UNSIGNED NULL,
    data_admissao DATE NOT NULL,
    status ENUM('Ativo','Afastado','Férias','Demitido') DEFAULT 'Ativo',
    jornada VARCHAR(20) NULL,
    observacoes TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pessoal_staff (staff_id),
    UNIQUE KEY uq_pessoal_cpf (cpf),
    KEY idx_pessoal_funcao (funcao_id),
    KEY idx_pessoal_obra (obra_id),
    CONSTRAINT fk_pessoal_funcao FOREIGN KEY (funcao_id) REFERENCES funcoes(id),
    CONSTRAINT fk_pessoal_obra FOREIGN KEY (obra_id) REFERENCES obras(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: posts
CREATE TABLE posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    conteudo LONGTEXT NOT NULL,
    capa_url VARCHAR(512) NULL,
    categoria_id INT UNSIGNED NULL,
    status ENUM('draft','pending','in_review','published','rejected') DEFAULT 'draft',
    reject_reason TEXT NULL,
    is_hidden TINYINT(1) DEFAULT 0,
    published_at DATETIME NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_posts_slug (slug),
    KEY idx_posts_status (status),
    KEY idx_posts_categoria (categoria_id),
    KEY idx_posts_user (user_id),
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_posts_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
