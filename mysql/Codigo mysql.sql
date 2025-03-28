CREATE DATABASE facilitau_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE facilitau_db;

-- Tabela Faculdade
CREATE TABLE Faculdade (
    faculdade_id INT AUTO_INCREMENT,
    sigla VARCHAR(10) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    rua VARCHAR(100) NOT NULL,
    cidade VARCHAR(50) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    cep VARCHAR(9) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    responsavel VARCHAR(100) NOT NULL,
    PRIMARY KEY (faculdade_id),
    UNIQUE (sigla),
    UNIQUE (email),
    CONSTRAINT chk_estado_faculdade CHECK (estado IN ('AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO')),
    CONSTRAINT chk_cep_faculdade CHECK (cep REGEXP '^[0-9]{5}-[0-9]{3}$'),
    CONSTRAINT chk_telefone_faculdade CHECK (telefone REGEXP '^\([0-9]{2}\) [0-9]{5}-[0-9]{4}$'),
    CONSTRAINT chk_email_faculdade CHECK (email LIKE '%@%.%'),
    INDEX idx_nome_faculdade (nome)
) ENGINE=InnoDB COMMENT='Tabela para armazenar as faculdades';

-- Tabela Estudante
CREATE TABLE Estudante (
    estudante_id INT AUTO_INCREMENT,
    faculdade_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    PRIMARY KEY (estudante_id),
    FOREIGN KEY (faculdade_id) REFERENCES Faculdade(faculdade_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (email),
    CONSTRAINT chk_email_estudante CHECK (email LIKE '%@%.%'),
    INDEX idx_nome_estudante (nome)
) ENGINE=InnoDB COMMENT='Tabela para armazenar os estudantes universitários';

-- Tabela Professor
CREATE TABLE Professor (
    professor_id INT AUTO_INCREMENT,
    faculdade_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    PRIMARY KEY (professor_id),
    FOREIGN KEY (faculdade_id) REFERENCES Faculdade(faculdade_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (email),
    CONSTRAINT chk_email_professor CHECK (email LIKE '%@%.%'),
    INDEX idx_nome_professor (nome)
) ENGINE=InnoDB COMMENT='Tabela para armazenar os professores';

-- Tabela Coordenador_Academico
CREATE TABLE Coordenador_Academico (
    coordenador_id INT AUTO_INCREMENT,
    faculdade_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    PRIMARY KEY (coordenador_id),
    FOREIGN KEY (faculdade_id) REFERENCES Faculdade(faculdade_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (email),
    CONSTRAINT chk_email_coordenador CHECK (email LIKE '%@%.%'),
    INDEX idx_nome_coordenador (nome)
) ENGINE=InnoDB COMMENT='Tabela para armazenar os coordenadores acadêmicos';

-- Tabela TarefaEvento
CREATE TABLE TarefaEvento (
    tarefa_evento_id INT AUTO_INCREMENT,
    estudante_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    data DATE NOT NULL,
    tipo ENUM('Tarefa', 'Evento') NOT NULL,
    PRIMARY KEY (tarefa_evento_id),
    FOREIGN KEY (estudante_id) REFERENCES Estudante(estudante_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_data_tarefa_evento (data)
) ENGINE=InnoDB COMMENT='Tabela para armazenar tarefas e eventos dos estudantes';

-- Tabela Comunicado
CREATE TABLE Comunicado (
    comunicado_id INT AUTO_INCREMENT,
    faculdade_id INT NOT NULL,
    professor_id INT,
    coordenador_id INT,
    titulo VARCHAR(200) NOT NULL,
    data DATE NOT NULL,
    PRIMARY KEY (comunicado_id),
    FOREIGN KEY (faculdade_id) REFERENCES Faculdade(faculdade_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES Professor(professor_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (coordenador_id) REFERENCES Coordenador_Academico(coordenador_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_comunicado_source CHECK (
        (professor_id IS NOT NULL AND coordenador_id IS NULL) OR 
        (professor_id IS NULL AND coordenador_id IS NOT NULL)
    ),
    INDEX idx_data_comunicado (data)
) ENGINE=InnoDB COMMENT='Tabela para armazenar comunicados de professores ou coordenadores';

-- Tabela PrazoInstitucional
CREATE TABLE PrazoInstitucional (
    prazo_id INT AUTO_INCREMENT,
    faculdade_id INT NOT NULL,
    coordenador_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    data DATE NOT NULL,
    PRIMARY KEY (prazo_id),
    FOREIGN KEY (faculdade_id) REFERENCES Faculdade(faculdade_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (coordenador_id) REFERENCES Coordenador_Academico(coordenador_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_data_prazo (data)
) ENGINE=InnoDB COMMENT='Tabela para armazenar prazos institucionais';