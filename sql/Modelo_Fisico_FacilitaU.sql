-- Criar o banco de dados
DROP DATABASE IF EXISTS facilitau_db;
CREATE DATABASE facilitau_db;
USE facilitau_db;

-- Tabela Cursos
CREATE TABLE Cursos (
    curso_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    duracao_semestres INT
);

-- Tabela Usuarios (estudantes, professores e coordenadores)
CREATE TABLE Usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('estudante', 'professor', 'coordenador') NOT NULL,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE,
    telefone VARCHAR(20),
    curso_id INT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES Cursos(curso_id) ON DELETE SET NULL
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

-- Inserir dados de teste
INSERT INTO Cursos (nome, descricao, duracao_semestres) VALUES
('Engenharia de Software', 'Curso de graduação em Engenharia de Software', 8),
('Ciência da Computação', 'Curso de graduação em Ciência da Computação', 8),
('Administração', 'Curso de graduação em Administração', 8);

INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id) VALUES
('estudante1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'estudante', 'João Silva', '2000-05-15', '(11) 99999-9999', 1),
('professor1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'professor', 'Maria Oliveira', '1985-10-22', '(11) 98888-8888', 2),
('coordenador1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'coordenador', 'Carlos Souza', '1978-03-30', '(11) 97777-7777', 3);

INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) VALUES
(3, 'aviso', 'Reunião Geral', 'Reunião de coordenadores na próxima sexta-feira.', '2025-03-30'),
(2, 'oportunidade', 'Vaga de Estágio', 'Oportunidade de estágio em TI. Inscrições até 2025-04-05.', '2025-03-28');

INSERT INTO Documentos (nome, conteudo, tipo, data_upload, usuario_id) VALUES
('Contrato de Matrícula', 'Texto do contrato de matrícula...', 'contrato', '2025-03-01', 3),
('Regulamento Acadêmico', 'Regras gerais do curso...', 'regulamento', '2025-03-01', NULL);

INSERT INTO Perguntas_Respostas (pergunta, resposta, categoria) VALUES
('Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.', 'documentação'),
('Qual a data da próxima prova?', 'Consulte sua agenda ou os avisos publicados.', 'calendário');

INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo) VALUES
(1, 'Prova de Cálculo', 'Estudar capítulos 1 a 3.', '2025-04-01', 'tarefa'),
(1, 'Palestra de Carreira', 'Evento no auditório principal.', '2025-04-02', 'evento');

INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) VALUES
(1, 'segunda', '09:00:00', '11:00:00', 'Estudar Cálculo'),
(1, 'terca', '14:00:00', '16:00:00', 'Revisar Notas de Física');

INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id, tarefa_evento_id) VALUES
(1, 'lembrete', 'Lembrete: Prova de Cálculo em 2025-04-01', '2025-03-30', NULL, 1),
(1, 'aviso', 'Novo aviso: Reunião Geral', '2025-03-30', 1, NULL);

INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta) VALUES
(1, 'Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.');

-- Criar Views
CREATE VIEW AvisosComAutor AS
SELECT 
    a.aviso_id,
    a.tipo_aviso,
    a.titulo,
    a.descricao,
    a.data_publicacao,
    u.nome AS autor,
    u.tipo AS tipo_autor
FROM Avisos a
JOIN Usuarios u ON a.usuario_id = u.usuario_id;

CREATE VIEW NotificacoesPendentes AS
SELECT 
    n.notificacao_id,
    n.usuario_id,
    u.nome AS nome_estudante,
    n.tipo_notificacao,
    n.mensagem,
    n.data_notificacao,
    n.aviso_id,
    n.tarefa_evento_id
FROM Notificacoes n
JOIN Usuarios u ON n.usuario_id = u.usuario_id
WHERE n.enviada = FALSE;

CREATE VIEW PlanejamentoPorEstudante AS
SELECT 
    p.planejamento_id,
    p.usuario_id,
    u.nome AS nome_estudante,
    p.dia_semana,
    p.horario_inicio,
    p.horario_fim,
    p.atividade
FROM Planejamento_Estudos p
JOIN Usuarios u ON p.usuario_id = u.usuario_id
ORDER BY 
    u.nome,
    FIELD(p.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'),
    p.horario_inicio;

CREATE VIEW TarefasEventosProximos AS
SELECT 
    te.tarefa_evento_id,
    te.usuario_id,
    u.nome AS nome_estudante,
    te.titulo,
    te.descricao,
    te.data,
    te.tipo
FROM Tarefas_Eventos te
JOIN Usuarios u ON te.usuario_id = u.usuario_id
WHERE te.data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY);

CREATE VIEW DocumentosPorTipo AS
SELECT 
    d.documento_id,
    d.nome,
    d.conteudo,
    d.tipo,
    d.data_upload,
    u.nome AS autor
FROM Documentos d
LEFT JOIN Usuarios u ON d.usuario_id = u.usuario_id;

CREATE VIEW InteracoesPorEstudante AS
SELECT 
    i.interacao_id,
    i.usuario_id,
    u.nome AS nome_estudante,
    i.pergunta,
    i.resposta,
    i.data_interacao
FROM Interacoes_Assistente i
JOIN Usuarios u ON i.usuario_id = u.usuario_id
ORDER BY i.data_interacao DESC;

CREATE VIEW UsuariosAtivos AS
SELECT 
    u.usuario_id,
    u.email,
    u.tipo,
    u.nome,
    u.data_criacao
FROM Usuarios u
WHERE u.data_criacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);

CREATE VIEW AvisosPorTipo AS
SELECT 
    tipo_aviso,
    COUNT(*) AS total,
    MAX(data_publicacao) AS ultima_publicacao
FROM Avisos
GROUP BY tipo_aviso;

-- Criar Procedures
DELIMITER //

CREATE PROCEDURE InserirNotificacaoAviso(
    IN p_aviso_id INT,
    IN p_usuario_id INT,
    IN p_titulo VARCHAR(100),
    IN p_data_publicacao DATE
)
BEGIN
    DECLARE v_mensagem TEXT;

    SET v_mensagem = CONCAT('Novo aviso: ', p_titulo);

    INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, enviada, aviso_id)
    SELECT 
        u.usuario_id,
        'aviso',
        v_mensagem,
        p_data_publicacao,
        FALSE,
        p_aviso_id
    FROM Usuarios u
    WHERE u.tipo = 'estudante';
END //

CREATE PROCEDURE InserirNotificacaoLembrete()
BEGIN
    DECLARE v_data_amanha DATE;
    SET v_data_amanha = DATE_ADD(CURDATE(), INTERVAL 1 DAY);

    INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, enviada, tarefa_evento_id)
    SELECT 
        te.usuario_id,
        'lembrete',
        CONCAT('Lembrete: ', te.titulo, ' em ', te.data),
        CURDATE(),
        FALSE,
        te.tarefa_evento_id
    FROM Tarefas_Eventos te
    WHERE te.data = v_data_amanha;
END //

CREATE PROCEDURE LimparNotificacoesAntigas()
BEGIN
    DELETE FROM Notificacoes
    WHERE enviada = TRUE
    AND data_notificacao < DATE_SUB(CURDATE(), INTERVAL 30 DAY);
END //

CREATE PROCEDURE AtualizarSenhaUsuario(
    IN p_email VARCHAR(100),
    IN p_nova_senha VARCHAR(255)
)
BEGIN
    UPDATE Usuarios
    SET senha = p_nova_senha
    WHERE email = p_email;
END //

CREATE PROCEDURE CadastrarTarefaEvento(
    IN p_usuario_id INT,
    IN p_titulo VARCHAR(100),
    IN p_descricao TEXT,
    IN p_data DATE,
    IN p_tipo ENUM('tarefa', 'evento')
)
BEGIN
    DECLARE v_tarefa_evento_id INT;
    DECLARE v_data_amanha DATE;

    INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo)
    VALUES (p_usuario_id, p_titulo, p_descricao, p_data, p_tipo);

    SET v_tarefa_evento_id = LAST_INSERT_ID();

    SET v_data_amanha = DATE_ADD(CURDATE(), INTERVAL 1 DAY);
    IF p_data = v_data_amanha THEN
        INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, enviada, tarefa_evento_id)
        VALUES (
            p_usuario_id,
            'lembrete',
            CONCAT('Lembrete: ', p_titulo, ' em ', p_data),
            CURDATE(),
            FALSE,
            v_tarefa_evento_id
        );
    END IF;
END //

CREATE PROCEDURE RegistrarInteracaoAssistente(
    IN p_usuario_id INT,
    IN p_pergunta TEXT,
    IN p_resposta TEXT
)
BEGIN
    INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta)
    VALUES (p_usuario_id, p_pergunta, p_resposta);
END //

CREATE PROCEDURE MarcarNotificacaoEnviada(
    IN p_notificacao_id INT
)
BEGIN
    UPDATE Notificacoes
    SET enviada = TRUE
    WHERE notificacao_id = p_notificacao_id;
END //

CREATE PROCEDURE ExcluirUsuario(
    IN p_usuario_id INT
)
BEGIN
    DELETE FROM Usuarios
    WHERE usuario_id = p_usuario_id;
END //

DELIMITER ;