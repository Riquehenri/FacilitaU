<?php
header('Content-Type: application/json');
require_once 'conexao.php'; // Arquivo de conexão com o banco

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo_usuario']; // 'estudante', 'professor', 'coordenador'

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Listar eventos
        $sql = "SELECT evento_id, tipo_evento AS title, data_inicio AS start, data_fim AS end, descricao 
                FROM Calendario 
                WHERE usuario_id = ? OR tipo_evento = 'aviso'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $eventos[] = $row;
        }
        echo json_encode($eventos);
        break;

    case 'POST':
        // Criar evento
        $data = json_decode(file_get_contents('php://input'), true);
        $tipo_evento = $data['tipo_evento'] ?? '';
        $data_inicio = $data['data_inicio'] ?? '';
        $data_fim = $data['data_fim'] ?? null;
        $descricao = $data['descricao'] ?? '';
        $tags = isset($data['tags']) ? implode(',', $data['tags']) : '';

        if ($tipo_usuario === 'estudante' && $tipo_evento === 'aviso') {
            http_response_code(403);
            echo json_encode(['error' => 'Estudantes não podem criar avisos']);
            exit;
        }

        $sql = "INSERT INTO Calendario (usuario_id, tipo_evento, data_inicio, data_fim, descricao, tags) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isssss', $usuario_id, $tipo_evento, $data_inicio, $data_fim, $descricao, $tags);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'evento_id' => $stmt->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar evento']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
}
?>