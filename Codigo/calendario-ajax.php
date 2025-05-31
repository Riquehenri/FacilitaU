<?php
// ARQUIVO EXCLUSIVO PARA PROCESSAMENTO AJAX
// Sem HTML, sem includes desnecessários, apenas JSON puro

// Desativar exibição de erros para evitar contaminação da saída JSON
ini_set('display_errors', 0);
error_reporting(0);

// Iniciar buffer de saída e limpar qualquer saída anterior
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Iniciar sessão
session_start();

// Verificar se é uma requisição POST com JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    outputJSON(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    outputJSON(['success' => false, 'message' => 'Usuário não está logado']);
    exit;
}

// Incluir apenas configuração do banco
try {
    include 'config.php';
} catch (Exception $e) {
    outputJSON(['success' => false, 'message' => 'Erro na conexão com banco de dados']);
    exit;
}

// Obter dados do usuário
$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo'];

// Ler dados JSON
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

if (!$input) {
    outputJSON(['success' => false, 'message' => 'Dados JSON inválidos', 'debug' => $input_raw]);
    exit;
}

$acao = $input['acao'] ?? '';

// Função para calcular se um evento recorrente deve aparecer em uma data específica
function eventoAparecemData($data_inicial, $tipo_recorrencia, $data_verificar) {
    $data_inicial_obj = new DateTime($data_inicial);
    $data_verificar_obj = new DateTime($data_verificar);
    
    if ($data_verificar_obj < $data_inicial_obj) {
        return false;
    }
    
    switch ($tipo_recorrencia) {
        case 'nao':
            return $data_inicial === $data_verificar;
        case 'diario':
            return true;
        case 'semanal':
            return $data_inicial_obj->format('w') === $data_verificar_obj->format('w');
        case 'mensal':
            return $data_inicial_obj->format('j') === $data_verificar_obj->format('j');
        case 'anual':
            return $data_inicial_obj->format('m-d') === $data_verificar_obj->format('m-d');
        default:
            return false;
    }
}

// Processar ações
try {
    switch ($acao) {
        case 'criar_planejamento':
            if ($tipo_usuario !== 'estudante') {
                outputJSON(['success' => false, 'message' => 'Sem permissão para criar planejamentos']);
                exit;
            }
            
            if (empty($input['atividade']) || empty($input['horario_inicio']) || empty($input['data'])) {
                outputJSON(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
                exit;
            }
            
            $atividade = $input['atividade'];
            $horario_inicio = $input['horario_inicio'];
            $duracao = $input['duracao'] ?? 60;
            $horario_fim = date('H:i:s', strtotime($horario_inicio . ' + ' . $duracao . ' minutes'));
            $data_inicial = $input['data'];
            $tipo_recorrencia = $input['repetir'] ?? 'nao';
            
            $data_obj = new DateTime($data_inicial);
            $dias_semana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
            $dia_semana = $dias_semana[$data_obj->format('w')];
            
            // Verificar duplicatas
            $sql_check = "SELECT COUNT(*) as count FROM Planejamento_Estudos 
                          WHERE usuario_id = ? AND atividade = ? AND horario_inicio = ? 
                          AND data_inicial = ? AND tipo_recorrencia = ?";
            $stmt_check = $conn->prepare($sql_check);
            
            if (!$stmt_check) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da consulta']);
                exit;
            }
            
            $stmt_check->bind_param("issss", $usuario_id, $atividade, $horario_inicio, $data_inicial, $tipo_recorrencia);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $exists = $result_check->fetch_assoc()['count'] > 0;
            
            if ($exists) {
                outputJSON(['success' => false, 'message' => 'Já existe um planejamento similar!']);
                exit;
            }
            
            // Inserir planejamento
            $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da inserção']);
                exit;
            }
            
            $stmt->bind_param("issssss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia);
            
            if ($stmt->execute()) {
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
            
        case 'criar_aviso':
            if (!in_array($tipo_usuario, ['professor', 'coordenador'])) {
                outputJSON(['success' => false, 'message' => 'Sem permissão para criar avisos']);
                exit;
            }
            
            if (empty($input['titulo']) || empty($input['descricao']) || empty($input['data'])) {
                outputJSON(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
                exit;
            }
            
            $titulo = $input['titulo'];
            $descricao = $input['descricao'];
            $tipo_aviso = $input['tipo_aviso'];
            $data_inicial = $input['data'];
            $tipo_recorrencia = $input['repetir'] ?? 'nao';
            
            // Verificar duplicatas
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
            
            // Inserir aviso
            $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao, data_inicial, tipo_recorrencia) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                outputJSON(['success' => false, 'message' => 'Erro na preparação da inserção']);
                exit;
            }
            
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
            
        case 'buscar_eventos':
            if (empty($input['data'])) {
                outputJSON(['success' => false, 'message' => 'Data não fornecida']);
                exit;
            }
            
            $data = $input['data'];
            $eventos = [];
            
            // Buscar planejamentos (estudantes)
            if ($tipo_usuario === 'estudante') {
                $sql_plan = "SELECT * FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
                $stmt_plan = $conn->prepare($sql_plan);
                
                if ($stmt_plan) {
                    $stmt_plan->bind_param("i", $usuario_id);
                    $stmt_plan->execute();
                    $result_plan = $stmt_plan->get_result();
                    
                    while ($row = $result_plan->fetch_assoc()) {
                        if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data)) {
                            $eventos[] = [
                                'id' => $row['planejamento_id'],
                                'tipo' => 'planejamento',
                                'titulo' => $row['atividade'],
                                'horario_inicio' => $row['horario_inicio'],
                                'horario_fim' => $row['horario_fim'],
                                'tipo_recorrencia' => $row['tipo_recorrencia'],
                                'pode_editar' => true
                            ];
                        }
                    }
                }
            }
            
            // Buscar avisos (todos)
            $sql_avisos = "SELECT a.*, u.nome as autor_nome, u.tipo as autor_tipo 
                          FROM Avisos a 
                          JOIN Usuarios u ON a.usuario_id = u.usuario_id 
                          WHERE a.ativo = TRUE";
            $stmt_avisos = $conn->prepare($sql_avisos);
            
            if ($stmt_avisos) {
                $stmt_avisos->execute();
                $result_avisos = $stmt_avisos->get_result();
                
                while ($row = $result_avisos->fetch_assoc()) {
                    if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data)) {
                        $eventos[] = [
                            'id' => $row['aviso_id'],
                            'tipo' => 'aviso_' . $row['autor_tipo'],
                            'titulo' => $row['titulo'],
                            'descricao' => $row['descricao'],
                            'autor' => $row['autor_nome'],
                            'tipo_aviso' => $row['tipo_aviso'],
                            'tipo_recorrencia' => $row['tipo_recorrencia'],
                            'pode_editar' => ($row['usuario_id'] == $usuario_id && in_array($tipo_usuario, ['professor', 'coordenador']))
                        ];
                    }
                }
            }
            
            outputJSON(['success' => true, 'eventos' => $eventos]);
            break;
            
        case 'remover_planejamento':
            if ($tipo_usuario !== 'estudante') {
                outputJSON(['success' => false, 'message' => 'Sem permissão para remover planejamentos']);
                exit;
            }
            
            if (empty($input['id'])) {
                outputJSON(['success' => false, 'message' => 'ID do planejamento não fornecido']);
                exit;
            }
            
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
            outputJSON(['success' => false, 'message' => 'Ação não reconhecida: ' . $acao]);
    }
} catch (Exception $e) {
    outputJSON(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}

// Função para retornar JSON e encerrar
function outputJSON($data) {
    // Limpar qualquer saída anterior
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Definir cabeçalhos
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Enviar JSON
    echo json_encode($data);
    exit;
}