-- =============================================
-- Criação do Banco de Dados Facilitau
-- Banco de dados para sistema de gestão acadêmica
-- com funcionalidades para estudantes, professores e coordenadores
-- =============================================

-- Remove o banco de dados existente (se houver) para recriação limpa
DROP DATABASE IF EXISTS facilitau_db;

-- Cria o banco de dados com codificação UTF-8
CREATE DATABASE facilitau_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco de dados criado para uso
USE facilitau_db;

-- =============================================
-- Tabela: Usuarios
-- Armazena informações de todos os usuários do sistema
-- (estudantes, professores e coordenadores)
-- =============================================
CREATE TABLE Usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do usuário',
    email VARCHAR(100) UNIQUE NOT NULL COMMENT 'E-mail do usuário (também usado como login)',
    senha VARCHAR(255) NOT NULL COMMENT 'Senha criptografada do usuário',
    tipo ENUM('estudante', 'professor', 'coordenador') NOT NULL COMMENT 'Tipo de usuário (papel no sistema)',
    nome VARCHAR(100) NOT NULL COMMENT 'Nome completo do usuário',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro'
)COMMENT 'Tabela de usuários do sistema';

-- =============================================
-- Tabela: Avisos
-- Armazena avisos gerais e oportunidades acadêmicas
-- =============================================
CREATE TABLE Avisos (
    aviso_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do aviso',
    usuario_id INT NOT NULL COMMENT 'ID do usuário que criou o aviso (referência à tabela Usuarios)',
    tipo_aviso ENUM('aviso', 'oportunidade') DEFAULT 'aviso' COMMENT 'Tipo de publicação (aviso geral ou oportunidade)',
    titulo VARCHAR(100) NOT NULL COMMENT 'Título do aviso',
    descricao TEXT COMMENT 'Descrição detalhada do aviso',
    data_publicacao DATE NOT NULL COMMENT 'Data de publicação do aviso',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
)COMMENT 'Tabela de avisos e oportunidades acadêmicas';

-- =============================================
-- Tabela: Documentos
-- Armazena documentos institucionais para suporte à assistente virtual
-- =============================================
CREATE TABLE Documentos (
    documento_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do documento',
    nome VARCHAR(100) NOT NULL COMMENT 'Nome do documento',
    conteudo TEXT COMMENT 'Conteúdo/texto do documento',
    tipo ENUM('contrato', 'regulamento', 'outro') NOT NULL COMMENT 'Tipo/categoria do documento',
    data_upload DATE NOT NULL COMMENT 'Data de upload/carregamento do documento',
    usuario_id INT COMMENT 'ID do usuário que fez o upload (referência à tabela Usuarios)',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE SET NULL
)COMMENT 'Tabela de documentos institucionais';

-- =============================================
-- Tabela: Perguntas_Respostas
-- Armazena pares de perguntas e respostas para a assistente virtual
-- =============================================
CREATE TABLE Perguntas_Respostas (
    pergunta_resposta_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do par pergunta-resposta',
    pergunta TEXT NOT NULL COMMENT 'Texto da pergunta',
    resposta TEXT NOT NULL COMMENT 'Texto da resposta',
    categoria VARCHAR(50) COMMENT 'Categoria/tema da pergunta (para organização)',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro'
)COMMENT 'Base de conhecimento para a assistente virtual';

-- =============================================
-- Tabela: Tarefas_Eventos
-- Armazena tarefas e eventos dos estudantes
-- =============================================
CREATE TABLE Tarefas_Eventos (
    tarefa_evento_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único da tarefa/evento',
    usuario_id INT NOT NULL COMMENT 'ID do usuário dono da tarefa/evento (referência à tabela Usuarios)',
    titulo VARCHAR(100) NOT NULL COMMENT 'Título da tarefa/evento',
    descricao TEXT COMMENT 'Descrição detalhada da tarefa/evento',
    data DATE NOT NULL COMMENT 'Data da tarefa/evento',
    tipo ENUM('tarefa', 'evento') NOT NULL COMMENT 'Tipo do registro (tarefa ou evento)',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
)COMMENT 'Tabela de tarefas e eventos acadêmicos';

-- =============================================
-- Tabela: Planejamento_Estudos
-- Armazena a rotina acadêmica dos estudantes
-- =============================================
CREATE TABLE Planejamento_Estudos (
    planejamento_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do planejamento',
    usuario_id INT NOT NULL COMMENT 'ID do usuário dono do planejamento (referência à tabela Usuarios)',
    dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo') NOT NULL COMMENT 'Dia da semana para o planejamento',
    horario_inicio TIME NOT NULL COMMENT 'Horário de início da atividade',
    horario_fim TIME NOT NULL COMMENT 'Horário de término da atividade',
    atividade VARCHAR(100) NOT NULL COMMENT 'Descrição da atividade planejada',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
)COMMENT 'Tabela de planejamento de estudos dos estudantes';

-- =============================================
-- Tabela: Notificacoes
-- Armazena lembretes e avisos automáticos para os usuários
-- =============================================
CREATE TABLE Notificacoes (
    notificacao_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único da notificação',
    usuario_id INT NOT NULL COMMENT 'ID do usuário destinatário (referência à tabela Usuarios)',
    tipo_notificacao ENUM('lembrete', 'aviso') NOT NULL COMMENT 'Tipo de notificação',
    mensagem TEXT NOT NULL COMMENT 'Texto da mensagem de notificação',
    data_notificacao DATE NOT NULL COMMENT 'Data de envio/agendamento da notificação',
    enviada BOOLEAN DEFAULT FALSE COMMENT 'Indica se a notificação já foi enviada',
    aviso_id INT NULL COMMENT 'ID do aviso relacionado (se aplicável, referência à tabela Avisos)',
    tarefa_evento_id INT NULL COMMENT 'ID da tarefa/evento relacionado (se aplicável, referência à tabela Tarefas_Eventos)',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (aviso_id) REFERENCES Avisos(aviso_id) ON DELETE SET NULL,
    FOREIGN KEY (tarefa_evento_id) REFERENCES Tarefas_Eventos(tarefa_evento_id) ON DELETE SET NULL
)COMMENT 'Tabela de notificações do sistema';

-- =============================================
-- Tabela: Interacoes_Assistente
-- Armazena o histórico de interações dos usuários com a assistente virtual
-- =============================================
CREATE TABLE Interacoes_Assistente (
    interacao_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único da interação',
    usuario_id INT NOT NULL COMMENT 'ID do usuário que interagiu (referência à tabela Usuarios)',
    pergunta TEXT NOT NULL COMMENT 'Pergunta feita pelo usuário',
    resposta TEXT COMMENT 'Resposta fornecida pela assistente',
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da interação',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
)COMMENT 'Histórico de interações com a assistente virtual';

-- =============================================
-- Inserção de dados de teste para demonstração
-- =============================================

-- Inserção de usuários de teste (estudante, professor e coordenador)
INSERT INTO Usuarios (email, senha, tipo, nome) VALUES
('estudante1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'estudante', 'João Silva'),
('professor1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'professor', 'Maria Oliveira'),
('coordenador1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'coordenador', 'Carlos Souza');

-- Inserção de avisos de teste
INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) VALUES
(3, 'aviso', 'Reunião Geral', 'Reunião de coordenadores na próxima sexta-feira.', '2025-03-30'),
(2, 'oportunidade', 'Vaga de Estágio', 'Oportunidade de estágio em TI. Inscrições até 2025-04-05.', '2025-03-28');

-- Inserção de documentos de teste
INSERT INTO Documentos (nome, conteudo, tipo, data_upload, usuario_id) VALUES
('Contrato de Matrícula', 'Texto do contrato de matrícula...', 'contrato', '2025-03-01', 3),
('Regulamento Acadêmico', 'Regras gerais do curso...', 'regulamento', '2025-03-01', NULL);

-- Inserção de perguntas e respostas de teste para a assistente virtual
INSERT INTO Perguntas_Respostas (pergunta, resposta, categoria) VALUES
('Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.', 'documentação'),
('Qual a data da próxima prova?', 'Consulte sua agenda ou os avisos publicados.', 'calendário');

-- Inserção de tarefas e eventos de teste
INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo) VALUES
(1, 'Prova de Cálculo', 'Estudar capítulos 1 a 3.', '2025-04-01', 'tarefa'),
(1, 'Palestra de Carreira', 'Evento no auditório principal.', '2025-04-02', 'evento');

-- Inserção de planejamentos de estudo de teste
INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) VALUES
(1, 'segunda', '09:00:00', '11:00:00', 'Estudar Cálculo'),
(1, 'terca', '14:00:00', '16:00:00', 'Revisar Notas de Física');

-- Inserção de notificações de teste
INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id, tarefa_evento_id) VALUES
(1, 'lembrete', 'Lembrete: Prova de Cálculo em 2025-04-01', '2025-03-30', NULL, 1),
(1, 'aviso', 'Novo aviso: Reunião Geral', '2025-03-30', 1, NULL);

-- Inserção de interação com assistente de teste
INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta) VALUES
(1, 'Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.');

-- =============================================
-- Views do sistema
-- =============================================

-- View: AvisosComAutor
-- Mostra avisos com informações do autor (nome e tipo)
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

-- View: NotificacoesPendentes
-- Lista todas as notificações não enviadas
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

-- View: PlanejamentoPorEstudante
-- Mostra o planejamento de estudos organizado por estudante e dia da semana
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

-- View: TarefasEventosProximos
-- Lista tarefas e eventos dos próximos 3 dias
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

-- View: DocumentosPorTipo
-- Mostra documentos com informações do autor (se disponível)
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

-- View: InteracoesPorEstudante
-- Mostra interações com a assistente virtual ordenadas por data
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

-- View: UsuariosAtivos
-- Lista usuários criados nos últimos 30 dias
CREATE VIEW UsuariosAtivos AS
SELECT 
    u.usuario_id,
    u.email,
    u.tipo,
    u.nome,
    u.data_criacao
FROM Usuarios u
WHERE u.data_criacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);

-- View: AvisosPorTipo
-- Mostra estatísticas de avisos por tipo
CREATE VIEW AvisosPorTipo AS
SELECT 
    tipo_aviso,
    COUNT(*) AS total,
    MAX(data_publicacao) AS ultima_publicacao
FROM Avisos
GROUP BY tipo_aviso;

-- =============================================
-- Stored Procedures do sistema
-- =============================================

DELIMITER //

-- Procedure: InserirNotificacaoAviso
-- Cria notificações para todos os estudantes quando um novo aviso é publicado
CREATE PROCEDURE InserirNotificacaoAviso(
    IN p_aviso_id INT COMMENT 'ID do aviso que gerou a notificação',
    IN p_usuario_id INT COMMENT 'ID do usuário que publicou o aviso',
    IN p_titulo VARCHAR(100) COMMENT 'Título do aviso',
    IN p_data_publicacao DATE COMMENT 'Data de publicação do aviso'
)
BEGIN
    DECLARE v_mensagem TEXT;

    -- Cria a mensagem da notificação
    SET v_mensagem = CONCAT('Novo aviso: ', p_titulo);

    -- Insere notificações para todos os estudantes
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

-- Procedure: InserirNotificacaoLembrete
-- Cria lembretes para tarefas/eventos que ocorrerão no dia seguinte
CREATE PROCEDURE InserirNotificacaoLembrete()
BEGIN
    DECLARE v_data_amanha DATE;
    SET v_data_amanha = DATE_ADD(CURDATE(), INTERVAL 1 DAY);

    -- Insere notificações para tarefas/eventos do dia seguinte
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

-- Procedure: LimparNotificacoesAntigas
-- Remove notificações já enviadas com mais de 30 dias
CREATE PROCEDURE LimparNotificacoesAntigas()
BEGIN
    DELETE FROM Notificacoes
    WHERE enviada = TRUE
    AND data_notificacao < DATE_SUB(CURDATE(), INTERVAL 30 DAY);
END //

-- Procedure: AtualizarSenhaUsuario
-- Atualiza a senha de um usuário específico
CREATE PROCEDURE AtualizarSenhaUsuario(
    IN p_email VARCHAR(100) COMMENT 'E-mail do usuário',
    IN p_nova_senha VARCHAR(255) COMMENT 'Nova senha (já criptografada)'
)
BEGIN
    UPDATE Usuarios
    SET senha = p_nova_senha
    WHERE email = p_email;
END //

-- Procedure: CadastrarTarefaEvento
-- Cadastra uma nova tarefa ou evento e cria notificação se for para o dia seguinte
CREATE PROCEDURE CadastrarTarefaEvento(
    IN p_usuario_id INT COMMENT 'ID do usuário dono da tarefa/evento',
    IN p_titulo VARCHAR(100) COMMENT 'Título da tarefa/evento',
    IN p_descricao TEXT COMMENT 'Descrição detalhada',
    IN p_data DATE COMMENT 'Data da tarefa/evento',
    IN p_tipo ENUM('tarefa', 'evento') COMMENT 'Tipo do registro'
)
BEGIN
    DECLARE v_tarefa_evento_id INT;
    DECLARE v_data_amanha DATE;

    -- Insere a nova tarefa/evento
    INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo)
    VALUES (p_usuario_id, p_titulo, p_descricao, p_data, p_tipo);

    -- Obtém o ID da tarefa/evento recém-criada
    SET v_tarefa_evento_id = LAST_INSERT_ID();

    -- Se a tarefa/evento for para amanhã, cria uma notificação
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

-- Procedure: RegistrarInteracaoAssistente
-- Registra uma interação do usuário com a assistente virtual
CREATE PROCEDURE RegistrarInteracaoAssistente(
    IN p_usuario_id INT COMMENT 'ID do usuário que interagiu',
    IN p_pergunta TEXT COMMENT 'Pergunta feita pelo usuário',
    IN p_resposta TEXT COMMENT 'Resposta fornecida pela assistente'
)
BEGIN
    INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta)
    VALUES (p_usuario_id, p_pergunta, p_resposta);
END //

-- Procedure: MarcarNotificacaoEnviada
-- Marca uma notificação como já enviada
CREATE PROCEDURE MarcarNotificacaoEnviada(
    IN p_notificacao_id INT COMMENT 'ID da notificação a ser marcada'
)
BEGIN
    UPDATE Notificacoes
    SET enviada = TRUE
    WHERE notificacao_id = p_notificacao_id;
END //

-- Procedure: ExcluirUsuario
-- Remove um usuário e todos os seus dados relacionados (por cascade)
CREATE PROCEDURE ExcluirUsuario(
    IN p_usuario_id INT COMMENT 'ID do usuário a ser excluído'
)
BEGIN
    DELETE FROM Usuarios
    WHERE usuario_id = p_usuario_id;
END //

DELIMITER ;


CREATE TABLE IF NOT EXISTS Calendario (
    evento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_evento VARCHAR(50) NOT NULL, -- 'aviso', 'atividade', 'plano'
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    descricao TEXT,
    tags VARCHAR(255), -- Para compatibilidade inicial
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Evento_Tags (
    evento_id INT,
    tag_id INT,
    FOREIGN KEY (evento_id) REFERENCES Calendario(evento_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES Tags(tag_id) ON DELETE CASCADE,
    PRIMARY KEY (evento_id, tag_id)
);

-- Índices para melhorar desempenho
CREATE INDEX idx_calendario_usuario ON Calendario(usuario_id);
CREATE INDEX idx_calendario_data ON Calendario(data_inicio);