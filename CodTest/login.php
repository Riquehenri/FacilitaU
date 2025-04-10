<?php 
session_start();
include_once('config.php');

if(isset($_POST['submit'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    // Verifica se o usuário existe no banco de dados
    $sql = "SELECT * FROM usuarios WHERE email = '$email' AND senha = '$senha'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        // Login bem-sucedido
        $_SESSION['email'] = $email; // Armazena o email na sessão
        header('Location: ia.php'); // Redireciona para a home
        exit(); // Importante para evitar execução adicional do script
    } else {
        // Login falhou
        $erro = "E-mail ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - Login</title>
    <link rel="stylesheet" href="CSS/Login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="info-section">
            <h1 class="logo">FacilitaU</h1>
            <h2>Bem-vindo ao FacilitaU</h2>
            <p>A plataforma que simplifica a vida acadêmica.</p>
            <div class="signup-box">
                <p>Ainda não tem uma conta?</p>
                <a href="cadastro.php" class="btn btn-secondary">Cadastre-se</a>
            </div>
        </div>

        <div class="form-section">
            <div id="login-form">
                <h2>Acesse sua conta</h2>
                <?php if(isset($erro)): ?>
                    <div class="alert alert-error"><?php echo $erro; ?></div>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="input-group">
                        <label for="email">E-mail institucional</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" placeholder="seu.email@instituicao.edu.br" required>
                    </div>
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="senha" id="senha" placeholder="****" required>
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i>
                        <a href="recuperar_senha.php" class="forgot-password">Esqueci minha senha</a>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="JS/Login.js"></script>
</body>
</html>