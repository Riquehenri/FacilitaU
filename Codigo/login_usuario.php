<?php
// Inicia a sessão para armazenar informações do usuário logado
session_start();

// Define o título da página que aparece na aba do navegador
$page_title = "Login";

// Inclui arquivos importantes:
include 'config.php';  // Configurações do banco de dados
include 'header.php';  // Cabeçalho comum a todas as páginas

// Verifica se o formulário foi enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pega os dados do formulário
    $email = $_POST['email'];  // Email digitado
    $senha = $_POST['senha'];  // Senha digitada

    // Prepara a consulta SQL segura (evita SQL injection)
    $sql = "SELECT * FROM Usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email); // "s" indica que é uma string
    $stmt->execute();
    $result = $stmt->get_result(); // Pega o resultado da consulta

    // Verifica se encontrou exatamente 1 usuário com esse email
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc(); // Pega os dados do usuário
        
        // Verifica se a senha está correta
        if (password_verify($senha, $row['senha'])) {
            // Se estiver correto, salva os dados na sessão:
            $_SESSION['usuario_id'] = $row['usuario_id']; // ID único
            $_SESSION['nome'] = $row['nome']; // Nome do usuário
            $_SESSION['tipo'] = $row['tipo']; // Tipo (estudante, professor, etc)
            
            // Redireciona para a página correta de acordo com o tipo de usuário
            if ($row['tipo'] == 'estudante') {
                header("Location: menu_estudante.php");
            } elseif ($row['tipo'] == 'professor') {
                header("Location: menu_professor.php");
            } else {
                header("Location: menu_coordenador.php");
            }
            exit(); // Termina o script após redirecionar
        } else {
            $erro = "E-mail ou senha incorretos!"; // Mensagem de erro
        }
    } else {
        $erro = "E-mail ou senha incorretos!"; // Mensagem de erro
    }
    $stmt->close(); // Fecha a consulta
}
$conn->close(); // Fecha a conexão com o banco
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Configurações básicas da página -->
    <meta charset="UTF-8"> <!-- Codificação de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade -->
    
    <title>FacilitaU - Login</title> <!-- Título da página -->
    
    <!-- Links para arquivos de estilo -->
    <link rel="stylesheet" href="CSS/Login.css"> <!-- Estilo principal -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Ícones -->
</head>
<body>
    <!-- Container principal -->
    <div class="container">
        <!-- Seção esquerda - Informações -->
        <div class="info-section">
            <h1 class="logo">FacilitaU</h1> <!-- Logo/Nome do sistema -->
            <h2>Bem-vindo ao FacilitaU</h2> <!-- Título -->
            <p>A plataforma que simplifica a vida acadêmica.</p> <!-- Descrição -->
            
            <!-- Caixa de cadastro -->
            <div class="signup-box">
                <p>Ainda não tem uma conta?</p>
                <a href="cadastro_usuario.php" class="btn btn-secondary">Cadastre-se</a> <!-- Botão -->
            </div>
        </div>

        <!-- Seção direita - Formulário de login -->
        <div class="form-section">
            <div id="login-form">
                <h2>Acesse sua conta</h2> <!-- Título do formulário -->
                
                <!-- Mostra mensagem de erro se existir -->
                <?php if(isset($erro)): ?>
                    <div class="alert alert-error"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <!-- Formulário de login -->
                <form action="login_usuario.php" method="POST">
                    <!-- Campo de email -->
                    <div class="input-group">
                        <label for="email">E-mail institucional</label>
                        <i class="fas fa-envelope input-icon"></i> <!-- Ícone -->
                        <input type="email" name="email" id="email" placeholder="seu.email@instituicao.edu.br" required>
                    </div>
                    
                    <!-- Campo de senha -->
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <i class="fas fa-lock input-icon"></i> <!-- Ícone -->
                        <input type="password" name="senha" id="senha" placeholder="****" required>
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i> <!-- Botão para mostrar senha -->
                        <a href="recuperar_senha.php" class="forgot-password">Esqueci minha senha</a> <!-- Link para recuperação -->
                    </div>
                    
                    <!-- Botão de submit -->
                    <button type="submit" name="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Script JavaScript para funcionalidades extras -->
    <script src="JS/Login.js"></script>
</body>
</html>