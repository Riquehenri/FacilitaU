-- Banco de dados
CREATE DATABASE facilitau_db;
USE facilitau_db;

-- Tabela Usuarios (estudantes, professores e coordenadores)
CREATE TABLE Usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,  -- Email único para login
    senha VARCHAR(255) NOT NULL,         -- Senha criptografada
    tipo ENUM('estudante', 'professor', 'coordenador') NOT NULL,  -- Tipo de usuário
    nome VARCHAR(100) NOT NULL,           -- Nome completo
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP  -- Data de cadastro automática
);

-- Tabela Avisos (avisos gerais e oportunidades)
CREATE TABLE Avisos (
    aviso_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,              -- Autor do aviso
    tipo_aviso ENUM('aviso', 'oportunidade') DEFAULT 'aviso',  -- Categoria
    titulo VARCHAR(100) NOT NULL,         -- Título do aviso
    descricao TEXT,                       -- Descrição detalhada
    data_publicacao DATE NOT NULL,        -- Data de publicação
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabela Documentos (para suporte à assistente virtual)
CREATE TABLE Documentos (
    documento_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,           -- Nome do arquivo
    conteudo TEXT,                        -- Conteúdo do documento
    tipo ENUM('contrato', 'regulamento', 'outro') NOT NULL,  -- Categoria
    data_upload DATE NOT NULL,            -- Data de upload
    usuario_id INT,                       -- Usuário que subiu o arquivo (opcional)
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE SET NULL
);

-- Tabela Perguntas_Respostas (para a assistente virtual)
CREATE TABLE Perguntas_Respostas (
    pergunta_resposta_id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta TEXT NOT NULL,               -- Pergunta comum
    resposta TEXT NOT NULL,               -- Resposta padrão
    categoria VARCHAR(50),                -- Categoria para organização
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP  -- Data de cadastro
);

-- Tabela Tarefas_Eventos (tarefas e eventos dos estudantes)
CREATE TABLE Tarefas_Eventos (
    tarefa_evento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,              -- Dono da tarefa/evento
    titulo VARCHAR(100) NOT NULL,         -- Título (ex: "Prova de Matemática")
    descricao TEXT,                       -- Detalhes
    data DATE NOT NULL,                   -- Data de ocorrência
    tipo ENUM('tarefa', 'evento') NOT NULL,  -- Tipo (tarefa ou evento)
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabela Planejamento_Estudos (rotina acadêmica dos estudantes)
CREATE TABLE Planejamento_Estudos (
    planejamento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,              -- Estudante dono do planejamento
    dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo') NOT NULL,  -- Dia da semana
    horario_inicio TIME NOT NULL,         -- Hora de início (ex: 09:00)
    horario_fim TIME NOT NULL,            -- Hora de término (ex: 11:00)
    atividade VARCHAR(100) NOT NULL,      -- Descrição da atividade
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Tabela Notificacoes (para lembretes e avisos automáticos)
CREATE TABLE Notificacoes (
    notificacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,              -- Destinatário
    tipo_notificacao ENUM('lembrete', 'aviso') NOT NULL,  -- Tipo
    mensagem TEXT NOT NULL,               -- Conteúdo da mensagem
    data_notificacao DATE NOT NULL,       -- Data de envio
    enviada BOOLEAN DEFAULT FALSE,        -- Indica se já foi enviada
    aviso_id INT NULL,                    -- Relacionamento com aviso (se aplicável)
    tarefa_evento_id INT NULL,            -- Relacionamento com tarefa/evento (se aplicável)
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (aviso_id) REFERENCES Avisos(aviso_id) ON DELETE SET NULL,
    FOREIGN KEY (tarefa_evento_id) REFERENCES Tarefas_Eventos(tarefa_evento_id) ON DELETE SET NULL
);

-- Tabela Interacoes_Assistente (histórico de interações com a assistente virtual)
CREATE TABLE Interacoes_Assistente (
    interacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,              -- Usuário que interagiu
    pergunta TEXT NOT NULL,               -- Pergunta feita
    resposta TEXT,                        -- Resposta recebida
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,  -- Data/hora da interação
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);