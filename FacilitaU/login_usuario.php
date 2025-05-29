<?php
// Inicia a sessão para controle de login do usuário
session_start();

// Define o título da página
$page_title = "Login";

// Inclui o arquivo de configuração (conexão ao banco, etc)
include 'config.php';

// Inclui o cabeçalho padrão (header.php)
include 'header.php';

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados enviados pelo formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Prepara a consulta SQL para buscar usuário pelo email
    $sql = "SELECT * FROM Usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);

    // Vincula o parâmetro email para evitar SQL Injection
    $stmt->bind_param("s", $email);

    // Executa a consulta
    $stmt->execute();

    // Obtém o resultado da consulta
    $result = $stmt->get_result();

    // Verifica se encontrou exatamente um usuário com esse email
    if ($result->num_rows == 1) {
        // Pega os dados do usuário
        $row = $result->fetch_assoc();

        // Verifica se a senha digitada confere com o hash armazenado no banco
        if (password_verify($senha, $row['senha'])) {
            // Armazena informações do usuário na sessão
            $_SESSION['usuario_id'] = $row['usuario_id'];
            $_SESSION['nome'] = $row['nome'];
            $_SESSION['tipo'] = $row['tipo'];
            
            // Redireciona para o menu correspondente ao tipo de usuário
            if ($row['tipo'] == 'estudante') {
                header("Location: menu_estudante.php");
            } elseif ($row['tipo'] == 'professor') {
                header("Location: menu_professor.php");
            } else {
                header("Location: menu_coordenador.php");
            }
            exit(); // Encerra o script após o redirecionamento
        } else {
            // Senha incorreta: define mensagem de erro
            $erro = "E-mail ou senha incorretos!";
        }
    } else {
        // Usuário não encontrado: define mensagem de erro
        $erro = "E-mail ou senha incorretos!";
    }
    // Fecha a consulta preparada
    $stmt->close();
}
// Fecha a conexão com o banco
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - Login</title>
    <!-- CSS customizado para a página de login -->
    <link rel="stylesheet" href="CSS/Login.css">
    <!-- FontAwesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="container">
        <!-- Seção de informações e convite para cadastro -->
        <div class="info-section">
            <h1 class="logo">FacilitaU</h1>
            <h2>Bem-vindo ao FacilitaU</h2>
            <p>A plataforma que simplifica a vida acadêmica.</p>
            <div class="signup-box">
                <p>Ainda não tem uma conta?</p>
                <a href="cadastro_usuario.php" class="btn btn-secondary">Cadastre-se</a>
            </div>
        </div>

        <!-- Seção do formulário de login -->
        <div class="form-section">
            <div id="login-form">
                <h2>Acesse sua conta</h2>

                <!-- Exibe a mensagem de erro caso exista -->
                <?php if(isset($erro)): ?>
                    <div class="alert alert-error"><?php echo $erro; ?></div>
                <?php endif; ?>

                <!-- Formulário de login -->
                <form action="login_usuario.php" method="POST">
                    <div class="input-group">
                        <label for="email">E-mail institucional</label>
                        <!-- Ícone de envelope -->
                        <i class="fas fa-envelope input-icon"></i>
                        <!-- Campo para email, obrigatório -->
                        <input type="email" name="email" id="email" placeholder="seu.email@instituicao.edu.br" required>
                    </div>
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <!-- Ícone de cadeado -->
                        <i class="fas fa-lock input-icon"></i>
                        <!-- Campo de senha, obrigatório -->
                        <input type="password" name="senha" id="senha" placeholder="****" required>
                        <!-- Ícone para mostrar/ocultar senha -->
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i>
                        <!-- Link para recuperar senha -->
                        <a href="recuperar_senha.php" class="forgot-password">Esqueci minha senha</a>
                    </div>
                    <!-- Botão para enviar formulário -->
                    <button type="submit" name="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Script para alternar a visualização da senha -->
    <script src="JS/Login.js"></script>
    <!-- Script para acessibilidade Vlibras -->
    <script src="JS/Vlibras.js"></script>

</body>
</html>
