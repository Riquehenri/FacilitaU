<?php
// Configurações do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = ''; // Senha padrão do XAMPP (vazia)
$banco = 'facilitau_db';

// Conectar ao banco de dados
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificar a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);

}
/*else {
    echo "Conexão bem-sucedida";
}*/

// Definir o charset para UTF-8
$conn->set_charset("utf8");
?>