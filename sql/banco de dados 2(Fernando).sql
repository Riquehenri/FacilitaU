CREATE DATABASE FacilitaU;
USE FacilitaU;
-- Tabela de Usuários
CREATE TABLE Usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL COMMENT 'Nome completo do usuário',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'E-mail do usuário',
    senha VARCHAR(255) NOT NULL COMMENT 'Senha criptografada',
    tipo ENUM('estudante', 'professor', 'coordenador') NOT NULL COMMENT 'Tipo de usuário',
    curso VARCHAR(50) COMMENT 'Curso do estudante (se aplicável)',
    periodo INT COMMENT 'Período do estudante (se aplicável)',
    turma VARCHAR(10) COMMENT 'Turma do estudante (se aplicável)',
    codigo_2fa VARCHAR(6) NULL COMMENT 'Código temporário para 2FA',
    codigo_2fa_expiracao DATETIME NULL COMMENT 'Data de expiração do código 2FA',
    onesignal_player_id VARCHAR(255) NULL COMMENT 'ID do usuário no OneSignal para notificações push',
    tema ENUM('claro', 'escuro') DEFAULT 'claro' COMMENT 'Tema preferido do usuário'
) COMMENT 'Tabela de usuários do sistema (estudantes, professores, coordenadores)';

-- Tabela de Planejamento de Estudos
CREATE TABLE Planejamento_Estudos (
    planejamento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    dia_semana ENUM('segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado', 'domingo') NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    atividade TEXT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Planejamentos de estudos dos estudantes';

-- Tabela de Avisos
CREATE TABLE Avisos (
    aviso_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_aviso ENUM('aviso', 'oportunidade') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    data_publicacao DATE NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Avisos e oportunidades criados por professores e coordenadores';

-- Tabela de Tags
CREATE TABLE Tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    nome_tag VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nome da tag (ex.: curso, período, turma)'
) COMMENT 'Tabela de tags para segmentar avisos e usuários';

-- Tabela de Relacionamento Avisos_Tags
CREATE TABLE Avisos_Tags (
    aviso_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (aviso_id, tag_id),
    FOREIGN KEY (aviso_id) REFERENCES Avisos(aviso_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES Tags(tag_id) ON DELETE CASCADE
) COMMENT 'Relacionamento entre avisos e tags';

-- Tabela de Relacionamento Usuarios_Tags
CREATE TABLE Usuarios_Tags (
    usuario_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (usuario_id, tag_id),
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES Tags(tag_id) ON DELETE CASCADE
) COMMENT 'Relacionamento entre usuários e tags';

-- Tabela de Notificações
CREATE TABLE Notificacoes (
    notificacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_notificacao ENUM('aviso', 'lembrete') NOT NULL,
    mensagem TEXT NOT NULL,
    data_notificacao DATETIME NOT NULL,
    enviada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Notificações enviadas aos usuários';

-- Tabela de Tarefas e Eventos
CREATE TABLE Tarefas_Eventos (
    tarefa_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('tarefa', 'aviso') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    data DATE NOT NULL,
    repeticao ENUM('nenhuma', 'diaria', 'semanal') DEFAULT 'nenhuma',
    lembrete ENUM('nenhum', '1_hora_antes', '1_dia_antes') DEFAULT 'nenhum' COMMENT 'Configuração de lembrete para a tarefa',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Tarefas e eventos do calendário';

-- Tabela de Documentos
CREATE TABLE Documentos (
    documento_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_documento ENUM('material', 'ata', 'outro') NOT NULL,
    caminho VARCHAR(255) NOT NULL,
    data_upload DATETIME NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Documentos enviados pelos usuários (PDFs, imagens)';

-- Tabela de Perguntas e Respostas (Assistente Virtual)
CREATE TABLE Perguntas_Respostas (
    pergunta_id INT AUTO_INCREMENT PRIMARY KEY,
    keywords TEXT NOT NULL COMMENT 'Palavras-chave para busca',
    resposta TEXT NOT NULL COMMENT 'Resposta correspondente'
) COMMENT 'Base de conhecimento do assistente virtual';

-- Tabela de Interações com o Assistente
CREATE TABLE Interacoes_Assistente (
    interacao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pergunta TEXT NOT NULL,
    resposta TEXT,
    data_interacao DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Histórico de interações com o assistente virtual';

-- Tabela de Gamificação
CREATE TABLE Gamificacao (
    usuario_id INT PRIMARY KEY,
    pontos INT DEFAULT 0 COMMENT 'Pontos acumulados pelo estudante',
    badges TEXT COMMENT 'Lista de badges (ex.: Dedicado, Mestre) separados por vírgula',
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Tabela para gamificação dos estudantes';

-- Tabela de Mensagens (Chat)
CREATE TABLE Mensagens (
    mensagem_id INT AUTO_INCREMENT PRIMARY KEY,
    remetente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME NOT NULL,
    FOREIGN KEY (remetente_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Mensagens do chat em tempo real';

-- Tabela de Feedback
CREATE TABLE Feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nota INT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    data_envio DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id) ON DELETE CASCADE
) COMMENT 'Feedback dos usuários sobre o sistema';  


-- Índices para Usuarios
CREATE INDEX idx_usuario_email ON Usuarios(email);
CREATE INDEX idx_usuario_tipo ON Usuarios(tipo);

-- Índices para Planejamento_Estudos
CREATE INDEX idx_planejamento_usuario ON Planejamento_Estudos(usuario_id);

-- Índices para Avisos
CREATE INDEX idx_aviso_usuario ON Avisos(usuario_id);
CREATE INDEX idx_aviso_data ON Avisos(data_publicacao);

-- Índices para Notificacoes
CREATE INDEX idx_notificacao_usuario ON Notificacoes(usuario_id);

-- Índices para Tarefas_Eventos
CREATE INDEX idx_tarefa_usuario ON Tarefas_Eventos(usuario_id);
CREATE INDEX idx_tarefa_data ON Tarefas_Eventos(data);



-- View para Avisos com Autor
CREATE VIEW AvisosComAutor AS
SELECT a.*, u.nome AS autor
FROM Avisos a
JOIN Usuarios u ON a.usuario_id = u.usuario_id;

-- View para Notificações Pendentes
CREATE VIEW NotificacoesPendentes AS
SELECT usuario_id, COUNT(*) AS total_pendentes
FROM Notificacoes
WHERE enviada = FALSE
GROUP BY usuario_id;

-- View para Planejamento por Estudante
CREATE VIEW PlanejamentoPorEstudante AS
SELECT p.*, u.nome AS estudante
FROM Planejamento_Estudos p
JOIN Usuarios u ON p.usuario_id = u.usuario_id;

-- View para Tarefas e Eventos Próximos
CREATE VIEW TarefasEventosProximos AS
SELECT * FROM Tarefas_Eventos
WHERE data >= CURDATE()
ORDER BY data;

-- View para Documentos por Tipo
CREATE VIEW DocumentosPorTipo AS
SELECT tipo_documento, COUNT(*) AS total
FROM Documentos
GROUP BY tipo_documento;

-- View para Interações por Estudante
CREATE VIEW InteracoesPorEstudante AS
SELECT i.*, u.nome AS estudante
FROM Interacoes_Assistente i
JOIN Usuarios u ON i.usuario_id = u.usuario_id;

-- View para Usuários Ativos
CREATE VIEW UsuariosAtivos AS
SELECT tipo, COUNT(*) AS total
FROM Usuarios
GROUP BY tipo;

-- View para Avisos por Tipo
CREATE VIEW AvisosPorTipo AS
SELECT tipo_aviso, COUNT(*) AS total
FROM Avisos
GROUP BY tipo_aviso;




DELIMITER //

-- Procedure para Inserir Notificação de Aviso
CREATE PROCEDURE InserirNotificacaoAviso(
    IN p_aviso_id INT,
    IN p_usuario_id INT,
    IN p_titulo VARCHAR(200),
    IN p_data DATE
)
BEGIN
    DECLARE v_mensagem TEXT;
    SET v_mensagem = CONCAT('Novo aviso: ', p_titulo, ' publicado em ', p_data);

    INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao)
    SELECT ut.usuario_id, 'aviso', v_mensagem, NOW()
    FROM Usuarios_Tags ut
    JOIN Avisos_Tags at ON ut.tag_id = at.tag_id
    WHERE at.aviso_id = p_aviso_id;
END //

-- Procedure para Inserir Notificação de Lembrete
CREATE PROCEDURE InserirNotificacaoLembrete(
    IN p_usuario_id INT,
    IN p_tarefa_id INT,
    IN p_titulo VARCHAR(200)
)
BEGIN
    DECLARE v_mensagem TEXT;
    SET v_mensagem = CONCAT('Lembrete: ', p_titulo, ' está chegando!');

    INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao)
    VALUES (p_usuario_id, 'lembrete', v_mensagem, NOW());
END //

-- Procedure para Limpar Notificações Antigas
CREATE PROCEDURE LimparNotificacoesAntigas()
BEGIN
    DELETE FROM Notificacoes
    WHERE data_notificacao < DATE_SUB(NOW(), INTERVAL 30 DAY);
END //

-- Procedure para Atualizar Senha
CREATE PROCEDURE AtualizarSenhaUsuario(
    IN p_usuario_id INT,
    IN p_nova_senha VARCHAR(255)
)
BEGIN
    UPDATE Usuarios
    SET senha = p_nova_senha
    WHERE usuario_id = p_usuario_id;
END //

-- Procedure para Cadastrar Tarefa ou Evento
CREATE PROCEDURE CadastrarTarefaEvento(
    IN p_usuario_id INT,
    IN p_titulo VARCHAR(200),
    IN p_descricao TEXT,
    IN p_data DATE,
    IN p_tipo ENUM('tarefa', 'aviso'),
    IN p_repeticao ENUM('nenhuma', 'diaria', 'semanal'),
    IN p_lembrete ENUM('nenhum', '1_hora_antes', '1_dia_antes')
)
BEGIN
    INSERT INTO Tarefas_Eventos (usuario_id, tipo, titulo, descricao, data, repeticao, lembrete)
    VALUES (p_usuario_id, p_tipo, p_titulo, p_descricao, p_data, p_repeticao, p_lembrete);
END //

-- Procedure para Registrar Interação com Assistente
CREATE PROCEDURE RegistrarInteracaoAssistente(
    IN p_usuario_id INT,
    IN p_pergunta TEXT,
    IN p_resposta TEXT
)
BEGIN
    INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta, data_interacao)
    VALUES (p_usuario_id, p_pergunta, p_resposta, NOW());
END //

-- Procedure para Marcar Notificação como Enviada
CREATE PROCEDURE MarcarNotificacaoEnviada(
    IN p_notificacao_id INT
)
BEGIN
    UPDATE Notificacoes
    SET enviada = TRUE
    WHERE notificacao_id = p_notificacao_id;
END //

-- Procedure para Excluir Usuário
CREATE PROCEDURE ExcluirUsuario(
    IN p_usuario_id INT
)
BEGIN
    DELETE FROM Usuarios WHERE usuario_id = p_usuario_id;
END //

-- Procedure para Atualizar Gamificação
CREATE PROCEDURE AtualizarGamificacao(
    IN p_usuario_id INT,
    IN p_pontos INT
)
BEGIN
    DECLARE v_pontos_atual INT;
    DECLARE v_badges_atual TEXT;

    SELECT pontos, badges INTO v_pontos_atual, v_badges_atual 
    FROM Gamificacao 
    WHERE usuario_id = p_usuario_id;

    IF v_pontos_atual IS NULL THEN
        INSERT INTO Gamificacao (usuario_id, pontos) VALUES (p_usuario_id, p_pontos);
        SET v_pontos_atual = p_pontos;
        SET v_badges_atual = '';
    ELSE
        UPDATE Gamificacao 
        SET pontos = pontos + p_pontos 
        WHERE usuario_id = p_usuario_id;
        SET v_pontos_atual = v_pontos_atual + p_pontos;
    END IF;

    SET v_badges_atual = IFNULL(v_badges_atual, '');
    IF v_pontos_atual >= 50 AND NOT FIND_IN_SET('Dedicado', v_badges_atual) THEN
        SET v_badges_atual = CONCAT(v_badges_atual, IF(v_badges_atual = '', '', ','), 'Dedicado');
    END IF;
    IF v_pontos_atual >= 100 AND NOT FIND_IN_SET('Mestre', v_badges_atual) THEN
        SET v_badges_atual = CONCAT(v_badges_atual, IF(v_badges_atual = '', '', ','), 'Mestre');
    END IF;

    UPDATE Gamificacao 
    SET badges = v_badges_atual 
    WHERE usuario_id = p_usuario_id;
END //

-- Procedure para Backup de Dados
CREATE PROCEDURE BackupDados()
BEGIN
    SET @sql = 'SELECT * INTO OUTFILE \'C:/xampp/htdocs/facilitau/FacilitaU/Codigo/backups/backup_' 
               + DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s') + '.sql\' 
               FROM (SELECT * FROM Usuarios 
                     UNION ALL SELECT * FROM Avisos 
                     UNION ALL SELECT * FROM Planejamento_Estudos 
                     UNION ALL SELECT * FROM Notificacoes 
                     UNION ALL SELECT * FROM Tarefas_Eventos 
                     UNION ALL SELECT * FROM Documentos 
                     UNION ALL SELECT * FROM Interacoes_Assistente 
                     UNION ALL SELECT * FROM Tags 
                     UNION ALL SELECT * FROM Avisos_Tags 
                     UNION ALL SELECT * FROM Usuarios_Tags 
                     UNION ALL SELECT * FROM Mensagens 
                     UNION ALL SELECT * FROM Gamificacao 
                     UNION ALL SELECT * FROM Feedback) AS backup';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //

DELIMITER ;




-- Inserir Usuários Iniciais
INSERT INTO Usuarios (nome, email, senha, tipo, curso, periodo, turma, tema) VALUES
('João Silva', 'joao@facilitau.com', '$2y$10$X1gXh2bL5Y4xJ3jZ7qW8vO8gYkT9nL2mP5rQ8tU3vW6xY9zA1bC.', 'estudante', 'Engenharia', 3, 'A', 'claro'),
('Maria Oliveira', 'maria@facilitau.com', '$2y$10$X1gXh2bL5Y4xJ3jZ7qW8vO8gYkT9nL2mP5rQ8tU3vW6xY9zA1bC.', 'professor', NULL, NULL, NULL, 'claro'),
('Ana Costa', 'ana@facilitau.com', '$2y$10$X1gXh2bL5Y4xJ3jZ7qW8vO8gYkT9nL2mP5rQ8tU3vW6xY9zA1bC.', 'coordenador', NULL, NULL, NULL, 'claro');

-- Senha padrão pra todos: "senha123" (já criptografada com password_hash)

-- Inserir Tags
INSERT INTO Tags (nome_tag) VALUES
('Engenharia'), ('3º Período'), ('Turma A');

-- Associar Tags aos Usuários
INSERT INTO Usuarios_Tags (usuario_id, tag_id) VALUES
(1, 1), (1, 2), (1, 3);

-- Inserir Perguntas e Respostas pro Assistente
INSERT INTO Perguntas_Respostas (keywords, resposta) VALUES
('horário aula, cronograma', 'Você pode ver o horário das suas aulas no menu de Planejamento de Estudos ou no Calendário.'),
('como criar tarefa', 'Para criar uma tarefa, vá até o Calendário, selecione um dia e clique em "Adicionar Tarefa".');