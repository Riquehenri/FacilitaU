<?php
// ========================================
// TESTE: GERENCIAMENTO DE NOTIFICAÇÕES
// ========================================

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Codigo/NotificationManager.php';

class NotificationManagerTest extends TestCase {
    private $notificationManager;
    private $mockConn;
    private $mockStmt;
    
    protected function setUp(): void {
        $this->mockConn = $this->createMock(mysqli::class);
        $this->mockStmt = $this->createMock(mysqli_stmt::class);
        
        $this->notificationManager = new NotificationManager($this->mockConn);
    }
    
    /**
     * Teste 1: Gerar notificação com dados válidos
     */
    public function testGenerateNotificationComDadosValidos() {
        $type = 'lembrete';
        $data = [
            'usuario_id' => 1,
            'mensagem' => 'Lembrete de prova amanhã',
            'data_notificacao' => '2025-04-15',
            'tarefa_evento_id' => 10
        ];
        
        $this->mockConn->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('bind_param')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockConn->insert_id = 789;
        
        $resultado = $this->notificationManager->generateNotification($type, $data);
        
        $this->assertTrue($resultado['success']);
        $this->assertEquals('Notificação gerada com sucesso', $resultado['message']);
        $this->assertEquals(789, $resultado['notificacao_id']);
    }
    
    /**
     * Teste 2: Falhar com tipo inválido
     */
    public function testGenerateNotificationComTipoInvalido() {
        $type = 'tipo_invalido';
        $data = [
            'usuario_id' => 1,
            'mensagem' => 'Mensagem'
        ];
        
        $resultado = $this->notificationManager->generateNotification($type, $data);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('Tipo de notificação inválido', $resultado['message']);
    }
    
    /**
     * Teste 3: Falhar sem usuário
     */
    public function testGenerateNotificationSemUsuario() {
        $type = 'aviso';
        $data = [
            'mensagem' => 'Mensagem sem usuário'
        ];
        
        $resultado = $this->notificationManager->generateNotification($type, $data);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('ID do usuário é obrigatório', $resultado['message']);
    }
    
    /**
     * Teste 4: Falhar sem mensagem
     */
    public function testGenerateNotificationSemMensagem() {
        $type = 'lembrete';
        $data = [
            'usuario_id' => 1
        ];
        
        $resultado = $this->notificationManager->generateNotification($type, $data);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('Mensagem é obrigatória', $resultado['message']);
    }
    
    /**
     * Teste 5: Gerar notificação com valores padrão
     */
    public function testGenerateNotificationComValoresPadrao() {
        $type = 'aviso';
        $data = [
            'usuario_id' => 1,
            'mensagem' => 'Notificação mínima'
            // Sem data_notificacao (deve usar data atual)
        ];
        
        $this->mockConn->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockConn->insert_id = 999;
        
        $resultado = $this->notificationManager->generateNotification($type, $data);
        
        $this->assertTrue($resultado['success']);
    }
}
