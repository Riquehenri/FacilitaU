-- Banco de dados
CREATE DATABASE facilitau_db;
USE facilitau_db;

-- Tabela Usuarios (estudantes, professores e coordenadores)
CREATE TABLE Usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('estudante', 'professor', 'coordenador') NOT NULL,
    nome VARCHAR(100) NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela Avisos (avisos gerais e oportunidades)
CREATE TABLE Avisos (
    aviso_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_aviso ENUM('aviso', 'oportunidade') DEFAULT 'aviso',
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_publicacao DATE NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabela Documentos (para suporte à assistente virtual)
CREATE TABLE Documentos (
    documento_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    conteudo TEXT,
    tipo ENUM('contrato', 'regulamento', 'outro') NOT NULL,
    data_upload DATE NOT NULL,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE SET NULL
);

-- Tabela Perguntas_Respostas (para a assistente virtual)
CREATE TABLE Perguntas_Respostas (
    pergunta_resposta_id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta TEXT NOT NULL,
    resposta TEXT NOT NULL,
    categoria VARCHAR(50),
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela Tarefas_Eventos (tarefas e eventos dos estudantes)
CREATE TABLE Tarefas_Eventos (
    tarefa_evento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    data DATE NOT NULL,
    tipo ENUM('tarefa', 'evento') NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabela Planejamento_Estudos (rotina acadêmica dos estudantes)
CREATE TABLE Planejamento_Estudos (
    planejamento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo') NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    atividade VARCHAR(100) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabela Notificacoes (para lembretes e avisos automáticos)
CREATE TABLE Notificacoes (
    notificacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_notificacao ENUM('lembrete', 'aviso') NOT NULL,
    mensagem TEXT NOT NULL,
    data_notificacao DATE NOT NULL,
    enviada BOOLEAN DEFAULT FALSE,
    aviso_id INT NULL,
    tarefa_evento_id INT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (aviso_id) REFERENCES Avisos(aviso_id) ON DELETE SET NULL,
    FOREIGN KEY (tarefa_evento_id) REFERENCES Tarefas_Eventos(tarefa_evento_id) ON DELETE SET NULL
);

-- Tabela Interacoes_Assistente (histórico de interações com a assistente virtual)
CREATE TABLE Interacoes_Assistente (
    interacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pergunta TEXT NOT NULL,
    resposta TEXT,
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);