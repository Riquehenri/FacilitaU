CREATE VIEW AvisosComAutor AS
SELECT 
    a.aviso_id,
    a.tipo_aviso,
    a.titulo,
    a.descricao,
    a.data_publicacao,
    u.nome AS autor,
    u.tipo AS tipo_autor
FROM Avisos a
JOIN Usuarios u ON a.usuario_id = u.usuario_id;


CREATE VIEW NotificacoesPendentes AS
SELECT 
    n.notificacao_id,
    n.usuario_id,
    u.nome AS nome_estudante,
    n.tipo_notificacao,
    n.mensagem,
    n.data_notificacao,
    n.aviso_id,
    n.tarefa_evento_id
FROM Notificacoes n
JOIN Usuarios u ON n.usuario_id = u.usuario_id
WHERE n.enviada = FALSE;


CREATE VIEW PlanejamentoPorEstudante AS
SELECT 
    p.planejamento_id,
    p.usuario_id,
    u.nome AS nome_estudante,
    p.dia_semana,
    p.horario_inicio,
    p.horario_fim,
    p.atividade
FROM Planejamento_Estudos p
JOIN Usuarios u ON p.usuario_id = u.usuario_id
ORDER BY 
    u.nome,
    FIELD(p.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'),
    p.horario_inicio;
    
    CREATE VIEW TarefasEventosProximos AS
SELECT 
    te.tarefa_evento_id,
    te.usuario_id,
    u.nome AS nome_estudante,
    te.titulo,
    te.descricao,
    te.data,
    te.tipo
FROM Tarefas_Eventos te
JOIN Usuarios u ON te.usuario_id = u.usuario_id
WHERE te.data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY);

CREATE VIEW DocumentosPorTipo AS
SELECT 
    d.documento_id,
    d.nome,
    d.conteudo,
    d.tipo,
    d.data_upload,
    u.nome AS autor
FROM Documentos d
LEFT JOIN Usuarios u ON d.usuario_id = u.usuario_id;



CREATE VIEW InteracoesPorEstudante AS
SELECT 
    i.interacao_id,
    i.usuario_id,
    u.nome AS nome_estudante,
    i.pergunta,
    i.resposta,
    i.data_interacao
FROM Interacoes_Assistente i
JOIN Usuarios u ON i.usuario_id = u.usuario_id
ORDER BY i.data_interacao DESC;


CREATE VIEW UsuariosAtivos AS
SELECT 
    u.usuario_id,
    u.email,
    u.tipo,
    u.nome,
    u.data_criacao
FROM Usuarios u
WHERE u.data_criacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);


CREATE VIEW AvisosPorTipo AS
SELECT 
    tipo_aviso,
    COUNT(*) AS total,
    MAX(data_publicacao) AS ultima_publicacao
FROM Avisos
GROUP BY tipo_aviso;