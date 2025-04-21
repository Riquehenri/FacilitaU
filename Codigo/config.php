<?php
// Configurações de conexão com o banco de dados MySQL

// Endereço do servidor de banco de dados (normalmente 'localhost' em ambientes de desenvolvimento)
$host = 'localhost';

// Nome de usuário para acessar o banco de dados
// Em desenvolvimento, frequentemente usa-se 'root' como padrão
$usuario = 'root';

// Senha do usuário do banco de dados
// Em ambiente local/desenvolvimento, muitas vezes fica vazia por padrão
// EM PRODUÇÃO: Isso deve ser uma senha forte e segura!
$senha = '';

// Nome do banco de dados que será utilizado
$banco = 'facilitau_db';

// Cria uma nova conexão MySQLi (Melhorada) com o banco de dados
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    // Encerra o script e mostra mensagem de erro detalhada
    // EM PRODUÇÃO: Evite mostrar erros detalhados para usuários finais
    die("Erro na conexão: " . $conn->connect_error);
}

// Configura o conjunto de caracteres para UTF-8
// Isso é essencial para evitar problemas com acentuação e caracteres especiais
$conn->set_charset("utf8");

?>