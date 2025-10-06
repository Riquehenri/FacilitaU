<?php
// ========================================
// CLASSE PARA GERENCIAMENTO DE EVENTOS
// ========================================
// Esta classe contém funções testáveis para gerenciar eventos no calendário

class EventManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Verifica conflitos de horário para um evento
     * @param int $usuario_id ID do usuário
     * @param string $data Data do evento (Y-m-d)
     * @param string $horario_inicio Horário de início (H:i:s)
     * @param string $horario_fim Horário de fim (H:i:s)
     * @param int|null $excluir_id ID do evento a excluir da verificação
     * @return array ['conflito' => bool, 'mensagem' => string, 'tempo_verificacao' => float]
     */
    public function verificarConflito($usuario_id, $data, $horario_inicio, $horario_fim, $excluir_id = null) {
        $tempo_inicio = microtime(true);
        
        // Buscar todos os planejamentos ativos do usuário
        $sql = "SELECT planejamento_id, horario_inicio, horario_fim, atividade 
                FROM Planejamento_Estudos 
                WHERE usuario_id = ? AND ativo = TRUE";
        
        if ($excluir_id) {
            $sql .= " AND planejamento_id != ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($excluir_id) {
            $stmt->bind_param("ii", $usuario_id, $excluir_id);
        } else {
            $stmt->bind_param("i", $usuario_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $conflitos = [];
        
        while ($row = $result->fetch_assoc()) {
            // Verificar se há sobreposição de horários
            if ($this->horariosConflitam($horario_inicio, $horario_fim, $row['horario_inicio'], $row['horario_fim'])) {
                $conflitos[] = $row['atividade'];
            }
        }
        
        $tempo_fim = microtime(true);
        $tempo_verificacao = ($tempo_fim - $tempo_inicio) * 1000; // em milissegundos
        
        if (count($conflitos) > 0) {
            return [
                'conflito' => true,
                'mensagem' => 'Conflito detectado com: ' . implode(', ', $conflitos),
                'tempo_verificacao' => $tempo_verificacao,
                'eventos_conflitantes' => $conflitos
            ];
        }
        
        return [
            'conflito' => false,
            'mensagem' => 'Nenhum conflito detectado',
            'tempo_verificacao' => $tempo_verificacao
        ];
    }
    
    /**
     * Verifica se dois horários conflitam
     */
    private function horariosConflitam($inicio1, $fim1, $inicio2, $fim2) {
        return (
            ($inicio1 < $fim2 && $fim1 > $inicio2) ||
            ($inicio2 < $fim1 && $fim2 > $inicio1)
        );
    }
    
    /**
     * Cria múltiplos eventos no mesmo horário (para teste de performance)
     */
    public function criarEventosEmMassa($usuario_id, $data, $horario_inicio, $horario_fim, $quantidade) {
        $tempo_inicio = microtime(true);
        $eventos_criados = 0;
        $erros = [];
        
        for ($i = 1; $i <= $quantidade; $i++) {
            $atividade = "Evento de Teste #$i";
            
            $sql = "INSERT INTO Planejamento_Estudos 
                    (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia, ativo) 
                    VALUES (?, 'segunda', ?, ?, ?, ?, 'nao', TRUE)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("issss", $usuario_id, $horario_inicio, $horario_fim, $atividade, $data);
            
            if ($stmt->execute()) {
                $eventos_criados++;
            } else {
                $erros[] = "Erro ao criar evento #$i: " . $this->conn->error;
            }
        }
        
        $tempo_fim = microtime(true);
        $tempo_total = ($tempo_fim - $tempo_inicio) * 1000;
        
        return [
            'eventos_criados' => $eventos_criados,
            'tempo_total' => $tempo_total,
            'tempo_medio' => $tempo_total / $quantidade,
            'erros' => $erros
        ];
    }
}
