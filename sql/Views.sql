-- =============================================
-- VIEW: AvisosComAutor
-- Descrição: Lista todos os avisos com informações do autor (nome e tipo de usuário)
-- Propósito: Facilitar a visualização de avisos mostrando quem os publicou
-- Ordenação: Por data de publicação (implícita na consulta)
-- Relacionamentos: Junta a tabela Avisos com Usuários através do usuario_id
-- =============================================
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

-- =============================================
-- VIEW: NotificacoesPendentes
-- Descrição: Lista todas as notificações não enviadas com informações do estudante
-- Propósito: Apoiar o sistema de envio de notificações mostrando apenas as pendentes
-- Filtro: Mostra apenas notificações com enviada = FALSE
-- Relacionamentos: Junta a tabela Notificacoes com Usuários
-- =============================================
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

-- =============================================
-- VIEW: PlanejamentoPorEstudante
-- Descrição: Mostra o planejamento de estudos organizado por estudante e dia da semana
-- Propósito: Facilitar a visualização das rotinas de estudo dos alunos
-- Ordenação: Por nome do estudante, dia da semana e horário de início
-- Relacionamentos: Junta Planejamento_Estudos com Usuários
-- Observação: Usa FIELD() para ordenar os dias da semana em ordem cronológica
-- =============================================
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

-- =============================================
-- VIEW: TarefasEventosProximos
-- Descrição: Lista tarefas e eventos dos próximos 3 dias
-- Propósito: Apoiar o sistema de lembretes e calendário
-- Filtro: Mostra apenas registros com data entre hoje e daqui a 3 dias
-- Relacionamentos: Junta Tarefas_Eventos com Usuários
-- =============================================
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

-- =============================================
-- VIEW: DocumentosPorTipo
-- Descrição: Mostra todos os documentos com informações do autor (se disponível)
-- Propósito: Facilitar a gestão e consulta de documentos institucionais
-- Relacionamentos: LEFT JOIN com Usuários para incluir documentos sem autor conhecido
-- Observação: Usa LEFT JOIN para manter documentos mesmo sem autor associado
-- =============================================
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

-- =============================================
-- VIEW: InteracoesPorEstudante
-- Descrição: Mostra o histórico de interações com a assistente virtual por estudante
-- Propósito: Apoiar a análise de uso da assistente virtual
-- Ordenação: Por data de interação (mais recentes primeiro)
-- Relacionamentos: Junta Interacoes_Assistente com Usuários
-- =============================================
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

-- =============================================
-- VIEW: UsuariosAtivos
-- Descrição: Lista usuários criados nos últimos 30 dias
-- Propósito: Identificar usuários recentes para ações de onboarding
-- Filtro: Mostra apenas usuários com data_criacao >= 30 dias atrás
-- =============================================
CREATE VIEW UsuariosAtivos AS
SELECT 
    u.usuario_id,
    u.email,
    u.tipo,
    u.nome,
    u.data_criacao
FROM Usuarios u
WHERE u.data_criacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);

-- =============================================
-- VIEW: AvisosPorTipo
-- Descrição: Mostra estatísticas de avisos agrupados por tipo
-- Propósito: Fornecer métricas sobre a distribuição de avisos
-- Agregação: Conta o total e mostra a última publicação por tipo
-- =============================================
CREATE VIEW AvisosPorTipo AS
SELECT 
    tipo_aviso,
    COUNT(*) AS total,
    MAX(data_publicacao) AS ultima_publicacao
FROM Avisos
GROUP BY tipo_aviso;