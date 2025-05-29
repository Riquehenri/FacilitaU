<?php
session_start(); // Inicia a sessão para acessar variáveis de sessão

$page_title = "Editar Perfil"; // Define o título da página
include 'config.php'; // Inclui o arquivo de conexão com o banco de dados
include 'header.php'; // Inclui o cabeçalho (HTML) comum

// Verifica se o usuário está logado, senão redireciona para login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id']; // Obtém o ID do usuário logado

// Busca os dados atuais do usuário (inclusive nome do curso com JOIN)
$sql = "SELECT u.*, c.nome as nome_curso FROM Usuarios u 
        LEFT JOIN Cursos c ON u.curso_id = c.curso_id 
        WHERE u.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Se não encontrar o usuário, encerra com erro
if ($result->num_rows == 0) {
    die("Usuário não encontrado.");
}

$usuario = $result->fetch_assoc(); // Armazena os dados do usuário em um array associativo
$stmt->close();

// Busca todos os cursos disponíveis para preencher o dropdown
$cursos = [];
$sql_cursos = "SELECT curso_id, nome FROM Cursos ORDER BY nome";
$result_cursos = $conn->query($sql_cursos);
if ($result_cursos->num_rows > 0) {
    while($row = $result_cursos->fetch_assoc()) {
        $cursos[] = $row;
    }
}

// Processamento do formulário (quando enviado via POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados enviados
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']); // Limpa caracteres não numéricos
    $curso_id = $_POST['curso_id'];

    $erros = []; // Array para armazenar mensagens de erro

    // Validação do telefone (deve ter 11 dígitos e o terceiro ser 9)
    if (strlen($telefone) != 11 || $telefone[2] != '9') {
        $erros[] = "Telefone inválido. Informe um número com DDD e 9 dígitos.";
    }

    // Validação da data de nascimento (mínimo 16 anos)
    $data_minima = date('Y-m-d', strtotime('-16 years'));
    if ($data_nascimento > $data_minima) {
        $erros[] = "Você deve ter pelo menos 16 anos.";
    }

    // Se tudo estiver certo, atualiza o perfil no banco
    if (empty($erros)) {
        $sql_update = "UPDATE Usuarios SET 
                        nome = ?, 
                        data_nascimento = ?, 
                        telefone = ?,
                        ig = ?, 
                        curso_id = ? 
                      WHERE usuario_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        
        // Aqui ocorre um erro: está faltando um parâmetro e o tipo 'i' no bind_param
        // Corrigindo abaixo:
        $stmt_update->bind_param("sssii", $nome, $data_nascimento, $telefone, $curso_id, $usuario_id);
        
        if ($stmt_update->execute()) {
            header("Location: perfil.php"); // Redireciona para a página de perfil
            exit();
        } else {
            $erros[] = "Erro ao atualizar perfil: " . $conn->error;
        }
        $stmt_update->close();
    }
}

$conn->close(); // Fecha a conexão com o banco

// Define o ícone e a cor com base no tipo de usuário
switch ($usuario['tipo']) {
    case 'coordenador':
        $icone = 'fa-user-shield';
        $cor = '#4a90e2'; // azul
        break;
    case 'professor':
        $icone = 'fa-user-tie';
        $cor = '#2ecc71'; // verde
        break;
    default: // estudante
        $icone = 'fa-user-graduate';
        $cor = '#e74c3c'; // vermelho
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Ícones e estilos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/perfil.css">
    <link rel="stylesheet" href="CSS/editarperfil.css">

    <!-- Estilo dinâmico com base no tipo de usuário -->
    <style>
        .profile-icon {
            color: <?php echo $cor; ?>;
        }
        .btn-primary {
            background-color: <?php echo $cor; ?>;
            border-color: <?php echo $cor; ?>;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <!-- Ícone do usuário -->
        <div class="text-center mb-4">
            <div class="profile-icon" style="font-size: 3rem;">
                <i class="fas <?php echo $icone; ?>"></i>
            </div>
            <h2>Editar Perfil</h2>
        </div>

        <!-- Exibe mensagens de erro se houver -->
        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <?php foreach ($erros as $erro): ?>
                    <p><?php echo $erro; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de edição -->
        <form method="POST" onsubmit="return validarFormulario()">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" class="form-control" id="nome" name="nome" 
                       value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" class="form-control" id="email" 
                       value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                <small class="text-muted">Para alterar o e-mail, entre em contato com o suporte.</small>
            </div>

            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" 
                       value="<?php echo $usuario['data_nascimento']; ?>" 
                       max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>" required>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" class="form-control" id="telefone" name="telefone" 
                       value="<?php echo $usuario['telefone'] ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $usuario['telefone']) : ''; ?>" 
                       oninput="formatarTelefone(this)" required>
                <small class="text-muted">Formato: (XX) XXXXX-XXXX</small>
            </div>

            <div class="form-group">
                <label for="curso_id">Curso</label>
                <select class="form-control" id="curso_id" name="curso_id" required>
                    <option value="">Selecione seu curso</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['curso_id']; ?>" 
                            <?php echo ($curso['curso_id'] == $usuario['curso_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($curso['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de Usuário</label>
                <input type="text" class="form-control" 
                       value="<?php echo ucfirst($usuario['tipo']); ?>" disabled>
            </div>

            <!-- Botões -->
            <button type="submit" class="btn btn-primary btn-block">Salvar Alterações</button>
            <button href="perfil.php" class="btn btn-secondary btn-block mt-2">Cancelar</button>
        </form>
    </div>

    <!-- Scripts -->
    <script src="JS/Vlibras.js"></script>
    <script src="JS/editarperfil.js"></script>
</body>
</html>
