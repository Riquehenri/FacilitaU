<?php
// Define o título da página
$page_title = "Cadastro de Usuário";

// Inclui arquivos de configuração e cabeçalho
include 'config.php';  // Configurações do banco de dados
include 'header.php';  // Cabeçalho HTML comum

// Verifica se o formulário foi submetido (método POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém e sanitiza os dados do formulário
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Cria hash da senha
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];

    // Prepara query para verificar se email já existe
    $sql_check = "SELECT * FROM Usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email); // 's' indica que é uma string
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    // Verifica se o email já está cadastrado
    if ($result_check->num_rows == 0) {
        // Prepara query para inserir novo usuário
        $sql = "INSERT INTO Usuarios (email, senha, tipo, nome) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $senha, $tipo, $nome);

        // Executa a inserção
        if ($stmt->execute()) {
            // Redireciona para login se cadastro for bem-sucedido
            header('Location: login_usuario.php');
            exit();
        } else {
            $erro = "Erro ao cadastrar"; // Mensagem de erro genérica
        }
        $stmt->close(); // Fecha a declaração
    } else {
        $erro = "Erro! O e-mail já está cadastrado."; // Erro específico
    }
    $stmt_check->close(); // Fecha a declaração de verificação
}
$conn->close(); // Fecha a conexão com o banco
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - Cadastro</title>
    <link rel="stylesheet" href="CSS/Login.css"> <!-- Estilo principal -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Ícones -->
</head>
<body>
    
    <div class="container">
        <!-- Seção informativa -->
        <div class="info-section">
            <h1 class="logo">FacilitaU</h1> <!-- Logo/Marca -->
            <h2>Bem-vindo ao FacilitaU</h2> <!-- Título -->
            <p>A plataforma que simplifica a vida acadêmica.</p> <!-- Slogan -->
            
            <!-- Caixa de login para quem já tem conta -->
            <div class="login-box">
                <p>Já possui uma conta?</p>
                <a href="login_usuario.php" class="btn btn-secondary">Acessar conta</a>
            </div>
        </div>

        <!-- Seção do formulário de cadastro -->
        <div class="form-section">
            <div id="register-form">
                <h2>Crie sua conta</h2> <!-- Título do formulário -->
                
                <!-- Formulário de cadastro -->
                <form action="cadastro_usuario.php" method="POST">
                    <!-- Campo Nome Completo -->
                    <div class="input-group">
                        <label for="nome">Nome Completo</label>
                        <i class="fas fa-user input-icon"></i> <!-- Ícone -->
                        <input type="text" name="nome" id="nome" required> <!-- Campo obrigatório -->
                    </div>
                    
                    <!-- Campo E-mail -->
                    <div class="input-group">
                        <label for="email">E-mail institucional</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" required>
                    </div>
                    
                    <!-- Campo Senha -->
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="senha" id="senha" required>
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i> <!-- Ícone para mostrar senha -->
                    </div>
                    
                    <!-- Campo Confirmar Senha -->
                    <div class="input-group">
                        <label for="confirmar_senha">Confirmar Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirmar_senha" required>
                    </div>
                    
                    <!-- Seletor de Tipo de Usuário -->
                    <label for="tipo">Tipo de Usuário:</label>
                    <select name="tipo" id="tipo" required>
                        <option value="">Selecione</option>
                        <option value="estudante">Estudante</option>
                        <option value="professor">Professor</option>
                        <option value="coordenador">Coordenador</option>
                    </select>
                    
                    <!-- Botão de Submissão -->
                    <button type="submit" name="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Script JavaScript para funcionalidades extras -->
    <script src="JS/Login.js"></script>
</body>
</html>