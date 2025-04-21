-- =============================================
-- PROCEDURE: InserirNotificacaoAviso
-- Descrição: Cria notificações para todos os estudantes quando um novo aviso é publicado
-- Parâmetros:
--   - p_aviso_id: ID do aviso que gerou a notificação
--   - p_usuario_id: ID do usuário que publicou o aviso
--   - p_titulo: Título do aviso
--   - p_data_publicacao: Data de publicação do aviso
-- =============================================
DELIMITER //
CREATE PROCEDURE InserirNotificacaoAviso(
    IN p_aviso_id INT,
    IN p_usuario_id INT,
    IN p_titulo VARCHAR(100),
    IN p_data_publicacao DATE
)
BEGIN
    DECLARE v_mensagem TEXT;

    -- Construir a mensagem da notificação
    SET v_mensagem = CONCAT('Novo aviso: ', p_titulo);

    -- Inserir notificações para todos os estudantes
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

-- =============================================
-- PROCEDURE: InserirNotificacaoLembrete
-- Descrição: Cria lembretes automáticos para tarefas/eventos que ocorrerão no dia seguinte
-- Não recebe parâmetros - opera com base na data atual
-- =============================================
DELIMITER //
CREATE PROCEDURE InserirNotificacaoLembrete()
BEGIN
    DECLARE v_data_amanha DATE;
    
    -- Definir a data de amanhã para comparação
    SET v_data_amanha = DATE_ADD(CURDATE(), INTERVAL 1 DAY);

    -- Inserir notificações para todas as tarefas/eventos agendados para amanhã
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

-- =============================================
-- PROCEDURE: LimparNotificacoesAntigas
-- Descrição: Remove notificações já enviadas com mais de 30 dias
-- Não recebe parâmetros - opera com base na data atual
-- =============================================
DELIMITER //
CREATE PROCEDURE LimparNotificacoesAntigas()
BEGIN
    -- Excluir notificações marcadas como enviadas e com mais de 30 dias
    DELETE FROM Notificacoes
    WHERE enviada = TRUE
    AND data_notificacao < DATE_SUB(CURDATE(), INTERVAL 30 DAY);
END //
DELIMITER ;

-- =============================================
-- PROCEDURE: AtualizarSenhaUsuario
-- Descrição: Atualiza a senha de um usuário específico
-- Parâmetros:
--   - p_email: E-mail do usuário que terá a senha alterada
--   - p_nova_senha: Nova senha (já deve vir criptografada)
-- =============================================
DELIMITER //
CREATE PROCEDURE AtualizarSenhaUsuario(
    IN p_email VARCHAR(100),
    IN p_nova_senha VARCHAR(255)
)
BEGIN
    -- Atualizar a senha do usuário com o e-mail especificado
    UPDATE Usuarios
    SET senha = p_nova_senha
    WHERE email = p_email;
END //
DELIMITER ;

-- =============================================
-- PROCEDURE: CadastrarTarefaEvento
-- Descrição: Cadastra uma nova tarefa ou evento e cria notificação se for para o dia seguinte
-- Parâmetros:
--   - p_usuario_id: ID do usuário dono da tarefa/evento
--   - p_titulo: Título da tarefa/evento
--   - p_descricao: Descrição detalhada
--   - p_data: Data de ocorrência
--   - p_tipo: Tipo ('tarefa' ou 'evento')
-- =============================================
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

    -- Inserir a nova tarefa/evento na tabela
    INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo)
    VALUES (p_usuario_id, p_titulo, p_descricao, p_data, p_tipo);

    -- Obter o ID da tarefa/evento recém-criada
    SET v_tarefa_evento_id = LAST_INSERT_ID();

    -- Verificar se a tarefa/evento é para amanhã
    SET v_data_amanha = DATE_ADD(CURDATE(), INTERVAL 1 DAY);
    
    -- Se for para amanhã, criar notificação de lembrete
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

-- =============================================
-- PROCEDURE: RegistrarInteracaoAssistente
-- Descrição: Registra uma interação do usuário com a assistente virtual
-- Parâmetros:
--   - p_usuario_id: ID do usuário que interagiu
--   - p_pergunta: Pergunta feita pelo usuário
--   - p_resposta: Resposta fornecida pela assistente
-- =============================================
DELIMITER //
CREATE PROCEDURE RegistrarInteracaoAssistente(
    IN p_usuario_id INT,
    IN p_pergunta TEXT,
    IN p_resposta TEXT
)
BEGIN
    -- Registrar a interação no histórico
    INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta)
    VALUES (p_usuario_id, p_pergunta, p_resposta);
END //
DELIMITER ;

-- =============================================
-- PROCEDURE: MarcarNotificacaoEnviada
-- Descrição: Marca uma notificação como já enviada
-- Parâmetros:
--   - p_notificacao_id: ID da notificação a ser marcada
-- =============================================
DELIMITER //
CREATE PROCEDURE MarcarNotificacaoEnviada(
    IN p_notificacao_id INT
)
BEGIN
    -- Atualizar o status da notificação para enviada
    UPDATE Notificacoes
    SET enviada = TRUE
    WHERE notificacao_id = p_notificacao_id;
END //
DELIMITER ;

-- =============================================
-- PROCEDURE: ExcluirUsuario
-- Descrição: Remove um usuário e todos os seus dados relacionados (via CASCADE)
-- Parâmetros:
--   - p_usuario_id: ID do usuário a ser excluído
-- Observação: As FK configuradas com ON DELETE CASCADE garantem a exclusão dos dados relacionados
-- =============================================
DELIMITER //
CREATE PROCEDURE ExcluirUsuario(
    IN p_usuario_id INT
)
BEGIN
    -- Excluir o usuário (as relações serão tratadas pelas constraints de FK)
    DELETE FROM Usuarios
    WHERE usuario_id = p_usuario_id;
END //
DELIMITER ;