<?php
$page_title = "Cadastro de Usuário";
include 'config.php';
include 'header.php';

// Função para validar telefone com DDD e 9 dígitos
function validarTelefone($telefone) {
    // Remove todos os caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica se tem 11 dígitos (DDD + 9 dígitos)
    if(strlen($telefone) != 11) {
        return false;
    }
    
    // Verifica se o 3º dígito é 9 (formato com nono dígito)
    if($telefone[2] != '9') {
        return false;
    }
    
    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dados básicos
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    
    // Novos campos adicionados
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $curso_id = $_POST['curso_id'];

    // Validação da data de nascimento (mínimo 16 anos)
    $data_minima = date('Y-m-d', strtotime('-16 years'));
    if ($data_nascimento > $data_minima) {
        $erro = "Você deve ter pelo menos 16 anos para se cadastrar.";
    }
    
    // Validação do telefone
    if (!validarTelefone($telefone)) {
        $erro = "Telefone inválido. Informe um número com DDD e 9 dígitos (ex: 11987654321)";
    }

    // Só continua se não houver erros
    if (!isset($erro)) {
        // Verificar se email já existe
        $sql_check = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            // Formata o telefone para (XX) XXXXX-XXXX
            $telefone_formatado = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
            
            // Inserir usuário com todos os campos
            $sql = "INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $email, $senha, $tipo, $nome, $data_nascimento, $telefone_formatado, $curso_id);

            if ($stmt->execute()) {
                header('Location: login_usuario.php');
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

// Buscar cursos disponíveis para o dropdown
$cursos = [];
$sql_cursos = "SELECT curso_id, nome FROM Cursos ORDER BY nome";
$result_cursos = $conn->query($sql_cursos);
if ($result_cursos->num_rows > 0) {
    while($row = $result_cursos->fetch_assoc()) {
        $cursos[] = $row;
    }
}
$conn->close();
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
                <a href="login_usuario.php" class="btn btn-secondary">Acessar conta</a>
            </div>
        </div>

        <div class="form-section">
            <div id="register-form">
                <h2>Crie sua conta</h2>
                <?php if(isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>
                <form action="cadastro_usuario.php" method="POST" onsubmit="return validarFormulario()">
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
                        <label for="data_nascimento">Data de Nascimento</label>
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="date" name="data_nascimento" id="data_nascimento" 
                               max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="telefone">Telefone (com DDD)</label>
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="telefone" id="telefone"  oninput="mascaraTelefone(event)" required>
                        <small class="form-text">Formato: (XX) XXXXX-XXXX</small>
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
                    
                    <div class="input-group">
                        <label for="tipo">Tipo de Usuário:</label>
                        <select name="tipo" id="tipo" required>
                            <option value="">Selecione</option>
                            <option value="estudante">Estudante</option>
                            <option value="professor">Professor</option>
                            <option value="coordenador">Coordenador</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="curso_id">Curso:</label>
                        <select name="curso_id" id="curso_id" required>
                            <option value="">Selecione seu curso</option>
                            <?php foreach($cursos as $curso): ?>
                                <option value="<?php echo $curso['curso_id']; ?>">
                                    <?php echo htmlspecialchars($curso['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="JS/Login.js"></script>
    <script src="JS/Vlibras.js"></script>
    <script src="JS/cadastrar.js"></script>
</body>
</html>