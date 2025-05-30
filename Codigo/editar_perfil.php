<?php
session_start(); // Inicia a sessão para acessar variáveis de sessão

$page_title = "Editar Perfil";
include 'config.php'; // Inclui configurações como conexão com o banco de dados
include 'header.php'; // Inclui o cabeçalho da página

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php"); // Redireciona para login se não estiver logado
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Consulta dados do usuário e o nome do curso associado
$sql = "SELECT u.*, c.nome as nome_curso FROM Usuarios u 
        LEFT JOIN Cursos c ON u.curso_id = c.curso_id 
        WHERE u.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Usuário não encontrado."); // Encerra se usuário não for encontrado
}

$usuario = $result->fetch_assoc();
$stmt->close();

// Busca todos os cursos para preencher o dropdown
$cursos = [];
$sql_cursos = "SELECT curso_id, nome FROM Cursos ORDER BY nome";
$result_cursos = $conn->query($sql_cursos);
if ($result_cursos->num_rows > 0) {
    while($row = $result_cursos->fetch_assoc()) {
        $cursos[] = $row; // Adiciona cursos ao array
    }
}

$erros = []; // Inicializa array de erros

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']); // Remove espaços do nome
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']); // Remove caracteres não numéricos
    $curso_id = intval($_POST['curso_id']); // Converte para inteiro

    // Validação do telefone: deve ter 11 dígitos e começar com 9 após o DDD
    if (strlen($telefone) != 11 || $telefone[2] != '9') {
        $erros[] = "Telefone inválido. Informe um número com DDD e 9 dígitos.";
    }

    // Validação de idade mínima: 16 anos
    $data_minima = date('Y-m-d', strtotime('-16 years'));
    if ($data_nascimento > $data_minima) {
        $erros[] = "Você deve ter pelo menos 16 anos.";
    }

    // Se não houver erros, atualiza os dados
    if (empty($erros)) {
        $sql_update = "UPDATE Usuarios SET 
                        nome = ?, 
                        data_nascimento = ?, 
                        telefone = ?, 
                        curso_id = ? 
                      WHERE usuario_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssii", $nome, $data_nascimento, $telefone, $curso_id, $usuario_id);

        if ($stmt_update->execute()) {
            header("Location: perfil.php"); // Redireciona ao perfil após salvar
            exit();
        } else {
            $erros[] = "Erro ao atualizar perfil: " . $conn->error;
        }
        $stmt_update->close();
    }
}

$conn->close(); // Fecha conexão com o banco

// Define ícone e cor com base no tipo de usuário
switch ($usuario['tipo']) {
    case 'coordenador':
        $icone = 'fa-user-shield';
        $cor = '#4a90e2';
        break;
    case 'professor':
        $icone = 'fa-user-tie';
        $cor = '#2ecc71';
        break;
    default:
        $icone = 'fa-user-graduate';
        $cor = '#e74c3c';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $page_title; ?></title>

    <!-- Font Awesome e CSS externo -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="CSS/perfil.css" />
    <link rel="stylesheet" href="CSS/editarperfil.css" />

    <!-- Estilo dinâmico baseado no tipo de usuário -->
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
        <!-- Ícone e título da página -->
        <div class="text-center mb-4">
            <div class="profile-icon" style="font-size: 3rem;">
                <i class="fas <?php echo $icone; ?>"></i>
            </div>
            <h2>Editar Perfil</h2>
        </div>

        <!-- Exibição de erros, se existirem -->
        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <?php foreach ($erros as $erro): ?>
                    <p><?php echo htmlspecialchars($erro); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de edição de perfil -->
        <form method="POST" onsubmit="return validarFormulario()">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" 
                       value="<?php echo htmlspecialchars($usuario['nome']); ?>" required />
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" class="form-control" 
                       value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled />
                <small class="text-muted">Para alterar o e-mail, entre em contato com o suporte.</small>
            </div>

            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" class="form-control"
                       max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>"
                       value="<?php echo $usuario['data_nascimento']; ?>" required />
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" class="form-control"
                       value="<?php echo $usuario['telefone'] ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $usuario['telefone']) : ''; ?>"
                       oninput="mascaraTelefone(event)" placeholder="(XX) 9XXXX-XXXX" required />
                <small class="text-muted">Formato: (XX) 9XXXX-XXXX</small>
            </div>

            <div class="form-group">
                <label for="curso_id">Curso</label>
                <select id="curso_id" name="curso_id" class="form-control" required>
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
                <input type="text" class="form-control" value="<?php echo ucfirst($usuario['tipo']); ?>" disabled />
            </div>

            <!-- Botões -->
            <button type="submit" class="btn btn-primary btn-block">Salvar Alterações</button>
            <button href="perfil.php" class="btn btn-secondary btn-block mt-2">Cancelar</button>
        </form>
    </div>

    <!-- Scripts adicionais -->
    <script src="JS/Vlibras.js"></script>
    <script src="JS/editarperfil.js"></script>
</body>
</html>
