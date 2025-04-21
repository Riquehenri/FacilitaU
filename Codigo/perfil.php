<?php
session_start(); // Inicia a sessão para acessar as variáveis de sessão do usuário, como o ID.

$page_title = "Meu Perfil"; // Define o título da página que será exibido no navegador.
include 'config.php'; // Inclui o arquivo de configuração, geralmente contendo a conexão com o banco de dados.
include 'header.php'; // Inclui o cabeçalho da página (layout padrão, menus, etc).

if (!isset($_SESSION['usuario_id'])) {
    // Verifica se o usuário está logado. Caso não esteja, redireciona para a página de login.
    header("Location: login_usuario.php");
    exit(); // Encerra o script após o redirecionamento.
}

$usuario_id = $_SESSION['usuario_id']; // Pega o ID do usuário armazenado na sessão.
$sql = "SELECT nome, email, tipo FROM Usuarios WHERE usuario_id = ?"; // Cria a consulta SQL com parâmetro.
$stmt = $conn->prepare($sql); // Prepara a declaração SQL para evitar SQL Injection.
$stmt->bind_param("i", $usuario_id); // Liga o parâmetro (ID do usuário) à consulta SQL.
$stmt->execute(); // Executa a consulta no banco de dados.
$result = $stmt->get_result(); // Pega o resultado da execução da consulta.

if ($result->num_rows == 0) {
    // Verifica se algum usuário foi retornado. Caso não, exibe erro.
    die("Usuário não encontrado.");
}

$usuario = $result->fetch_assoc(); // Armazena os dados do usuário em um array associativo.
$stmt->close(); // Fecha a declaração preparada.
$conn->close(); // Fecha a conexão com o banco de dados.

switch ($usuario['tipo']) {
    case 'coordenador':
        $icone = 'fa-user-shield'; // Ícone FontAwesome para coordenador.
        $cor = '#4a90e2'; // Cor personalizada para coordenador.
        break;
    case 'professor':
        $icone = 'fa-user-tie'; // Ícone FontAwesome para professor.
        $cor = '#2ecc71'; // Cor personalizada para professor.
        break;
    default:
        $icone = 'fa-user-graduate'; // Ícone padrão para estudante ou outros tipos.
        $cor = '#e74c3c'; // Cor padrão para outros usuários.
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres para UTF-8. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade em dispositivos móveis. -->
    <title><?php echo $page_title; ?></title> <!-- Exibe o título da página no navegador. -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Importa ícones do FontAwesome. -->
    <link rel="stylesheet" href="CSS/perfil.css"> <!-- Importa o CSS personalizado para o perfil. -->
    <style>
        /* n alterar essa parte, estilo que precisa ser mantido no PHP */ 
        .profile-icon {
            color: <?php echo $cor; ?>; /* Aplica a cor personalizada de acordo com o tipo de usuário. */
        }

        .profile-type {
            background-color: <?php echo $cor; ?>; /* Define o fundo com a mesma cor do tipo de conta. */
        }
    </style>
</head>
<body>
    <div class="profile-card"> <!-- Cartão de perfil centralizado com informações do usuário. -->
        <div class="profile-icon"> <!-- Ícone grande de perfil. -->
            <i class="fas <?php echo $icone; ?>"></i> <!-- Aplica o ícone de acordo com o tipo de usuário. -->
        </div>
        
        <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nome']); ?></h1> <!-- Exibe o nome do usuário com segurança contra XSS. -->
        <div class="profile-email"><?php echo htmlspecialchars($usuario['email']); ?></div> <!-- Exibe o email do usuário. -->
        <div class="profile-type"><?php echo $usuario['tipo']; ?></div> <!-- Mostra o tipo de conta (ex: professor, coordenador). -->
        
        <div class="profile-info"> <!-- Informações adicionais organizadas em itens. -->
            <div class="info-item">
                <span class="info-label">ID do Usuário</span> <!-- Rótulo da informação. -->
                <span class="info-value"><?php echo $usuario_id; ?></span> <!-- Valor do ID do usuário. -->
            </div>
            
            <div class="info-item">
                <span class="info-label">Tipo de Conta</span>
                <span class="info-value"><?php echo ucfirst($usuario['tipo']); ?></span> <!-- Mostra o tipo com a primeira letra maiúscula. -->
            </div>
        </div>
    </div>
</body>
</html>
