-- Exclui o banco de dados se ele já existir
DROP DATABASE IF EXISTS facilitau_db;

-- Cria o banco de dados com o nome facilitau_db
CREATE DATABASE facilitau_db;

-- Define o uso do banco recém-criado
USE facilitau_db;

-- Cria a tabela de cursos
CREATE TABLE Cursos (
    curso_id INT AUTO_INCREMENT PRIMARY KEY, -- ID único para cada curso
    nome VARCHAR(100) NOT NULL,              -- Nome do curso
    descricao TEXT,                          -- Descrição do curso
    duracao_semestres INT                    -- Duração em semestres
);

-- Cria a tabela de usuários (estudantes, professores, coordenadores)
CREATE TABLE Usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,         -- ID único do usuário
    email VARCHAR(100) UNIQUE NOT NULL,                -- Email do usuário
    senha VARCHAR(255) NOT NULL,                       -- Senha (criptografada)
    tipo ENUM('estudante', 'professor', 'coordenador') NOT NULL, -- Tipo de usuário
    nome VARCHAR(100) NOT NULL,                        -- Nome do usuário
    data_nascimento DATE,                              -- Data de nascimento
    telefone VARCHAR(20),                              -- Telefone
    curso_id INT,                                      -- Curso relacionado
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,   -- Data de criação do cadastro
    FOREIGN KEY (curso_id) REFERENCES Cursos(curso_id) ON DELETE SET NULL -- Relação com curso
);

-- Cria a tabela de avisos e oportunidades
CREATE TABLE Avisos (
    aviso_id INT AUTO_INCREMENT PRIMARY KEY,     -- ID do aviso
    usuario_id INT NOT NULL,                     -- Quem publicou o aviso
    tipo_aviso ENUM('aviso', 'oportunidade') DEFAULT 'aviso', -- Tipo
    titulo VARCHAR(100) NOT NULL,                -- Título do aviso
    descricao TEXT,                              -- Detalhes do aviso
    data_publicacao DATE NOT NULL,               -- Data de publicação
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Cria a tabela de documentos (para a assistente virtual)
CREATE TABLE Documentos (
    documento_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,            -- Nome do documento
    conteudo TEXT,                         -- Conteúdo do documento
    tipo ENUM('contrato', 'regulamento', 'outro') NOT NULL,
    data_upload DATE NOT NULL,             -- Data do envio
    usuario_id INT,                        -- Quem enviou (opcional)
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE SET NULL
);

-- Cria a tabela de perguntas e respostas (usada pela assistente)
CREATE TABLE Perguntas_Respostas (
    pergunta_resposta_id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta TEXT NOT NULL,              -- A pergunta
    resposta TEXT NOT NULL,              -- A resposta
    categoria VARCHAR(50),               -- Categoria da pergunta
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Cria a tabela de tarefas e eventos do estudante
CREATE TABLE Tarefas_Eventos (
    tarefa_evento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,              -- ID do aluno
    titulo VARCHAR(100) NOT NULL,         -- Título da tarefa/evento
    descricao TEXT,                       -- Descrição
    data DATE NOT NULL,                   -- Data do evento
    tipo ENUM('tarefa', 'evento') NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Cria a tabela de planejamento de estudos
CREATE TABLE Planejamento_Estudos (
    planejamento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo') NOT NULL,
    horario_inicio TIME NOT NULL,         -- Início da atividade
    horario_fim TIME NOT NULL,            -- Fim da atividade
    atividade VARCHAR(100) NOT NULL,      -- Nome da atividade
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Cria a tabela de notificações (avisos e lembretes)
CREATE TABLE Notificacoes (
    notificacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_notificacao ENUM('lembrete', 'aviso') NOT NULL,
    mensagem TEXT NOT NULL,
    data_notificacao DATE NOT NULL,
    enviada BOOLEAN DEFAULT FALSE,        -- Se a notificação já foi enviada
    aviso_id INT NULL,
    tarefa_evento_id INT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (aviso_id) REFERENCES Avisos(aviso_id) ON DELETE SET NULL,
    FOREIGN KEY (tarefa_evento_id) REFERENCES Tarefas_Eventos(tarefa_evento_id) ON DELETE SET NULL
);

-- Cria a tabela de interações com a assistente virtual
CREATE TABLE Interacoes_Assistente (
    interacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pergunta TEXT NOT NULL,
    resposta TEXT,
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

-- Inserindo dados iniciais nos cursos
INSERT INTO Cursos (nome, descricao, duracao_semestres) VALUES
('Engenharia de Software', 'Curso de graduação em Engenharia de Software', 8),
('Ciência da Computação', 'Curso de graduação em Ciência da Computação', 8),
('Sistema da Informação', 'Curso de graduação em Sistema da informação', 8);

-- Inserindo usuários de exemplo
INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id) VALUES
('estudante1@facilitau.com', '<senha_hash>', 'estudante', 'João Silva', '2000-05-15', '(11) 99999-9999', 1),
('professor1@facilitau.com', '<senha_hash>', 'professor', 'Maria Oliveira', '1985-10-22', '(11) 98888-8888', 2),
('coordenador1@facilitau.com', '<senha_hash>', 'coordenador', 'Carlos Souza', '1978-03-30', '(11) 97777-7777', 3);

-- Inserindo avisos
INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) VALUES
(3, 'aviso', 'Reunião Geral', 'Reunião de coordenadores na próxima sexta-feira.', '2025-03-30'),
(2, 'oportunidade', 'Vaga de Estágio', 'Oportunidade de estágio em TI. Inscrições até 2025-04-05.', '2025-03-28');

-- Inserindo documentos
INSERT INTO Documentos (nome, conteudo, tipo, data_upload, usuario_id) VALUES
('Contrato de Matrícula', 'Texto do contrato de matrícula...', 'contrato', '2025-03-01', 3),
('Regulamento Acadêmico', 'Regras gerais do curso...', 'regulamento', '2025-03-01', NULL);

-- Inserindo perguntas e respostas
INSERT INTO Perguntas_Respostas (pergunta, resposta, categoria) VALUES
('Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.', 'documentação'),
('Qual a data da próxima prova?', 'Consulte sua agenda ou os avisos publicados.', 'calendário');

-- Inserindo tarefas e eventos
INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo) VALUES
(1, 'Prova de Cálculo', 'Estudar capítulos 1 a 3.', '2025-04-01', 'tarefa'),
(1, 'Palestra de Carreira', 'Evento no auditório principal.', '2025-04-02', 'evento');

-- Inserindo planejamento de estudos
INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) VALUES
(1, 'segunda', '09:00:00', '11:00:00', 'Estudar Cálculo'),
(1, 'terca', '14:00:00', '16:00:00', 'Revisar Notas de Física');

-- Inserindo notificações
INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id, tarefa_evento_id) VALUES
(1, 'lembrete', 'Lembrete: Prova de Cálculo em 2025-04-01', '2025-03-30', NULL, 1),
(1, 'aviso', 'Novo aviso: Reunião Geral', '2025-03-30', 1, NULL);

-- Inserindo interação com a assistente
INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta) VALUES
(1, 'Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.');

-- ========================
--     CRIAÇÃO DE VIEWS
-- ========================

-- Avisos com o nome e tipo do autor
CREATE VIEW AvisosComAutor AS
SELECT a.aviso_id, a.tipo_aviso, a.titulo, a.descricao, a.data_publicacao,
       u.nome AS autor, u.tipo AS tipo_autor
FROM Avisos a
JOIN Usuarios u ON a.usuario_id = u.usuario_id;

-- Notificações que ainda não foram enviadas
CREATE VIEW NotificacoesPendentes AS
SELECT n.notificacao_id, n.usuario_id, u.nome AS nome_estudante, n.tipo_notificacao,
       n.mensagem, n.data_notificacao, n.aviso_id, n.tarefa_evento_id
FROM Notificacoes n
JOIN Usuarios u ON n.usuario_id = u.usuario_id
WHERE n.enviada = FALSE;

-- Planejamento de estudos por estudante, ordenado por dia e hora
CREATE VIEW PlanejamentoPorEstudante AS
SELECT p.planejamento_id, p.usuario_id, u.nome AS nome_estudante,
       p.dia_semana, p.horario_inicio, p.horario_fim, p.atividade
FROM Planejamento_Estudos p
JOIN Usuarios u ON p.usuario_id = u.usuario_id
ORDER BY u.nome,
         FIELD(p.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'),
         p.horario_inicio;

-- Tarefas e eventos próximos (nos próximos 3 dias)
CREATE VIEW TarefasEventosProximos AS
SELECT te.tarefa_evento_id, te.usuario_id, u.nome AS nome_estudante,
       te.titulo, te.descricao, te.data, te.tipo
FROM Tarefas_Eventos te
JOIN Usuarios u ON te.usuario_id = u.usuario_id
WHERE te.data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY);

-- Lista de documentos com nome do autor
CREATE VIEW DocumentosPorTipo AS
SELECT d.documento_id, d.nome, d.conteudo, d.tipo, d.data_upload, u.nome AS autor
FROM Documentos d
LEFT JOIN Usuarios u ON d.usuario_id = u.usuario_id;

-- Histórico de interações com a assistente por estudante
CREATE VIEW InteracoesPorEstudante AS
SELECT i.interacao_id, i.usuario_id, u.nome AS nome_estudante,
       i.pergunta, i.resposta, i.data_interacao
FROM Interacoes_Assistente i
JOIN Usuarios u ON i.usuario_id = u.usuario_id
ORDER BY i.data_interacao DESC;

-- Usuários cadastrados nos últimos 30 dias
CREATE VIEW UsuariosAtivos AS
SELECT u.usuario_id, u.email, u.tipo, u.nome, u.data_criacao
FROM Usuarios u
WHERE u.data_criacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);

-- Contagem de avisos por tipo
CREATE VIEW AvisosPorTipo AS
SELECT tipo_aviso, COUNT(*) AS total, MAX(data_publicacao) AS ultima_publicacao
FROM Avisos
GROUP BY tipo_aviso;

-- =============================
--     CRIAÇÃO DE PROCEDURES
-- =============================

DELIMITER //

-- Insere uma notificação para todos os estudantes quando um aviso é criado
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
    SELECT u.usuario_id, 'aviso', v_mensagem, p_data_publicacao, FALSE, p_aviso_id
    FROM Usuarios u
    WHERE u.tipo = 'estudante';
END //

-- Insere lembretes automáticos para tarefas com data no dia seguinte
CREATE PROCEDURE InserirNotificacaoLembrete()
BEGIN
    DECLARE v_data_amanha DATE;
    SET v_data_amanha = DATE_ADD(CURDATE(), INTERVAL 1 DAY);

    INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, enviada, tarefa_evento_id)
    SELECT te.usuario_id, 'lembrete', CONCAT('Lembrete: ', te.titulo, ' em ', te.data),
           CURDATE(), FALSE, te.tarefa_evento_id
    FROM Tarefas_Eventos te
    WHERE te.data = v_data_amanha;
END //

-- Apaga notificações antigas já enviadas (mais de 30 dias)
CREATE PROCEDURE LimparNotificacoesAntigas()
BEGIN
    DELETE FROM Notificacoes
    WHERE enviada = TRUE
      AND data_notificacao < DATE_SUB(CURDATE(), INTERVAL 30 DAY);
END //

-- Atualiza a senha de um usuário
CREATE PROCEDURE AtualizarSenhaUsuario(
    IN p_email VARCHAR(100),
    IN p_nova_senha VARCHAR(255)
)
BEGIN
    UPDATE Usuarios
    SET senha = p_nova_senha
    WHERE email = p_email;
END //

-- Cadastra tarefa/evento e gera lembrete se for para o dia seguinte
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
        VALUES (p_usuario_id, 'lembrete', CONCAT('Lembrete: ', p_titulo, ' em ', p_data), CURDATE(), FALSE, v_tarefa_evento_id);
    END IF;
END //

-- Registra uma pergunta feita à assistente
CREATE PROCEDURE RegistrarInteracaoAssistente(
    IN p_usuario_id INT,
    IN p_pergunta TEXT,
    IN p_resposta TEXT
)
BEGIN
    INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta)
    VALUES (p_usuario_id, p_pergunta, p_resposta);
END //

-- Marca notificação como enviada
CREATE PROCEDURE MarcarNotificacaoEnviada(
    IN p_notificacao_id INT
)
BEGIN
    UPDATE Notificacoes
    SET enviada = TRUE
    WHERE notificacao_id = p_notificacao_id;
END //

-- Exclui um usuário do sistema
CREATE PROCEDURE ExcluirUsuario(
    IN p_usuario_id INT
)
BEGIN
    DELETE FROM Usuarios
    WHERE usuario_id = p_usuario_id;
END //

DELIMITER ;
