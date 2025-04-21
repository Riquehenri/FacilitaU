-- =============================================
-- INSERÇÃO DE DADOS DE TESTE
-- Objetivo: Popular o banco de dados com registros iniciais para testes e desenvolvimento
-- Observação: As senhas são hashes fictícios para fins de demonstração
-- =============================================

-- =============================================
-- USUÁRIOS DE TESTE
-- Descrição: Cria três usuários representando cada tipo (estudante, professor, coordenador)
-- Propósito: Permitir login e teste de funcionalidades específicas por tipo de usuário
-- =============================================
INSERT INTO Usuarios (email, senha, tipo, nome) VALUES
-- Estudante João Silva (usuário básico do sistema)
('estudante1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'estudante', 'João Silva'),

-- Professora Maria Oliveira (pode publicar avisos e oportunidades)
('professor1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'professor', 'Maria Oliveira'),

-- Coordenador Carlos Souza (pode gerenciar documentos institucionais)
('coordenador1@facilitau.com', '$2y$10$z1X2Y3W4Q5E6R7T8U9I0O.kJ2L3M4N5P6Q7R8S9T0U1V2W3X4Y5Z6', 'coordenador', 'Carlos Souza');

-- =============================================
-- AVISOS DE TESTE
-- Descrição: Insere exemplos de avisos e oportunidades acadêmicas
-- Propósito: Demonstrar o sistema de comunicação institucional
-- =============================================
INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) VALUES
-- Aviso sobre reunião (publicado pelo coordenador)
(3, 'aviso', 'Reunião Geral', 'Reunião de coordenadores na próxima sexta-feira.', '2025-03-30'),

-- Oportunidade de estágio (publicada pela professora)
(2, 'oportunidade', 'Vaga de Estágio', 'Oportunidade de estágio em TI. Inscrições até 2025-04-05.', '2025-03-28');

-- =============================================
-- DOCUMENTOS INSTITUCIONAIS
-- Descrição: Insere documentos fundamentais para o funcionamento do sistema
-- Propósito: Popular a base de documentos da assistente virtual
-- =============================================
INSERT INTO Documentos (nome, conteudo, tipo, data_upload, usuario_id) VALUES
-- Contrato de matrícula (upload pelo coordenador)
('Contrato de Matrícula', 'Texto do contrato de matrícula...', 'contrato', '2025-03-01', 3),

-- Regulamento acadêmico (upload sem autor específico)
('Regulamento Acadêmico', 'Regras gerais do curso...', 'regulamento', '2025-03-01', NULL);

-- =============================================
-- BASE DE CONHECIMENTO DA ASSISTENTE VIRTUAL
-- Descrição: Perguntas frequentes e suas respostas
-- Propósito: Alimentar o sistema de atendimento automático
-- =============================================
INSERT INTO Perguntas_Respostas (pergunta, resposta, categoria) VALUES
-- Pergunta sobre documentação
('Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.', 'documentação'),

-- Pergunta sobre calendário acadêmico
('Qual a data da próxima prova?', 'Consulte sua agenda ou os avisos publicados.', 'calendário');

-- =============================================
-- TAREFAS E EVENTOS DE TESTE
-- Descrição: Atividades acadêmicas do estudante João Silva
-- Propósito: Demonstrar o sistema de organização acadêmica
-- =============================================
INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo) VALUES
-- Tarefa acadêmica (prova)
(1, 'Prova de Cálculo', 'Estudar capítulos 1 a 3.', '2025-04-01', 'tarefa'),

-- Evento institucional (palestra)
(1, 'Palestra de Carreira', 'Evento no auditório principal.', '2025-04-02', 'evento');

-- =============================================
-- PLANEJAMENTO DE ESTUDOS
-- Descrição: Rotina semanal de estudos do estudante João Silva
-- Propósito: Demonstrar a funcionalidade de planejamento acadêmico
-- =============================================
INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) VALUES
-- Atividade de segunda-feira
(1, 'segunda', '09:00:00', '11:00:00', 'Estudar Cálculo'),

-- Atividade de terça-feira
(1, 'terca', '14:00:00', '16:00:00', 'Revisar Notas de Física');

-- =============================================
-- NOTIFICAÇÕES DE TESTE
-- Descrição: Alertas e lembretes para o estudante
-- Propósito: Demonstrar o sistema de notificações automáticas
-- =============================================
INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id, tarefa_evento_id) VALUES
-- Lembrete de prova (vinculado a tarefa_evento_id 1)
(1, 'lembrete', 'Lembrete: Prova de Cálculo em 2025-04-01', '2025-03-30', NULL, 1),

-- Notificação de aviso (vinculado a aviso_id 1)
(1, 'aviso', 'Novo aviso: Reunião Geral', '2025-03-30', 1, NULL);

-- =============================================
-- INTERAÇÕES COM A ASSISTENTE VIRTUAL
-- Descrição: Histórico de conversas com o sistema de atendimento
-- Propósito: Demonstrar o funcionamento da assistente virtual
-- =============================================
INSERT INTO Interacoes_Assistente (usuario_id, pergunta, resposta) VALUES
-- Primeira interação do estudante João
(1, 'Como acessar o contrato?', 'Você pode acessar o contrato na seção de documentos.');