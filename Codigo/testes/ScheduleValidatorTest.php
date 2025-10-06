<?php
// ========================================
// TESTE: VALIDAÇÃO DE HORÁRIOS
// ========================================

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Codigo/ScheduleValidator.php';

class ScheduleValidatorTest extends TestCase {
    private $validator;
    
    protected function setUp(): void {
        $this->validator = new ScheduleValidator();
    }
    
    /**
     * Teste 1: Horários conflitantes
     */
    public function testHorariosConflitantes() {
        $horarios = [
            ['inicio' => '08:00', 'fim' => '10:00'],
            ['inicio' => '09:00', 'fim' => '11:00'] // Conflita com o primeiro
        ];
        
        $resultado = $this->validator->validateSchedule($horarios);
        
        $this->assertFalse($resultado['valido']);
        $this->assertCount(1, $resultado['conflitos']);
        $this->assertStringContainsString('conflito', strtolower($resultado['mensagem']));
    }
    
    /**
     * Teste 2: Horários sem conflito
     */
    public function testHorariosSemConflito() {
        $horarios = [
            ['inicio' => '08:00', 'fim' => '10:00'],
            ['inicio' => '10:00', 'fim' => '12:00'], // Não conflita
            ['inicio' => '14:00', 'fim' => '16:00']  // Não conflita
        ];
        
        $resultado = $this->validator->validateSchedule($horarios);
        
        $this->assertTrue($resultado['valido']);
        $this->assertCount(0, $resultado['conflitos']);
        $this->assertEquals('Todos os horários são válidos', $resultado['mensagem']);
    }
    
    /**
     * Teste 3: Múltiplos conflitos
     */
    public function testMultiplosConflitos() {
        $horarios = [
            ['inicio' => '08:00', 'fim' => '10:00'],
            ['inicio' => '09:00', 'fim' => '11:00'], // Conflita com #1
            ['inicio' => '09:30', 'fim' => '10:30']  // Conflita com #1 e #2
        ];
        
        $resultado = $this->validator->validateSchedule($horarios);
        
        $this->assertFalse($resultado['valido']);
        $this->assertGreaterThan(1, count($resultado['conflitos']));
    }
    
    /**
     * Teste 4: Formato de horário inválido
     */
    public function testFormatoHorarioInvalido() {
        $horarios = [
            ['inicio' => '25:00', 'fim' => '26:00'] // Hora inválida
        ];
        
        $resultado = $this->validator->validateSchedule($horarios);
        
        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('inválido', $resultado['mensagem']);
    }
    
    /**
     * Teste 5: Lista vazia
     */
    public function testListaVazia() {
        $horarios = [];
        
        $resultado = $this->validator->validateSchedule($horarios);
        
        $this->assertTrue($resultado['valido']);
        $this->assertEquals('Nenhum horário para validar', $resultado['mensagem']);
    }
    
    /**
     * Teste 6: Horários adjacentes (não conflitam)
     */
    public function testHorariosAdjacentes() {
        $horarios = [
            ['inicio' => '08:00', 'fim' => '10:00'],
            ['inicio' => '10:00', 'fim' => '12:00'] // Começa exatamente quando o outro termina
        ];
        
        $resultado = $this->validator->validateSchedule($horarios);
        
        $this->assertTrue($resultado['valido']);
    }
}
