<?php
// ========================================
// TESTE: GERENCIAMENTO DE TAREFAS
// ========================================

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Codigo/TaskManager.php';

class TaskManagerTest extends TestCase {
    private $taskManager;
    private $mockConn;
    private $mockStmt;
    
    protected function setUp(): void {
        // Criar mock da conexão do banco
        $this->mockConn = $this->createMock(mysqli::class);
        $this->mockStmt = $this->createMock(mysqli_stmt::class);
        
        $this->taskManager = new TaskManager($this->mockConn);
    }
    
    /**
     * Teste 1: Salvar tarefa com dados válidos
     */
    public function testSaveTaskComDadosValidos() {
        $tarefaData = [
            'titulo' => 'Estudar para prova',
            'descricao' => 'Revisar capítulos 1 a 5',
            'data' => '2025-04-15',
            'usuario_id' => 1,
            'tipo' => 'tarefa'
        ];
        
        // Configurar mock para retornar sucesso
        $this->mockConn->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('bind_param')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockConn->insert_id = 123;
        
        $resultado = $this->taskManager->saveTask($tarefaData);
        
        $this->assertTrue($resultado['success']);
        $this->assertEquals('Tarefa salva com sucesso', $resultado['message']);
        $this->assertEquals(123, $resultado['tarefa_id']);
    }
    
    /**
     * Teste 2: Falhar ao salvar tarefa sem título
     */
    public function testSaveTaskSemTitulo() {
        $tarefaData = [
            'descricao' => 'Descrição sem título',
            'data' => '2025-04-15',
            'usuario_id' => 1
        ];
        
        $resultado = $this->taskManager->saveTask($tarefaData);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('Título é obrigatório', $resultado['message']);
        $this->assertNull($resultado['tarefa_id']);
    }
    
    /**
     * Teste 3: Falhar ao salvar tarefa sem data
     */
    public function testSaveTaskSemData() {
        $tarefaData = [
            'titulo' => 'Tarefa sem data',
            'descricao' => 'Descrição',
            'usuario_id' => 1
        ];
        
        $resultado = $this->taskManager->saveTask($tarefaData);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('Data é obrigatória', $resultado['message']);
    }
    
    /**
     * Teste 4: Falhar com formato de data inválido
     */
    public function testSaveTaskComDataInvalida() {
        $tarefaData = [
            'titulo' => 'Tarefa',
            'descricao' => 'Descrição',
            'data' => '15/04/2025', // Formato errado
            'usuario_id' => 1
        ];
        
        $resultado = $this->taskManager->saveTask($tarefaData);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('Formato de data inválido', $resultado['message']);
    }
    
    /**
     * Teste 5: Salvar tarefa com valores padrão
     */
    public function testSaveTaskComValoresPadrao() {
        $tarefaData = [
            'titulo' => 'Tarefa mínima',
            'data' => '2025-04-15',
            'usuario_id' => 1
            // Sem descrição e tipo (devem usar valores padrão)
        ];
        
        $this->mockConn->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('bind_param')
            ->with('issss', 1, 'Tarefa mínima', '', '2025-04-15', 'tarefa')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockConn->insert_id = 456;
        
        $resultado = $this->taskManager->saveTask($tarefaData);
        
        $this->assertTrue($resultado['success']);
    }
}
