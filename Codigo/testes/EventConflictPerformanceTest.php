<?php
// ========================================
// TESTE DE PERFORMANCE: CONFLITO DE EVENTOS
// ========================================

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Codigo/config.php';
require_once __DIR__ . '/../Codigo/EventManager.php';

class EventConflictPerformanceTest extends TestCase {
    private $conn;
    private $eventManager;
    private $usuario_id_teste;
    
    protected function setUp(): void {
        // Conectar ao banco de dados
        global $conn;
        $this->conn = $conn;
        $this->eventManager = new EventManager($this->conn);
        
        // Criar usuário de teste
        $email_teste = 'teste_performance_' . time() . '@facilitau.com';
        $sql = "INSERT INTO Usuarios (email, senha, tipo, nome) VALUES (?, 'senha_hash', 'estudante', 'Teste Performance')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email_teste);
        $stmt->execute();
        $this->usuario_id_teste = $this->conn->insert_id;
    }
    
    protected function tearDown(): void {
        // Limpar dados de teste
        $sql = "DELETE FROM Planejamento_Estudos WHERE usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->usuario_id_teste);
        $stmt->execute();
        
        $sql = "DELETE FROM Usuarios WHERE usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->usuario_id_teste);
        $stmt->execute();
    }
    
    /**
     * Teste 1: Criar evento inicial
     */
    public function testCriarEventoInicial() {
        $data = date('Y-m-d');
        $horario_inicio = '10:00:00';
        $horario_fim = '11:00:00';
        
        $sql = "INSERT INTO Planejamento_Estudos 
                (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia, ativo) 
                VALUES (?, 'segunda', ?, ?, 'Evento Inicial', ?, 'nao', TRUE)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $this->usuario_id_teste, $horario_inicio, $horario_fim, $data);
        $resultado = $stmt->execute();
        
        $this->assertTrue($resultado, 'Evento inicial deve ser criado com sucesso');
    }
    
    /**
     * Teste 2: Tentar inserir 100 eventos no mesmo horário e medir tempo
     */
    public function testInserir100EventosMesmoHorario() {
        // Criar evento inicial
        $data = date('Y-m-d');
        $horario_inicio = '10:00:00';
        $horario_fim = '11:00:00';
        
        $sql = "INSERT INTO Planejamento_Estudos 
                (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia, ativo) 
                VALUES (?, 'segunda', ?, ?, 'Evento Inicial', ?, 'nao', TRUE)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $this->usuario_id_teste, $horario_inicio, $horario_fim, $data);
        $stmt->execute();
        
        // Tentar criar 100 eventos no mesmo horário
        $resultado = $this->eventManager->criarEventosEmMassa(
            $this->usuario_id_teste,
            $data,
            $horario_inicio,
            $horario_fim,
            100
        );
        
        // Verificações
        $this->assertEquals(100, $resultado['eventos_criados'], '100 eventos devem ser criados');
        $this->assertLessThan(5000, $resultado['tempo_total'], 'Tempo total deve ser menor que 5 segundos');
        $this->assertLessThan(50, $resultado['tempo_medio'], 'Tempo médio por evento deve ser menor que 50ms');
        
        // Exibir resultados
        echo "\n=== RESULTADOS DO TESTE DE PERFORMANCE ===\n";
        echo "Eventos criados: {$resultado['eventos_criados']}\n";
        echo "Tempo total: " . round($resultado['tempo_total'], 2) . " ms\n";
        echo "Tempo médio por evento: " . round($resultado['tempo_medio'], 2) . " ms\n";
    }
    
    /**
     * Teste 3: Verificar conflito com 100 eventos
     */
    public function testVerificarConflitoComMuitosEventos() {
        // Criar 100 eventos
        $data = date('Y-m-d');
        $horario_inicio = '10:00:00';
        $horario_fim = '11:00:00';
        
        $this->eventManager->criarEventosEmMassa(
            $this->usuario_id_teste,
            $data,
            $horario_inicio,
            $horario_fim,
            100
        );
        
        // Verificar conflito
        $resultado = $this->eventManager->verificarConflito(
            $this->usuario_id_teste,
            $data,
            '10:30:00',
            '11:30:00'
        );
        
        // Verificações
        $this->assertTrue($resultado['conflito'], 'Deve detectar conflito');
        $this->assertLessThan(1000, $resultado['tempo_verificacao'], 'Verificação deve ser rápida (< 1s)');
        $this->assertGreaterThan(0, count($resultado['eventos_conflitantes']), 'Deve listar eventos conflitantes');
        
        echo "\n=== RESULTADOS DA VERIFICAÇÃO DE CONFLITO ===\n";
        echo "Conflito detectado: " . ($resultado['conflito'] ? 'SIM' : 'NÃO') . "\n";
        echo "Tempo de verificação: " . round($resultado['tempo_verificacao'], 2) . " ms\n";
        echo "Eventos conflitantes: " . count($resultado['eventos_conflitantes']) . "\n";
        echo "Mensagem: {$resultado['mensagem']}\n";
    }
}
