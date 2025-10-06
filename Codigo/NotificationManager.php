<?php
// ========================================
// CLASSE PARA GERENCIAMENTO DE NOTIFICAÇÕES
// ========================================

class NotificationManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Gera uma notificação
     * @param string $type Tipo da notificação ('lembrete', 'aviso')
     * @param array $data Dados da notificação ['usuario_id', 'mensagem', 'data_notificacao', 'aviso_id', 'tarefa_evento_id']
     * @return array ['success' => bool, 'message' => string, 'notificacao_id' => int|null]
     */
    public function generateNotification($type, $data) {
        // Validar tipo
        if (!in_array($type, ['lembrete', 'aviso'])) {
            return [
                'success' => false,
                'message' => 'Tipo de notificação inválido',
                'notificacao_id' => null
            ];
        }
        
        // Validar dados obrigatórios
        if (empty($data['usuario_id'])) {
            return [
                'success' => false,
                'message' => 'ID do usuário é obrigatório',
                'notificacao_id' => null
            ];
        }
        
        if (empty($data['mensagem'])) {
            return [
                'success' => false,
                'message' => 'Mensagem é obrigatória',
                'notificacao_id' => null
            ];
        }
        
        // Valores padrão
        $data_notificacao = $data['data_notificacao'] ?? date('Y-m-d');
        $aviso_id = $data['aviso_id'] ?? null;
        $tarefa_evento_id = $data['tarefa_evento_id'] ?? null;
        
        // Inserir notificação
        $sql = "INSERT INTO Notificacoes 
                (usuario_id, tipo_notificacao, mensagem, data_notificacao, enviada, aviso_id, tarefa_evento_id) 
                VALUES (?, ?, ?, ?, FALSE, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Erro ao preparar consulta',
                'notificacao_id' => null
            ];
        }
        
        $stmt->bind_param("isssii", 
            $data['usuario_id'],
            $type,
            $data['mensagem'],
            $data_notificacao,
            $aviso_id,
            $tarefa_evento_id
        );
        
        if ($stmt->execute()) {
            $notificacao_id = $this->conn->insert_id;
            return [
                'success' => true,
                'message' => 'Notificação gerada com sucesso',
                'notificacao_id' => $notificacao_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao gerar notificação: ' . $this->conn->error,
                'notificacao_id' => null
            ];
        }
    }
    
    /**
     * Busca notificação por ID
     */
    public function getNotificationById($notificacao_id) {
        $sql = "SELECT * FROM Notificacoes WHERE notificacao_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $notificacao_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Marca notificação como enviada
     */
    public function markAsSent($notificacao_id) {
        $sql = "UPDATE Notificacoes SET enviada = TRUE WHERE notificacao_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $notificacao_id);
        return $stmt->execute();
    }
}
