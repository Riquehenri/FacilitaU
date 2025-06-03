<?php
// ========================================
// ARQUIVO EXCLUSIVO PARA PROCESSAMENTO AJAX
// ========================================
// Este arquivo é responsável APENAS por processar requisições AJAX (JavaScript)
// e retornar dados em formato JSON. Não contém HTML, apenas lógica de backend.
// AJAX = Asynchronous JavaScript and XML (permite comunicação entre frontend e backend sem recarregar a página)

// Desativar exibição de erros para evitar contaminação da saída JSON
// Quando retornamos JSON, qualquer texto extra (como erros do PHP) pode quebrar o formato
ini_set('display_errors', 0);  // Não mostra erros na tela
error_reporting(0);            // Não reporta erros

// Iniciar buffer de saída e limpar qualquer saída anterior
// Buffer = área temporária de memória onde o PHP armazena dados antes de enviar ao navegador
while (ob_get_level()) {       // Enquanto houver buffers ativos
    ob_end_clean();            // Limpa e fecha cada buffer
}
ob_start();                    // Inicia um novo buffer limpo

// Iniciar sessão
// Sessão = forma de manter dados do usuário entre diferentes páginas/requisições
session_start();

// ========================================
// VALIDAÇÕES INICIAIS DE SEGURANÇA
// ========================================

// Verificar se é uma requisição POST com JSON
// POST = método HTTP usado para enviar dados (mais seguro que GET)
// GET = usado para buscar dados (aparece na URL)
// POST = usado para criar/modificar dados (não aparece na URL)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Se não for POST, retorna erro e para a execução
    outputJSON(['success' => false, 'message' => 'Método não permitido']);
    exit; // Para a execução do script aqui
}

// Verificar se o usuário está logado
// $_SESSION = array global que mantém dados do usuário logado
if (!isset($_SESSION['usuario_id'])) {
    // Se não há ID do usuário na sessão, ele não está logado
    outputJSON(['success' => false, 'message' => 'Usuário não está logado']);
    exit;
}

// ========================================
// CONEXÃO COM BANCO DE DADOS
// ========================================

// Incluir apenas configuração do banco
// try/catch = estrutura para capturar e tratar erros
try {
    include 'config.php';  // Arquivo com configurações de conexão ao banco
} catch (Exception $e) {   // Se der erro na inclusão
    outputJSON(['success' => false, 'message' => 'Erro na conexão com banco de dados']);
    exit;
}

// Obter dados do usuário da sessão
$usuario_id = $_SESSION['usuario_id'];     // ID único do usuário
$tipo_usuario = $_SESSION['tipo'];         // Tipo: 'estudante', 'professor', 'coordenador'

// ========================================
// PROCESSAMENTO DOS DADOS RECEBIDOS
// ========================================

// Ler dados JSON enviados pelo JavaScript
$input_raw = file_get_contents('php://input');  // Lê dados brutos da requisição
$input = json_decode($input_raw, true);          // Converte JSON em array PHP

// Validar se os dados JSON são válidos
if (!$input) {
    // Se não conseguiu decodificar o JSON, retorna erro com debug
    outputJSON(['success' => false, 'message' => 'Dados JSON inválidos', 'debug' => $input_raw]);
    exit;
}

// Extrair a ação solicitada (que operação fazer)
$acao = $input['acao'] ?? '';  // ?? = operador null coalescing (se não existir, usa string vazia)

// ========================================
// FUNÇÃO PRINCIPAL: CALCULAR RECORRÊNCIA
// ========================================
// Esta função determina se um evento recorrente deve aparecer em uma data específica
function eventoAparecemData($data_inicial, $tipo_recorrencia, $data_verificar) {
    // DateTime = classe PHP para trabalhar com datas
    $data_inicial_obj = new DateTime($data_inicial);      // Data quando o evento foi criado
    $data_verificar_obj = new DateTime($data_verificar);  // Data que estamos verificando
    
    // Se a data de verificação é anterior à data inicial, o evento não aparece
    // Exemplo: evento criado em 15/01, não pode aparecer em 10/01
    if ($data_verificar_obj < $data_inicial_obj) {
        return false;  // false = não aparece
    }
    
    // Switch = estrutura condicional para múltiplas opções
    switch ($tipo_recorrencia) {
        case 'nao':
            // Não repete: aparece apenas na data inicial
            return $data_inicial === $data_verificar;
            
        case 'diario':
            // Repete diariamente: aparece todos os dias após a data inicial
            return true;
            
        case 'semanal':
            // Repete semanalmente: aparece no mesmo dia da semana
            // format('w') retorna o dia da semana (0=domingo, 1=segunda, etc.)
            return $data_inicial_obj->format('w') === $data_verificar_obj->format('w');
            
        case 'mensal':
            // Repete mensalmente: aparece no mesmo dia do mês
            // format('j') retorna o dia do mês (1, 2, 3... 31)
            return $data_inicial_obj->format('j') === $data_verificar_obj->format('j');
            
        case 'anual':
            // Repete anualmente: aparece na mesma data (dia e mês)
            // format('m-d') retorna mês-dia (ex: 12-25 para 25 de dezembro)
            return $data_inicial_obj->format('m-d') === $data_verificar_obj->format('m-d');
            
        default:
            // Caso não reconheça o tipo, não aparece
            return false;
    }
}

// ========================================
// PROCESSAMENTO DAS AÇÕES (OPERAÇÕES)
// ========================================

// try/catch para capturar qualquer erro durante o processamento
try {
    // Switch para determinar qual operação executar
    switch ($acao) {
        
        // ========================================
        // AÇÃO: CRIAR PLANEJAMENTO DE ESTUDOS
        // ========================================
        case 'criar_planejamento':
            // Verificar permissão: apenas estudantes podem criar planejamentos pessoais
            if ($tipo_usuario !== 'estudante') {
                outputJSON(['success' => false, 'message' => 'Sem permissão para criar planejamentos']);
                exit;
            }
            
            // Validar dados obrigatórios
            // empty() = verifica se está vazio, null ou false
            if (empty($input['atividade']) || empty($input['horario_inicio']) || empty($input['data'])) {
                outputJSON(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
                exit;
            }
            
            // Extrair dados do formulário
            $atividade = $input['atividade'];           // Nome da atividade (ex: "Estudar Matemática")
            $horario_inicio = $input['horario_inicio']; // Horário de início (ex: "14:00")
            $duracao = $input['duracao'] ?? 60;         // Duração em minutos (padrão: 60)
            
            // Calcular horário de fim baseado na duração
            // strtotime() converte string de tempo em timestamp
            // date() formata timestamp de volta para string
            $horario_fim = date('H:i:s', strtotime($horario_inicio . ' + ' . $duracao . ' minutes'));
            
            $data_inicial = $input['data'];                    // Data inicial do evento
            $tipo_recorrencia = $input['repetir'] ?? 'nao';    // Tipo de repetição
            
            // Converter data para dia da semana (para compatibilidade com sistema antigo)
            $data_obj = new DateTime($data_inicial);
            $dias_semana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
            $dia_semana = $dias_semana[$data_obj->format('w')]; // w = dia da semana numérico
            
            // ========================================
            // VERIFICAR DUPLICATAS
            // ========================================
            // Evitar criar planejamentos idênticos
            $sql_check = "SELECT COUNT(*) as count FROM Planejamento_Estudos 
                          WHERE usuario_id = ? AND atividade = ? AND horario_inicio = ? 
                          AND data_inicial = ? AND tipo_recorrencia = ?";
            
            // Prepared Statement = forma segura de executar SQL (previne SQL Injection)
            $stmt_check = $conn->prepare($sql_check);
            
            if (!$stmt_check) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da consulta']);
                exit;
            }
            
            // Bind parameters = associar valores aos placeholders (?)
            // "issss" = tipos dos parâmetros (i=integer, s=string)
            $stmt_check->bind_param("issss", $usuario_id, $atividade, $horario_inicio, $data_inicial, $tipo_recorrencia);
            $stmt_check->execute();                    // Executar a consulta
            $result_check = $stmt_check->get_result(); // Obter resultado
            $exists = $result_check->fetch_assoc()['count'] > 0; // Verificar se existe
            
            if ($exists) {
                outputJSON(['success' => false, 'message' => 'Já existe um planejamento similar!']);
                exit;
            }
            
            // ========================================
            // INSERIR NOVO PLANEJAMENTO
            // ========================================
            $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da inserção']);
                exit;
            }
            
            $stmt->bind_param("issssss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia);
            
            if ($stmt->execute()) {
                // Match expression (PHP 8+) = switch mais moderno e conciso
                $mensagem = match($tipo_recorrencia) {
                    'nao' => 'Planejamento criado para o dia selecionado!',
                    'diario' => 'Planejamento criado com repetição diária!',
                    'semanal' => 'Planejamento criado com repetição semanal!',
                    'mensal' => 'Planejamento criado com repetição mensal!',
                    'anual' => 'Planejamento criado com repetição anual!',
                    default => 'Planejamento criado!'
                };
                outputJSON(['success' => true, 'message' => $mensagem]);
            } else {
                outputJSON(['success' => false, 'message' => 'Erro ao criar planejamento']);
            }
            break;
            
        // ========================================
        // AÇÃO: CRIAR AVISO
        // ========================================
        case 'criar_aviso':
            // Verificar permissão: apenas professores e coordenadores
            if (!in_array($tipo_usuario, ['professor', 'coordenador'])) {
                outputJSON(['success' => false, 'message' => 'Sem permissão para criar avisos']);
                exit;
            }
            
            // Validar dados obrigatórios
            if (empty($input['titulo']) || empty($input['descricao']) || empty($input['data'])) {
                outputJSON(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
                exit;
            }
            
            // Extrair dados do formulário
            $titulo = $input['titulo'];                     // Título do aviso
            $descricao = $input['descricao'];               // Descrição detalhada
            $tipo_aviso = $input['tipo_aviso'];             // Tipo: 'aviso' ou 'oportunidade'
            $data_inicial = $input['data'];                 // Data do aviso
            $tipo_recorrencia = $input['repetir'] ?? 'nao'; // Tipo de repetição
            
            // Verificar duplicatas (mesmo processo do planejamento)
            $sql_check = "SELECT COUNT(*) as count FROM Avisos 
                          WHERE usuario_id = ? AND titulo = ? AND data_inicial = ? AND tipo_recorrencia = ?";
            $stmt_check = $conn->prepare($sql_check);
            
            if (!$stmt_check) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da consulta']);
                exit;
            }
            
            $stmt_check->bind_param("isss", $usuario_id, $titulo, $data_inicial, $tipo_recorrencia);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $exists = $result_check->fetch_assoc()['count'] > 0;
            
            if ($exists) {
                outputJSON(['success' => false, 'message' => 'Já existe um aviso similar!']);
                exit;
            }
            
            // Inserir novo aviso
            $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao, data_inicial, tipo_recorrencia) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da inserção']);
                exit;
            }
            
            // Note: data_publicacao e data_inicial são iguais neste caso
            $stmt->bind_param("issssss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_inicial, $data_inicial, $tipo_recorrencia);
            
            if ($stmt->execute()) {
                $mensagem = match($tipo_recorrencia) {
                    'nao' => 'Aviso criado para o dia selecionado!',
                    'semanal' => 'Aviso criado com repetição semanal!',
                    'mensal' => 'Aviso criado com repetição mensal!',
                    'anual' => 'Aviso criado com repetição anual!',
                    default => 'Aviso criado!'
                };
                outputJSON(['success' => true, 'message' => $mensagem]);
            } else {
                outputJSON(['success' => false, 'message' => 'Erro ao criar aviso']);
            }
            break;
            
        // ========================================
        // AÇÃO: BUSCAR EVENTOS DE UM DIA
        // ========================================
        case 'buscar_eventos':
            if (empty($input['data'])) {
                outputJSON(['success' => false, 'message' => 'Data não fornecida']);
                exit;
            }
            
            $data = $input['data'];  // Data para buscar eventos
            $eventos = [];           // Array para armazenar eventos encontrados
            
            // ========================================
            // BUSCAR PLANEJAMENTOS (APENAS ESTUDANTES)
            // ========================================
            if ($tipo_usuario === 'estudante') {
                $sql_plan = "SELECT * FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
                $stmt_plan = $conn->prepare($sql_plan);
                
                if ($stmt_plan) {
                    $stmt_plan->bind_param("i", $usuario_id);
                    $stmt_plan->execute();
                    $result_plan = $stmt_plan->get_result();
                    
                    // Loop através de todos os planejamentos do usuário
                    while ($row = $result_plan->fetch_assoc()) {
                        // Verificar se este planejamento deve aparecer na data solicitada
                        if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data)) {
                            // Adicionar ao array de eventos
                            $eventos[] = [
                                'id' => $row['planejamento_id'],
                                'tipo' => 'planejamento',
                                'titulo' => $row['atividade'],
                                'horario_inicio' => $row['horario_inicio'],
                                'horario_fim' => $row['horario_fim'],
                                'tipo_recorrencia' => $row['tipo_recorrencia'],
                                'pode_editar' => true  // Estudante pode editar seus próprios planejamentos
                            ];
                        }
                    }
                }
            }
            
            // ========================================
            // BUSCAR AVISOS (TODOS OS USUÁRIOS VEEM)
            // ========================================
            $sql_avisos = "SELECT a.*, u.nome as autor_nome, u.tipo as autor_tipo 
                          FROM Avisos a 
                          JOIN Usuarios u ON a.usuario_id = u.usuario_id 
                          WHERE a.ativo = TRUE";
            $stmt_avisos = $conn->prepare($sql_avisos);
            
            if ($stmt_avisos) {
                $stmt_avisos->execute();
                $result_avisos = $stmt_avisos->get_result();
                
                // Loop através de todos os avisos ativos
                while ($row = $result_avisos->fetch_assoc()) {
                    // Verificar se este aviso deve aparecer na data solicitada
                    if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data)) {
                        $eventos[] = [
                            'id' => $row['aviso_id'],
                            'tipo' => 'aviso_' . $row['autor_tipo'],  // Ex: 'aviso_professor'
                            'titulo' => $row['titulo'],
                            'descricao' => $row['descricao'],
                            'autor' => $row['autor_nome'],
                            'tipo_aviso' => $row['tipo_aviso'],
                            'tipo_recorrencia' => $row['tipo_recorrencia'],
                            // Pode editar apenas se for o autor e tiver permissão
                            'pode_editar' => ($row['usuario_id'] == $usuario_id && in_array($tipo_usuario, ['professor', 'coordenador']))
                        ];
                    }
                }
            }
            
            // Retornar todos os eventos encontrados
            outputJSON(['success' => true, 'eventos' => $eventos]);
            break;
            
        // ========================================
        // AÇÃO: REMOVER PLANEJAMENTO
        // ========================================
        case 'remover_planejamento':
            // Verificar permissão
            if ($tipo_usuario !== 'estudante') {
                outputJSON(['success' => false, 'message' => 'Sem permissão para remover planejamentos']);
                exit;
            }
            
            if (empty($input['id'])) {
                outputJSON(['success' => false, 'message' => 'ID do planejamento não fornecido']);
                exit;
            }
            
            // Soft delete: marcar como inativo ao invés de deletar fisicamente
            // Isso preserva dados para auditoria e permite recuperação
            $sql = "UPDATE Planejamento_Estudos SET ativo = FALSE WHERE planejamento_id = ? AND usuario_id = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da remoção']);
                exit;
            }
            
            $stmt->bind_param("ii", $input['id'], $usuario_id);
            
            if ($stmt->execute()) {
                outputJSON(['success' => true, 'message' => 'Planejamento removido!']);
            } else {
                outputJSON(['success' => false, 'message' => 'Erro ao remover planejamento']);
            }
            break;
            
        default:
            // Ação não reconhecida
            outputJSON(['success' => false, 'message' => 'Ação não reconhecida: ' . $acao]);
    }
} catch (Exception $e) {
    // Capturar qualquer erro não previsto
    outputJSON(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}

// ========================================
// FUNÇÃO UTILITÁRIA: RETORNAR JSON
// ========================================
// Esta função garante que sempre retornamos JSON válido e limpo
function outputJSON($data) {
    // Limpar qualquer saída anterior que possa contaminar o JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Definir cabeçalhos HTTP apropriados
    header('Content-Type: application/json');           // Informa que é JSON
    header('Cache-Control: no-cache, no-store, must-revalidate'); // Não cachear
    header('Pragma: no-cache');                         // Compatibilidade com navegadores antigos
    header('Expires: 0');                               // Expira imediatamente
    
    // Converter array PHP para JSON e enviar
    echo json_encode($data);
    exit; // Parar execução após enviar resposta
}

// ========================================
// PONTOS DE EXPANSÃO FUTURA:
// ========================================

/* 
1. SISTEMA DE NOTIFICAÇÕES:
   - Adicionar case 'criar_notificacao'
   - Implementar notificações push
   - Sistema de lembretes por email/SMS

2. SISTEMA DE ANEXOS:
   - Permitir upload de arquivos nos avisos
   - Suporte a imagens, PDFs, documentos
   - Galeria de mídia

3. SISTEMA DE COMENTÁRIOS:
   - Permitir comentários em avisos
   - Sistema de likes/reações
   - Discussões em threads

4. RELATÓRIOS E ANALYTICS:
   - case 'gerar_relatorio'
   - Estatísticas de uso do calendário
   - Relatórios de produtividade

5. INTEGRAÇÃO COM CALENDÁRIOS EXTERNOS:
   - Sincronização com Google Calendar
   - Exportar para iCal
   - Importar eventos externos

6. SISTEMA DE PERMISSÕES AVANÇADO:
   - Grupos de usuários
   - Permissões granulares
   - Moderação de conteúdo

7. API REST COMPLETA:
   - Endpoints padronizados
   - Autenticação por token
   - Documentação automática

8. CACHE E PERFORMANCE:
   - Cache de consultas frequentes
   - Otimização de queries
   - Paginação de resultados

9. AUDITORIA E LOGS:
   - Log de todas as ações
   - Histórico de modificações
   - Rastreamento de usuários

10. BACKUP E RECUPERAÇÃO:
    - Backup automático de dados
    - Versionamento de eventos
    - Recuperação de dados deletados
*/
?>
