<?php
/**
 * Classe de Autenticação
 * Responsável por gerenciar a autenticação de usuários no sistema FacilitaU
 */
class Auth {
    private $conn;

    /**
     * Construtor da classe Auth
     * @param mysqli $conn Conexão com o banco de dados
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Autentica um usuário com email e senha
     * 
     * @param string $email Email do usuário
     * @param string $senha Senha do usuário
     * @return array|false Retorna array com dados do usuário se autenticado, false caso contrário
     */
    public function authenticateUser($email, $senha) {
        // Valida os parâmetros de entrada
        if (empty($email) || empty($senha)) {
            return false;
        }

        // Prepara a consulta SQL para buscar usuário pelo email
        $sql = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        // Vincula o parâmetro email para evitar SQL Injection
        $stmt->bind_param("s", $email);

        // Executa a consulta
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        // Obtém o resultado da consulta
        $result = $stmt->get_result();

        // Verifica se encontrou exatamente um usuário com esse email
        if ($result->num_rows == 1) {
            // Pega os dados do usuário
            $row = $result->fetch_assoc();

            // Verifica se a senha digitada confere com o hash armazenado no banco
            if (password_verify($senha, $row['senha'])) {
                // Fecha a consulta preparada
                $stmt->close();
                
                // Retorna os dados do usuário autenticado
                return [
                    'usuario_id' => $row['usuario_id'],
                    'nome' => $row['nome'],
                    'email' => $row['email'],
                    'tipo' => $row['tipo']
                ];
            }
        }

        // Fecha a consulta preparada
        $stmt->close();
        
        // Retorna false se a autenticação falhar
        return false;
    }

    /**
     * Verifica se um email existe no banco de dados
     * 
     * @param string $email Email a ser verificado
     * @return bool True se o email existe, false caso contrário
     */
    public function emailExists($email) {
        $sql = "SELECT usuario_id FROM Usuarios WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }

    /**
     * Cria um hash seguro de senha
     * 
     * @param string $senha Senha em texto plano
     * @return string Hash da senha
     */
    public function hashPassword($senha) {
        return password_hash($senha, PASSWORD_DEFAULT);
    }
}
?>
