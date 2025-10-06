<?php
// ========================================
// CLASSE PARA GERENCIAMENTO DE TAREFAS
// ========================================

class TaskManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Salva uma tarefa no banco de dados
     * @param array $tarefaData Dados da tarefa ['titulo', 'descricao', 'data', 'usuario_id', 'tipo']
     * @return array ['success' => bool, 'message' => string, 'tarefa_id' => int|null]
     */
    public function saveTask($tarefaData) {
        // Validar dados obrigatórios
        if (empty($tarefaData['titulo'])) {
            return ['success' => false, 'message' => 'Título é obrigatório', 'tarefa_id' => null];
        }
        
        if (empty($tarefaData['data'])) {
            return ['success' => false, 'message' => 'Data é obrigatória', 'tarefa_id' => null];
        }
        
        if (empty($tarefaData['usuario_id'])) {
            return ['success' => false, 'message' => 'ID do usuário é obrigatório', 'tarefa_id' => null];
        }
        
        // Validar formato da data
        if (!$this->validarData($tarefaData['data'])) {
            return ['success' => false, 'message' => 'Formato de data inválido', 'tarefa_id' => null];
        }
        
        // Valores padrão
        $descricao = $tarefaData['descricao'] ?? '';
        $tipo = $tarefaData['tipo'] ?? 'tarefa';
        
        // Inserir no banco
        $sql = "INSERT INTO Tarefas_Eventos (usuario_id, titulo, descricao, data, tipo) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Erro ao preparar consulta', 'tarefa_id' => null];
        }
        
        $stmt->bind_param("issss", 
            $tarefaData['usuario_id'], 
            $tarefaData['titulo'], 
            $descricao, 
            $tarefaData['data'], 
            $tipo
        );
        
        if ($stmt->execute()) {
            $tarefa_id = $this->conn->insert_id;
            return [
                'success' => true, 
                'message' => 'Tarefa salva com sucesso', 
                'tarefa_id' => $tarefa_id
            ];
        } else {
            return [
                'success' => false, 
                'message' => 'Erro ao salvar tarefa: ' . $this->conn->error, 
                'tarefa_id' => null
            ];
        }
    }
    
    /**
     * Valida formato de data (Y-m-d)
     */
    private function validarData($data) {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
    
    /**
     * Busca tarefa por ID
     */
    public function getTaskById($tarefa_id) {
        $sql = "SELECT * FROM Tarefas_Eventos WHERE tarefa_evento_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $tarefa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
