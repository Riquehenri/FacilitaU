-- Procedure: InserirComunicado
DELIMITER //
CREATE PROCEDURE InserirComunicado (
    IN p_faculdade_id INT,
    IN p_professor_id INT,
    IN p_coordenador_id INT,
    IN p_titulo VARCHAR(200),
    IN p_data DATE
)
BEGIN
    -- Validação: Professor OU Coordenador, mas não ambos
    IF (p_professor_id IS NOT NULL AND p_coordenador_id IS NOT NULL) OR 
       (p_professor_id IS NULL AND p_coordenador_id IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Um comunicado deve ser associado a um Professor OU um Coordenador, mas não ambos.';
    END IF;

    -- Inserir o comunicado
    INSERT INTO Comunicado (faculdade_id, professor_id, coordenador_id, titulo, data)
    VALUES (p_faculdade_id, p_professor_id, p_coordenador_id, p_titulo, p_data);

    -- Registrar no log
    INSERT INTO Log_Alteracoes (tabela_afetada, registro_id, operacao, data_operacao, detalhes)
    VALUES ('Comunicado', LAST_INSERT_ID(), 'Inserção', NOW(), CONCAT('Comunicado inserido: ', p_titulo));
END //
DELIMITER ;

-- Procedure: AtualizarDataPrazo
DELIMITER //
CREATE PROCEDURE AtualizarDataPrazo (
    IN p_prazo_id INT,
    IN p_nova_data DATE
)
BEGIN
    -- Atualizar a data do prazo
    UPDATE PrazoInstitucional
    SET data = p_nova_data
    WHERE prazo_id = p_prazo_id;

    -- Registrar no log
    INSERT INTO Log_Alteracoes (tabela_afetada, registro_id, operacao, data_operacao, detalhes)
    VALUES ('PrazoInstitucional', p_prazo_id, 'Atualização', NOW(), CONCAT('Data atualizada para: ', p_nova_data));
END //
DELIMITER ;

-- Procedure: ExcluirEstudante
DELIMITER //
CREATE PROCEDURE ExcluirEstudante (
    IN p_estudante_id INT
)
BEGIN
    -- Excluir o estudante (TarefaEvento será excluída automaticamente por CASCADE)
    DELETE FROM Estudante
    WHERE estudante_id = p_estudante_id;

    -- Registrar no log
    INSERT INTO Log_Alteracoes (tabela_afetada, registro_id, operacao, data_operacao, detalhes)
    VALUES ('Estudante', p_estudante_id, 'Exclusão', NOW(), 'Estudante excluído');
END //
DELIMITER ;