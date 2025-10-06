<?php
// ========================================
// CLASSE PARA VALIDAÇÃO DE HORÁRIOS
// ========================================

class ScheduleValidator {
    
    /**
     * Valida se há conflitos em uma lista de horários
     * @param array $horarios Array de horários [['inicio' => 'HH:MM', 'fim' => 'HH:MM'], ...]
     * @return array ['valido' => bool, 'conflitos' => array, 'mensagem' => string]
     */
    public function validateSchedule($horarios) {
        if (empty($horarios)) {
            return [
                'valido' => true,
                'conflitos' => [],
                'mensagem' => 'Nenhum horário para validar'
            ];
        }
        
        $conflitos = [];
        
        // Verificar cada par de horários
        for ($i = 0; $i < count($horarios); $i++) {
            for ($j = $i + 1; $j < count($horarios); $j++) {
                $horario1 = $horarios[$i];
                $horario2 = $horarios[$j];
                
                // Validar formato dos horários
                if (!$this->validarFormatoHorario($horario1['inicio']) || 
                    !$this->validarFormatoHorario($horario1['fim']) ||
                    !$this->validarFormatoHorario($horario2['inicio']) || 
                    !$this->validarFormatoHorario($horario2['fim'])) {
                    return [
                        'valido' => false,
                        'conflitos' => [],
                        'mensagem' => 'Formato de horário inválido'
                    ];
                }
                
                // Verificar se há conflito
                if ($this->horariosConflitam($horario1['inicio'], $horario1['fim'], 
                                             $horario2['inicio'], $horario2['fim'])) {
                    $conflitos[] = [
                        'horario1' => $horario1,
                        'horario2' => $horario2,
                        'descricao' => "Conflito entre {$horario1['inicio']}-{$horario1['fim']} e {$horario2['inicio']}-{$horario2['fim']}"
                    ];
                }
            }
        }
        
        if (count($conflitos) > 0) {
            return [
                'valido' => false,
                'conflitos' => $conflitos,
                'mensagem' => count($conflitos) . ' conflito(s) detectado(s)'
            ];
        }
        
        return [
            'valido' => true,
            'conflitos' => [],
            'mensagem' => 'Todos os horários são válidos'
        ];
    }
    
    /**
     * Verifica se dois horários conflitam
     */
    private function horariosConflitam($inicio1, $fim1, $inicio2, $fim2) {
        $inicio1_time = strtotime($inicio1);
        $fim1_time = strtotime($fim1);
        $inicio2_time = strtotime($inicio2);
        $fim2_time = strtotime($fim2);
        
        return (
            ($inicio1_time < $fim2_time && $fim1_time > $inicio2_time) ||
            ($inicio2_time < $fim1_time && $fim2_time > $inicio1_time)
        );
    }
    
    /**
     * Valida formato de horário (HH:MM ou HH:MM:SS)
     */
    private function validarFormatoHorario($horario) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $horario);
    }
}
