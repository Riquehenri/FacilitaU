<?php
use PHPUnit\Framework\TestCase;

/**
 * Testes para a classe Auth
 */
class AuthTest extends TestCase {
    private $conn;
    private $auth;
    private $testUserId;

    /**
     * Configuração executada antes de cada teste
     */
    protected function setUp(): void {
        // Configuração da conexão com o banco de dados de teste
        $host = 'localhost:3307';
        $usuario = 'root';
        $senha = '123456';
        $banco = 'facilitau_db';

        $this->conn = new mysqli($host, $usuario, $senha, $banco);
        
        if ($this->conn->connect_error) {
            $this->markTestSkipped('Não foi possível conectar ao banco de dados: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8");

        // Inclui a classe Auth
        require_once __DIR__ . '/../Codigo/Auth.php';
        
        // Cria uma instância da classe Auth
        $this->auth = new Auth($this->conn);

        // Cria um usuário de teste no banco
        $this->createTestUser();
    }

    /**
     * Limpeza executada após cada teste
     */
    protected function tearDown(): void {
        // Remove o usuário de teste
        if ($this->testUserId) {
            $sql = "DELETE FROM Usuarios WHERE usuario_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $this->testUserId);
            $stmt->execute();
            $stmt->close();
        }

        // Fecha a conexão
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Cria um usuário de teste no banco de dados
     */
    private function createTestUser() {
        $email = 'user@exemplo.com';
        $senha = password_hash('Abcd1234!', PASSWORD_DEFAULT);
        $nome = 'Usuário Teste';
        $tipo = 'estudante';

        $sql = "INSERT INTO Usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);
        $stmt->execute();
        $this->testUserId = $stmt->insert_id;
        $stmt->close();
    }

    /**
     * Teste 1: Autenticação com credenciais válidas
     */
    public function testAuthenticateUserWithValidCredentials() {
        // Arrange (Preparar)
        $email = 'user@exemplo.com';
        $senha = 'Abcd1234!';

        // Act (Executar)
        $result = $this->auth->authenticateUser($email, $senha);

        // Assert (Verificar)
        $this->assertIsArray($result, 'O resultado deve ser um array');
        $this->assertArrayHasKey('usuario_id', $result, 'O resultado deve conter usuario_id');
        $this->assertArrayHasKey('nome', $result, 'O resultado deve conter nome');
        $this->assertArrayHasKey('email', $result, 'O resultado deve conter email');
        $this->assertArrayHasKey('tipo', $result, 'O resultado deve conter tipo');
        $this->assertEquals('user@exemplo.com', $result['email'], 'O email deve corresponder');
        $this->assertEquals('Usuário Teste', $result['nome'], 'O nome deve corresponder');
        $this->assertEquals('estudante', $result['tipo'], 'O tipo deve ser estudante');
    }

    /**
     * Teste 2: Autenticação com senha incorreta
     */
    public function testAuthenticateUserWithInvalidPassword() {
        // Arrange
        $email = 'user@exemplo.com';
        $senhaIncorreta = 'SenhaErrada123!';

        // Act
        $result = $this->auth->authenticateUser($email, $senhaIncorreta);

        // Assert
        $this->assertFalse($result, 'A autenticação deve falhar com senha incorreta');
    }

    /**
     * Teste 3: Autenticação com email inexistente
     */
    public function testAuthenticateUserWithNonExistentEmail() {
        // Arrange
        $emailInexistente = 'naoexiste@exemplo.com';
        $senha = 'Abcd1234!';

        // Act
        $result = $this->auth->authenticateUser($emailInexistente, $senha);

        // Assert
        $this->assertFalse($result, 'A autenticação deve falhar com email inexistente');
    }

    /**
     * Teste 4: Autenticação com email vazio
     */
    public function testAuthenticateUserWithEmptyEmail() {
        // Arrange
        $email = '';
        $senha = 'Abcd1234!';

        // Act
        $result = $this->auth->authenticateUser($email, $senha);

        // Assert
        $this->assertFalse($result, 'A autenticação deve falhar com email vazio');
    }

    /**
     * Teste 5: Autenticação com senha vazia
     */
    public function testAuthenticateUserWithEmptyPassword() {
        // Arrange
        $email = 'user@exemplo.com';
        $senha = '';

        // Act
        $result = $this->auth->authenticateUser($email, $senha);

        // Assert
        $this->assertFalse($result, 'A autenticação deve falhar com senha vazia');
    }

    /**
     * Teste 6: Verificar se email existe
     */
    public function testEmailExists() {
        // Arrange
        $emailExistente = 'user@exemplo.com';
        $emailInexistente = 'naoexiste@exemplo.com';

        // Act & Assert
        $this->assertTrue($this->auth->emailExists($emailExistente), 'O email deve existir');
        $this->assertFalse($this->auth->emailExists($emailInexistente), 'O email não deve existir');
    }

    /**
     * Teste 7: Verificar hash de senha
     */
    public function testHashPassword() {
        // Arrange
        $senha = 'MinhaSenh@123';

        // Act
        $hash = $this->auth->hashPassword($senha);

        // Assert
        $this->assertNotEmpty($hash, 'O hash não deve estar vazio');
        $this->assertNotEquals($senha, $hash, 'O hash deve ser diferente da senha original');
        $this->assertTrue(password_verify($senha, $hash), 'A senha deve ser verificável com o hash');
    }
}
?>
