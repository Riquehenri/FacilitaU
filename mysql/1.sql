-- Tabela Log_Alteracoes
CREATE TABLE Log_Alteracoes (
    log_id INT AUTO_INCREMENT,
    tabela_afetada VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    operacao VARCHAR(50) NOT NULL,
    data_operacao DATETIME NOT NULL,
    detalhes TEXT,
    PRIMARY KEY (log_id),
    INDEX idx_data_operacao (data_operacao)
) ENGINE=InnoDB COMMENT='Tabela para registrar alterações no sistema';