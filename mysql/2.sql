-- View: TarefasEventosPorEstudanteFaculdade
CREATE VIEW TarefasEventosPorEstudanteFaculdade AS
SELECT 
    e.estudante_id,
    e.nome AS nome_estudante,
    f.nome AS faculdade_nome,
    te.tarefa_evento_id,
    te.titulo,
    te.data,
    te.tipo
FROM Estudante e
JOIN Faculdade f ON e.faculdade_id = f.faculdade_id
JOIN TarefaEvento te ON e.estudante_id = te.estudante_id;

-- View: ComunicadosPorFaculdade
CREATE VIEW ComunicadosPorFaculdade AS
SELECT 
    f.faculdade_id,
    f.nome AS faculdade_nome,
    c.comunicado_id,
    c.titulo,
    c.data,
    COALESCE(p.nome, co.nome) AS autor
FROM Comunicado c
JOIN Faculdade f ON c.faculdade_id = f.faculdade_id
LEFT JOIN Professor p ON c.professor_id = p.professor_id
LEFT JOIN Coordenador_Academico co ON c.coordenador_id = co.coordenador_id;

-- View: PrazosPorFaculdade
CREATE VIEW PrazosPorFaculdade AS
SELECT 
    f.faculdade_id,
    f.nome AS faculdade_nome,
    p.prazo_id,
    p.titulo,
    p.data,
    c.nome AS coordenador_nome
FROM PrazoInstitucional p
JOIN Faculdade f ON p.faculdade_id = f.faculdade_id
JOIN Coordenador_Academico c ON p.coordenador_id = c.coordenador_id;