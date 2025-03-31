-- Inserir usuários de teste
INSERT INTO Usuarios (email, senha, tipo, nome) VALUES
('estudante1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'estudante', 'João Silva'),
('professor1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'professor', 'Maria Oliveira'),
('coordenador1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'coordenador', 'Carlos Souza');

-- Inserir avisos de teste
INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) VALUES
(3, 'aviso', 'Reunião Geral', 'Reunião de coordenadores na próxima sexta-feira.', '2025-03-30'),
(2, 'oportunidade', 'Vaga de Estágio', 'Oportunidade de estágio em TI. Inscrições até 2025-04-05.', '2025-03-28');

-- Inserir documentos de teste
INSERT INTO Documentos (nome, conteudo, tipo, data_upload, usuario_id) VALUES
('Contrato de Matrícula', 'Texto do contrato de matrícula...', 'contrato', '2025-03-01', 3),
('Regulamento Acadêmico', 'Regras gerais do curso...', 'regulamento', '2025-03-01', NULL);

-- Inserir perguntas/respostas para a assistente virtual
INSERT INTO Perguntas_Respostas (pergunta, resposta, categoria) VALUES
('Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.', 'documentação'),
('Qual a data da próxima prova?', 'Consulte sua agenda ou os avisos publicados.', 'calendário');

-- Inserir tarefas/eventos de teste
INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo) VALUES
(1, 'Prova de Cálculo', 'Estudar capítulos 1 a 3.', '2025-04-01', 'tarefa'),
(1, 'Palestra de Carreira', 'Evento no auditório principal.', '2025-04-02', 'evento');

-- Inserir planejamento de estudos de teste
INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) VALUES
(1, 'segunda', '09:00:00', '11:00:00', 'Estudar Cálculo'),
(1, 'terca', '14:00:00', '16:00:00', 'Revisar Notas de Física');

-- Inserir notificações de teste
INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id, tarefa_evento_id) VALUES
(1, 'lembrete', 'Lembrete: Prova de Cálculo em 2025-04-01', '2025-03-30', NULL, 1),
(1, 'aviso', 'Novo aviso: Reunião Geral', '2025-03-30', 1, NULL);

-- Inserir interações com a assistente virtual
INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta) VALUES
(1, 'Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.');