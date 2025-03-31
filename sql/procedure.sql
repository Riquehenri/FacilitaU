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

DELIMITER ;


DELIMITER //

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

DELIMITER ;


DELIMITER //

CREATE PROCEDURE LimparNotificacoesAntigas()
BEGIN
    DELETE FROM Notificacoes
    WHERE enviada = TRUE
    AND data_notificacao < DATE_SUB(CURDATE(), INTERVAL 30 DAY);
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE AtualizarSenhaUsuario(
    IN p_email VARCHAR(100),
    IN p_nova_senha VARCHAR(255)
)
BEGIN
    UPDATE Usuarios
    SET senha = p_nova_senha
    WHERE email = p_email;
END //

DELIMITER ;


DELIMITER //

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

    -- Inserir a tarefa/evento
    INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo)
    VALUES (p_usuario_id, p_titulo, p_descricao, p_data, p_tipo);

    -- Obter o ID da tarefa/evento inserida
    SET v_tarefa_evento_id = LAST_INSERT_ID();

    -- Verificar se a data é amanhã para criar um lembrete
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

DELIMITER ;


DELIMITER //

CREATE PROCEDURE RegistrarInteracaoAssistente(
    IN p_usuario_id INT,
    IN p_pergunta TEXT,
    IN p_resposta TEXT
)
BEGIN
    INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta)
    VALUES (p_usuario_id, p_pergunta, p_resposta);
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE MarcarNotificacaoEnviada(
    IN p_notificacao_id INT
)
BEGIN
    UPDATE Notificacoes
    SET enviada = TRUE
    WHERE notificacao_id = p_notificacao_id;
END //

DELIMITER ;



DELIMITER //

CREATE PROCEDURE ExcluirUsuario(
    IN p_usuario_id INT
)
BEGIN
    DELETE FROM Usuarios
    WHERE usuario_id = p_usuario_id;
END //

DELIMITER ;