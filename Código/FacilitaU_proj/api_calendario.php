<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'enviar_email.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $tag_id = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
        $sql = "SELECT c.evento_id, c.tipo_evento AS title, c.data_inicio AS start, c.data_fim AS end, c.descricao, c.prioridade, 
                       GROUP_CONCAT(t.nome) AS tags 
                FROM Calendario c 
                LEFT JOIN Evento_Tags et ON c.evento_id = et.evento_id 
                LEFT JOIN Tags t ON et.tag_id = t.tag_id 
                WHERE c.usuario_id = ? OR c.tipo_evento = 'aviso'";
        if ($tag_id) {
            $sql .= " AND et.tag_id IN ($tag_id)";
        }
        $sql .= " GROUP BY c.evento_id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $row['color'] = $row['prioridade'] == 3 ? '#ff4d4d' : ($row['prioridade'] == 2 ? '#4da8ff' : '#4CAF50');
            $eventos[] = $row;
        }
        echo json_encode($eventos);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $tipo_evento = $data['tipo_evento'] ?? '';
        $data_inicio = $data['data_inicio'] ?? '';
        $data_fim = $data['data_fim'] ?? null;
        $descricao = $data['descricao'] ?? '';
        $tags = $data['tags'] ?? [];
        $prioridade = $tipo_evento === 'aviso' ? 3 : ($tipo_evento === 'atividade' ? 2 : 1);

        if ($tipo_usuario === 'estudante' && $tipo_evento === 'aviso') {
            http_response_code(403);
            echo json_encode(['error' => 'Estudantes não podem criar avisos']);
            exit;
        }

        $conn->begin_transaction();
        try {
            $sql = "INSERT INTO Calendario (usuario_id, tipo_evento, data_inicio, data_fim, descricao, prioridade) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('issssi', $usuario_id, $tipo_evento, $data_inicio, $data_fim, $descricao, $prioridade);
            $stmt->execute();
            $evento_id = $conn->insert_id;

            foreach ($tags as $tag_id) {
                $sql = "INSERT INTO Evento_Tags (evento_id, tag_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ii', $evento_id, $tag_id);
                $stmt->execute();

                // Enviar e-mail para usuários das turmas associadas
                if ($tipo_evento === 'aviso') {
                    $sql = "SELECT u.usuario_id FROM Usuarios u 
                            JOIN Evento_Tags et ON et.tag_id = ? 
                            WHERE et.evento_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ii', $tag_id, $evento_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        enviarEmail($conn, $row['usuario_id'], 
                                   "Novo Aviso no FacilitaU", 
                                   "<h2>Novo Aviso</h2><p>$descricao</p><p>Data: " . date('d/m/Y H:i', strtotime($data_inicio)) . "</p>");
                    }
                }
            }

            $conn->commit();
            echo json_encode(['success' => true, 'evento_id' => $evento_id]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar evento']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $evento_id = $data['evento_id'] ?? 0;
        $tipo_evento = $data['tipo_evento'] ?? '';
        $data_inicio = $data['data_inicio'] ?? '';
        $data_fim = $data['data_fim'] ?? null;
        $descricao = $data['descricao'] ?? '';
        $tags = $data['tags'] ?? [];
        $prioridade = $tipo_evento === 'aviso' ? 3 : ($tipo_evento === 'atividade' ? 2 : 1);

        $sql = "SELECT usuario_id, tipo_evento FROM Calendario WHERE evento_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $evento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $evento = $result->fetch_assoc();

        if (!$evento || ($evento['usuario_id'] != $usuario_id && $tipo_usuario === 'estudante')) {
            http_response_code(403);
            echo json_encode(['error' => 'Você não tem permissão para editar este evento']);
            exit;
        }

        $conn->begin_transaction();
        try {
            $sql = "UPDATE Calendario SET tipo_evento = ?, data_inicio = ?, data_fim = ?, descricao = ?, prioridade = ? 
                    WHERE evento_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssii', $tipo_evento, $data_inicio, $data_fim, $descricao, $prioridade, $evento_id);
            $stmt->execute();

            $sql = "DELETE FROM Evento_Tags WHERE evento_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $evento_id);
            $stmt->execute();

            foreach ($tags as $tag_id) {
                $sql = "INSERT INTO Evento_Tags (evento_id, tag_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ii', $evento_id, $tag_id);
                $stmt->execute();
            }

            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao editar evento']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $evento_id = $data['evento_id'] ?? 0;

        $sql = "SELECT usuario_id, tipo_evento FROM Calendario WHERE evento_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $evento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $evento = $result->fetch_assoc();

        if (!$evento || ($evento['usuario_id'] != $usuario_id && $tipo_usuario === 'estudante')) {
            http_response_code(403);
            echo json_encode(['error' => 'Você não tem permissão para excluir este evento']);
            exit;
        }

        $sql = "DELETE FROM Calendario WHERE evento_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $evento_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir evento']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
}
?>