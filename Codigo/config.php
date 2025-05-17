<?php
// Configurações do banco de dados
$host = 'localhost';
$port = 3307; // Porta ajustada
$usuario = 'root'; // Usuário padrão
$senha = '123456'; // Senha que definimos
$banco = 'facilitau_db'; // Nome correto do banco

// Criar conexão
$conn = new mysqli($host, $usuario, $senha, $banco, $port);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Definir charset pra evitar problemas com acentos
$conn->set_charset("utf8mb4");
?>