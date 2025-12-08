-- Tabela: categorias_equipamentos
CREATE TABLE categorias_equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: equipamentos
CREATE TABLE equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL UNIQUE,
    codigo VARCHAR(50) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    numero_serie VARCHAR(100),
    descricao TEXT,
    categoria_id INT,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    data_fabricacao DATE,
    estado VARCHAR(50),
    quantidade_estoque INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_equipamentos(id)
);

-- Tabela: pessoal
CREATE TABLE pessoal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(30),
    funcao_id INT,
    status VARCHAR(20) DEFAULT 'Ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (funcao_id) REFERENCES funcoes(id)
);

-- Tabela: funcoes
CREATE TABLE funcoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela: posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    conteudo TEXT,
    categoria_id INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_posts(id)
);
