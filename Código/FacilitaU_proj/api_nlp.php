<?php
header('Content-Type: application/json');
require_once 'conexao.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$comando = strtolower($data['comando'] ?? '');

// Simulação de NLP: interpretar o comando
$response = ['success' => false, 'action' => '', 'message' => ''];

if (strpos($comando, 'criar plano') !== false || strpos($comando, 'sugerir plano') !== false) {
    // Extrair informações do comando (simulação básica)
    $materia = '';
    $data = '';
    if (preg_match('/para ([\w\s]+)/', $comando, $matches)) {
        $materia = $matches[1];
    }
    if (preg_match('/(na próxima semana|amanhã|dia \d+)/', $comando, $matches)) {
        $data = $matches[0];
    }

    if (!$materia) {
        $response['message'] = 'Por favor, especifique a matéria (ex.: "Crie um plano para matemática").';
    } else {
        // Calcular data de início e fim
        $data_inicio = date('Y-m-d H:i:s');
        $data_fim = date('Y-m-d H:i:s', strtotime('+7 days')); // Padrão: 1 semana
        if ($data === 'amanhã') {
            $data_fim = date('Y-m-d H:i:s', strtotime('+1 day'));
        } elseif (preg_match('/dia (\d+)/', $data, $matches)) {
            $data_fim = date('Y-m-d H:i:s', strtotime("+$matches[1] days"));
        }

        // Inserir plano no calendário
        $sql = "INSERT INTO Calendario (usuario_id, tipo_evento, data_inicio, data_fim, descricao, prioridade) 
                VALUES (?, 'plano', ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $descricao = "Plano de estudo para $materia";
        $stmt->bind_param('issss', $usuario_id, $data_inicio, $data_fim, $descricao);
        $stmt->execute();

        $response['success'] = true;
        $response['action'] = 'criar_plano';
        $response['message'] = "Plano de estudo para $materia criado de " . date('d/m/Y', strtotime($data_inicio)) . 
                              " até " . date('d/m/Y', strtotime($data_fim)) . ". Estude 1 hora por dia!";
    }
} elseif (strpos($comando, 'criar aviso') !== false) {
    // Criar um aviso com base no comando
    $sql = "SELECT * FROM Calendario WHERE usuario_id = ? AND data_inicio > NOW() ORDER BY data_inicio LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $evento = $result->fetch_assoc();

    if ($evento) {
        $aviso = [
            'tipo_evento' => 'aviso',
            'data_inicio' => date('Y-m-d H:i:s'),
            'descricao' => "Lembrete: " . $evento['tipo_evento'] . " em " . date('d/m/Y', strtotime($evento['data_inicio'])),
            'prioridade' => 3
        ];
        $sql = "INSERT INTO Calendario (usuario_id, tipo_evento, data_inicio, descricao, prioridade) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isssi', $usuario_id, $aviso['tipo_evento'], $aviso['data_inicio'], $aviso['descricao'], $aviso['prioridade']);
        $stmt->execute();

        $response['success'] = true;
        $response['action'] = 'criar_aviso';
        $response['message'] = "Aviso criado: " . $aviso['descricao'];
    } else {
        $response['message'] = "Nenhum evento próximo para criar um aviso.";
    }
} else {
    $response['message'] = "Comando não reconhecido. Tente algo como 'Criar plano para matemática na próxima semana'.";
}

echo json_encode($response);
?>