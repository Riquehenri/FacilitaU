-- Inserir Faculdades
INSERT INTO Faculdade (sigla, nome, rua, cidade, estado, cep, telefone, email, responsavel) VALUES 
('FENG', 'Faculdade de Engenharia', 'Rua das Flores, 123', 'São Paulo', 'SP', '12345-678', '(11) 91234-5678', 'engenharia@faculdade.com', 'Dr. Roberto Lima'),
('FDIR', 'Faculdade de Direito', 'Avenida Central, 456', 'Rio de Janeiro', 'RJ', '98765-432', '(21) 99876-5432', 'direito@faculdade.com', 'Dra. Mariana Costa');

-- Inserir Estudantes
INSERT INTO Estudante (faculdade_id, nome, email) VALUES 
(1, 'Ana Silva', 'ana.silva@email.com'),
(2, 'João Pereira', 'joao.pereira@email.com');

-- Inserir Professores
INSERT INTO Professor (faculdade_id, nome, email) VALUES 
(1, 'Prof. Maria Oliveira', 'maria.oliveira@email.com'),
(2, 'Prof. Carlos Souza', 'carlos.souza@email.com');

-- Inserir Coordenadores Acadêmicos
INSERT INTO Coordenador_Academico (faculdade_id, nome, email) VALUES 
(1, 'Coord. Laura Mendes', 'laura.mendes@email.com'),
(2, 'Coord. Pedro Almeida', 'pedro.almeida@email.com');

-- Inserir Tarefas/Eventos (Estudante)
INSERT INTO TarefaEvento (estudante_id, titulo, data, tipo) VALUES 
(1, 'Prova de Matemática', '2025-04-15', 'Tarefa'),
(2, 'Seminário de Direito', '2025-04-20', 'Evento');

-- Inserir Comunicados usando o Procedure
CALL InserirComunicado(1, 1, NULL, 'Aula extra dia 25', '2025-04-25');
CALL InserirComunicado(2, NULL, 2, 'Suspensão de aulas dia 28', '2025-04-28');

-- Inserir Prazos Institucionais (Coordenador)
INSERT INTO PrazoInstitucional (faculdade_id, coordenador_id, titulo, data) VALUES 
(1, 1, 'Matrícula até dia 25', '2025-04-25'),
(2, 2, 'Entrega de notas até dia 30', '2025-04-30');