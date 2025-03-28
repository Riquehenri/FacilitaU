-- Atualizar o email de uma Faculdade
UPDATE Faculdade 
SET email = 'engenharia.novo@faculdade.com' 
WHERE faculdade_id = 1;

-- Atualizar o email de um Estudante
UPDATE Estudante 
SET email = 'ana.silva.novo@email.com' 
WHERE estudante_id = 1;

-- Atualizar a data de um Comunicado
UPDATE Comunicado 
SET data = '2025-04-26' 
WHERE comunicado_id = 1;

-- Atualizar a data de um Prazo Institucional usando o Procedure
CALL AtualizarDataPrazo(1, '2025-04-26');





-- Excluir uma Faculdade (Estudantes, Professores, Coordenadores, Comunicados e Prazos associados serão excluídos por CASCADE)
DELETE FROM Faculdade 
WHERE faculdade_id = 2;

-- Excluir um Estudante usando o Procedure
CALL ExcluirEstudante(1);

-- Excluir um Professor (Comunicado associado terá professor_id definido como NULL por SET NULL)
DELETE FROM Professor 
WHERE professor_id = 1;

-- Excluir um Prazo Institucional
DELETE FROM PrazoInstitucional 
WHERE prazo_id = 1;






-- Verificar Faculdades
SELECT * FROM Faculdade;

-- Verificar Estudantes
SELECT * FROM Estudante;

-- Verificar Professores
SELECT * FROM Professor;

-- Verificar Coordenadores Acadêmicos
SELECT * FROM Coordenador_Academico;

-- Verificar Tarefas/Eventos
SELECT * FROM TarefaEvento;

-- Verificar Comunicados
SELECT * FROM Comunicado;

-- Verificar Prazos Institucionais
SELECT * FROM PrazoInstitucional;

-- Verificar Views
SELECT * FROM TarefasEventosPorEstudanteFaculdade;
SELECT * FROM ComunicadosPorFaculdade;
SELECT * FROM PrazosPorFaculdade;

-- Verificar Log de Alterações
SELECT * FROM Log_Alteracoes;