-- Portal Administrativo - Schema consolidado
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS dog_handlers;
DROP TABLE IF EXISTS dog_health_records;
DROP TABLE IF EXISTS dog_evaluations;
DROP TABLE IF EXISTS dog_missions;
DROP TABLE IF EXISTS dog_vaccinations;
DROP TABLE IF EXISTS dogs;
DROP TABLE IF EXISTS dog_breeds;
DROP TABLE IF EXISTS livro_ocorrencias;
DROP TABLE IF EXISTS livro_ocorrencia_tipos;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS gallery_images;
DROP TABLE IF EXISTS gallery_categories;
DROP TABLE IF EXISTS pessoal;
DROP TABLE IF EXISTS equipamentos;
DROP TABLE IF EXISTS obras;
DROP TABLE IF EXISTS funcoes;
DROP TABLE IF EXISTS categorias_equipamentos;
DROP TABLE IF EXISTS categorias_posts;
DROP TABLE IF EXISTS municipios_ibge;
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
    slug VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    badge_color VARCHAR(7) NOT NULL DEFAULT '#df6301',
    icone VARCHAR(50) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_categorias_posts_nome (nome),
    UNIQUE KEY uq_categorias_posts_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: municipios_ibge
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

-- Tabela: livro_ocorrencia_tipos
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

-- Tabela: livro_ocorrencias
CREATE TABLE livro_ocorrencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protocolo VARCHAR(30) NOT NULL,
    data_ocorrencia DATETIME NOT NULL,
    closed_at DATETIME NULL,
    municipio_codigo INT UNSIGNED NOT NULL,
    municipio_nome VARCHAR(150) NOT NULL,
    subgrupamento ENUM('1º SGB','2º SGB','3º SGB','4º SGB','5º SGB','6º SGB','7º SGB','8º SGB','9º SGB','10º SGB') NOT NULL,
    tipo_id INT UNSIGNED NOT NULL,
    descricao LONGTEXT NOT NULL,
    relatorio_conclusao LONGTEXT NULL,
    status ENUM('aberta','concluida') DEFAULT 'aberta',
    responsavel_id INT UNSIGNED NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_livro_protocolo (protocolo),
    KEY idx_livro_data (data_ocorrencia),
    KEY idx_livro_closed_at (closed_at),
    KEY idx_livro_municipio (municipio_codigo),
    KEY idx_livro_tipo (tipo_id),
    KEY idx_livro_subgrupamento (subgrupamento),
    KEY idx_livro_status (status),
    CONSTRAINT fk_livro_municipio FOREIGN KEY (municipio_codigo) REFERENCES municipios_ibge(codigo),
    CONSTRAINT fk_livro_responsavel FOREIGN KEY (responsavel_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_livro_tipo FOREIGN KEY (tipo_id) REFERENCES livro_ocorrencia_tipos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: dog_breeds
CREATE TABLE dog_breeds (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    size ENUM('small','medium','large','giant') DEFAULT 'medium',
    `function` ENUM('companion','guard','hunting','herding','working','terrier','sporting','toy','non-sporting') DEFAULT NULL,
    origin VARCHAR(120) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_dog_breeds_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: dogs
CREATE TABLE dogs (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    breed_id INT(10) UNSIGNED NOT NULL,
    birth_date DATE DEFAULT NULL,
    birth_city VARCHAR(150) DEFAULT NULL,
    birth_state CHAR(2) DEFAULT NULL,
    weight_kg DECIMAL(5,2) DEFAULT NULL,
    sex ENUM('male','female','unknown') DEFAULT 'unknown',
    operational_function ENUM('search_rescue','scent','guard','other') DEFAULT NULL,
    training_phase ENUM('iib','i3','iiq') DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('available','training','adopted','inactive') DEFAULT 'available',
    notes TEXT DEFAULT NULL,
    identifying_marks TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_dogs_slug (slug),
    KEY fk_dogs_breed (breed_id),
    CONSTRAINT fk_dogs_breed FOREIGN KEY (breed_id) REFERENCES dog_breeds (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: dog_vaccinations
CREATE TABLE dog_vaccinations (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    dog_id INT(10) UNSIGNED NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    vaccine_date DATE NOT NULL,
    next_dose_date DATE DEFAULT NULL,
    veterinarian VARCHAR(150) DEFAULT NULL,
    clinic VARCHAR(150) DEFAULT NULL,
    batch_number VARCHAR(50) DEFAULT NULL,
    allergic_reaction ENUM('none','mild','moderate','severe') DEFAULT 'none',
    reaction_notes TEXT DEFAULT NULL,
    dose_type ENUM('first','second','third','booster','single','other') DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_dog_vaccine (dog_id, vaccine_date),
    CONSTRAINT fk_vaccinations_dog FOREIGN KEY (dog_id) REFERENCES dogs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: dog_missions
CREATE TABLE dog_missions (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    dog_id INT(10) UNSIGNED NOT NULL,
    mission_type ENUM('search_rescue','detection','patrol','therapy','training','competition','other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    location VARCHAR(200) DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    duration_hours DECIMAL(6,2) DEFAULT NULL,
    performance_rating ENUM('excellent','good','satisfactory','needs_improvement') DEFAULT NULL,
    supervisor VARCHAR(150) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    achievements TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_dog_mission_date (dog_id, start_date),
    CONSTRAINT fk_missions_dog FOREIGN KEY (dog_id) REFERENCES dogs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: dog_evaluations
CREATE TABLE dog_evaluations (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    dog_id INT(10) UNSIGNED NOT NULL,
    physical_condition DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    focus_attention DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    obedience_control DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    perseverance_resilience DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    technical_capacity DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    sociability_temperament DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    total_score DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    classification ENUM('apto_sem_restricoes','apto_com_acompanhamento','treinamento_intensivo','restrito') DEFAULT NULL,
    tac_stage ENUM('first','second','third') DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    evaluated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_dog_evaluations_dog_id (dog_id),
    KEY idx_dog_evaluations_evaluated_at (evaluated_at),
    CONSTRAINT fk_dog_evaluations_dog FOREIGN KEY (dog_id) REFERENCES dogs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: dog_health_records
CREATE TABLE dog_health_records (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    dog_id INT(10) UNSIGNED NOT NULL,
    record_type ENUM('exam','consultation','surgery','medication','injury','illness','checkup','other') NOT NULL,
    record_date DATE NOT NULL,
    veterinarian VARCHAR(150) DEFAULT NULL,
    clinic VARCHAR(150) DEFAULT NULL,
    diagnosis VARCHAR(255) DEFAULT NULL,
    treatment TEXT DEFAULT NULL,
    medication TEXT DEFAULT NULL,
    exam_results TEXT DEFAULT NULL,
    follow_up_date DATE DEFAULT NULL,
    cost DECIMAL(10,2) DEFAULT NULL,
    attachments VARCHAR(500) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_dog_health_date (dog_id, record_date),
    CONSTRAINT fk_health_records_dog FOREIGN KEY (dog_id) REFERENCES dogs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: dog_handlers
CREATE TABLE dog_handlers (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    dog_id INT(10) UNSIGNED NOT NULL,
    user_id INT(10) UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    nomination_document VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_dog_handler (dog_id, start_date),
    KEY idx_user_handler (user_id),
    CONSTRAINT fk_handlers_dog FOREIGN KEY (dog_id) REFERENCES dogs (id) ON DELETE CASCADE,
    CONSTRAINT fk_handlers_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: gallery_categories
CREATE TABLE gallery_categories (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(64) NOT NULL,
    slug VARCHAR(128) DEFAULT NULL,
    color VARCHAR(16) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_gallery_categories_nome (nome),
    UNIQUE KEY uq_gallery_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: gallery_images
CREATE TABLE gallery_images (
    id INT(11) NOT NULL AUTO_INCREMENT,
    category_id INT(10) UNSIGNED NOT NULL,
    titulo VARCHAR(128) NOT NULL,
    descricao TEXT DEFAULT NULL,
    url VARCHAR(255) NOT NULL,
    data_upload DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_gallery_category (category_id),
    CONSTRAINT fk_gallery_images_category FOREIGN KEY (category_id) REFERENCES gallery_categories (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
