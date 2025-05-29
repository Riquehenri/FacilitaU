<?php
// Define o título da página
$page_title = "Cadastro de Usuário";

// Inclui arquivos com configurações do banco e o cabeçalho
include 'config.php';
include 'header.php';

// Função que valida o telefone: deve ter DDD + 9 dígitos, totalizando 11 números
function validarTelefone($telefone) {
    // Remove tudo que não for número
    $telefone = preg_replace('/[^0-9]/', '', $telefone);

    // Verifica se o número tem exatamente 11 dígitos
    if (strlen($telefone) != 11) {
        return false;
    }

    // Verifica se o 3º dígito é 9 (nono dígito obrigatório em celulares)
    if ($telefone[2] != '9') {
        return false;
    }

    return true; // Telefone válido
}

// Se o formulário foi enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados enviados pelo formulário
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $curso_id = $_POST['curso_id'];

    // Verifica se a idade é de pelo menos 16 anos
    $data_minima = date('Y-m-d', strtotime('-16 years'));
    if ($data_nascimento > $data_minima) {
        $erro = "Você deve ter pelo menos 16 anos para se cadastrar.";
    }

    // Valida o telefone
    if (!validarTelefone($telefone)) {
        $erro = "Telefone inválido. Informe um número com DDD e 9 dígitos (ex: 11987654321)";
    }

    // Se não houver erros, prossegue com o cadastro
    if (!isset($erro)) {
        // Verifica se já existe um usuário com o mesmo e-mail
        $sql_check = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        // Se o e-mail não existe, pode cadastrar
        if ($result_check->num_rows == 0) {
            // Formata o telefone no padrão (XX) XXXXX-XXXX
            $telefone_formatado = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);

            // Cria a SQL de inserção
            $sql = "INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $email, $senha, $tipo, $nome, $data_nascimento, $telefone_formatado, $curso_id);

            // Executa a inserção
            if ($stmt->execute()) {
                header('Location: login_usuario.php'); // Redireciona para o login
                exit();
            } else {
                $erro = "Erro ao cadastrar: " . $conn->error;
            }

            $stmt->close();
        } else {
            $erro = "Erro! O e-mail já está cadastrado.";
        }

        $stmt_check->close();
    }
}

// Busca todos os cursos para preencher o campo <select> do formulário
$cursos = [];
$sql_cursos = "SELECT curso_id, nome FROM Cursos ORDER BY nome";
$result_cursos = $conn->query($sql_cursos);
if ($result_cursos->num_rows > 0) {
    while ($row = $result_cursos->fetch_assoc()) {
        $cursos[] = $row;
    }
}

$conn->close(); // Fecha conexão com o banco
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
        <!-- Lado esquerdo com informações -->
        <div class="info-section">
            <h1 class="logo">FacilitaU</h1>
            <h2>Bem-vindo ao FacilitaU</h2>
            <p>A plataforma que simplifica a vida acadêmica.</p>
            <div class="login-box">
                <p>Já possui uma conta?</p>
                <a href="login_usuario.php" class="btn btn-secondary">Acessar conta</a>
            </div>
        </div>

        <!-- Formulário de cadastro -->
        <div class="form-section">
            <div id="register-form">
                <h2>Crie sua conta</h2>
                <!-- Exibe mensagem de erro, se houver -->
                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <form action="cadastro_usuario.php" method="POST" onsubmit="return validarFormulario()">
                    <!-- Nome -->
                    <div class="input-group">
                        <label for="nome">Nome Completo</label>
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="nome" id="nome" required>
                    </div>

                    <!-- E-mail -->
                    <div class="input-group">
                        <label for="email">E-mail institucional</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <!-- Data de nascimento -->
                    <div class="input-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="date" name="data_nascimento" id="data_nascimento"
                               max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>" required>
                    </div>

                    <!-- Telefone -->
                    <div class="input-group">
                        <label for="telefone">Telefone (com DDD)</label>
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="telefone" id="telefone" oninput="mascaraTelefone(event)" required>
                        <small class="form-text">Formato: (XX) XXXXX-XXXX</small>
                    </div>

                    <!-- Senha -->
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="senha" id="senha" required>
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i>
                    </div>

                    <!-- Confirmação de senha -->
                    <div class="input-group">
                        <label for="confirmar_senha">Confirmar Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirmar_senha" required>
                    </div>

                    <!-- Tipo de usuário -->
                    <div class="input-group">
                        <label for="tipo">Tipo de Usuário:</label>
                        <select name="tipo" id="tipo" required>
                            <option value="">Selecione</option>
                            <option value="estudante">Estudante</option>
                            <option value="professor">Professor</option>
                            <option value="coordenador">Coordenador</option>
                        </select>
                    </div>

                    <!-- Curso -->
                    <div class="input-group">
                        <label for="curso_id">Curso:</label>
                        <select name="curso_id" id="curso_id" required>
                            <option value="">Selecione seu curso</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?php echo $curso['curso_id']; ?>">
                                    <?php echo htmlspecialchars($curso['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Botão de cadastro -->
                    <button type="submit" name="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="JS/Login.js"></script>
    <script src="JS/Vlibras.js"></script>
    <script src="JS/cadastrar.js"></script>
</body>
</html>
