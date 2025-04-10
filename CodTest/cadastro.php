<?php 
if(isset($_POST['submit'])) {
    include_once('config.php');
    
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    $result = mysqli_query($conn, "INSERT INTO usuarios(nome, email, senha) VALUES ('$nome', '$email', '$senha')");
    header('Location: login.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - Cadastro</title>
    <link rel="stylesheet" href="CSS/Login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="info-section">
            <h1 class="logo">FacilitaU</h1>
            <h2>Bem-vindo ao FacilitaU</h2>
            <p>A plataforma que simplifica a vida acadêmica.</p>
            <div class="login-box">
                <p>Já possui uma conta?</p>
                <a href="login.php" class="btn btn-secondary">Acessar conta</a>
            </div>
        </div>

        <div class="form-section">
            <div id="register-form">
                <h2>Crie sua conta</h2>
                <form action="cadastro.php" method="POST">
                    <div class="input-group">
                        <label for="nome">Nome Completo</label>
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="nome" id="nome" required>
                    </div>
                    <div class="input-group">
                        <label for="email">E-mail institucional</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="senha" id="senha" required>
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i>
                    </div>
                    <div class="input-group">
                        <label for="confirmar_senha">Confirmar Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirmar_senha" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="JS/Login.js"></script>
</body>
</html>