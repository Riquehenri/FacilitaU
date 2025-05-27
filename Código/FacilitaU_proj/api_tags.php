<?php
header('Content-Type: application/json');
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Listar tags
        $sql = "SELECT tag_id, nome FROM Tags";
        $result = $conn->query($sql);
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        echo json_encode($tags);
        break;

    case 'POST':
        // Associar tag a evento
        $data = json_decode(file_get_contents('php://input'), true);
        $evento_id = $data['evento_id'] ?? 0;
        $tag_id = $data['tag_id'] ?? 0;

        $sql = "INSERT INTO Evento_Tags (evento_id, tag_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $evento_id, $tag_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao associar tag']);
        }
        break;
}
?>