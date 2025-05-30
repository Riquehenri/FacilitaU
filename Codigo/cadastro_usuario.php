<?php
$page_title = "Cadastro de Usuário"; // Define o título da página
include 'config.php'; // Inclui o arquivo de configuração (como conexão com o banco)
include 'header.php'; // Inclui o cabeçalho da página

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados do formulário
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $curso_id = $_POST['curso_id'];

    // Calcula a data mínima para validar idade (16 anos)
    $data_minima = date('Y-m-d', strtotime('-16 years'));
    if ($data_nascimento > $data_minima) {
        $erro = "Você deve ter pelo menos 16 anos para se cadastrar."; // Mensagem de erro se for menor de idade
    }

    // Se não houve erro de idade
    if (!isset($erro)) {
        // Verifica se o e-mail já está cadastrado
        $sql_check = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        // Se o e-mail ainda não existe
        if ($result_check->num_rows == 0) {
            // Insere novo usuário
            $sql = "INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $email, $senha, $tipo, $nome, $data_nascimento, $telefone, $curso_id);

            if ($stmt->execute()) {
                // Redireciona para login se cadastro for bem-sucedido
                header('Location: login_usuario.php');
                exit();
            } else {
                // Erro ao inserir no banco
                $erro = "Erro ao cadastrar: " . $conn->error;
            }

            $stmt->close(); // Fecha statement de inserção
        } else {
            $erro = "Erro! O e-mail já está cadastrado."; // E-mail já usado
        }

        $stmt_check->close(); // Fecha statement de verificação
    }
}

// Busca lista de cursos no banco para exibir no formulário
$cursos = [];
$sql_cursos = "SELECT curso_id, nome FROM Cursos ORDER BY nome";
$result_cursos = $conn->query($sql_cursos);
if ($result_cursos->num_rows > 0) {
    while ($row = $result_cursos->fetch_assoc()) {
        $cursos[] = $row; // Armazena os cursos em um array
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
  <link rel="stylesheet" href="CSS/Login.css"> <!-- Estilo da página -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Ícones FontAwesome -->
</head>
<body>
  <div class="container">
    <!-- Seção informativa da esquerda -->
    <div class="info-section">
      <h1 class="logo">FacilitaU</h1>
      <h2>Bem-vindo ao FacilitaU</h2>
      <p>A plataforma que simplifica a vida acadêmica.</p>
      <div class="login-box">
        <p>Já possui uma conta?</p>
        <a href="login_usuario.php" class="btn btn-secondary">Acessar conta</a> <!-- Link para login -->
      </div>
    </div>

    <!-- Seção do formulário de cadastro -->
    <div class="form-section">
      <div id="register-form">
        <h2>Crie sua conta</h2>
        <!-- Exibe erro, se houver -->
        <?php if (isset($erro)): ?>
          <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <!-- Formulário de cadastro -->
        <form action="cadastro_usuario.php" method="POST" onsubmit="return validarFormulario()">
          <!-- Nome completo -->
          <div class="input-group">
            <label for="nome">Nome Completo</label>
            <i class="fas fa-user input-icon"></i>
            <input type="text" name="nome" id="nome" required>
          </div>

          <!-- E-mail -->
          <div class="input-group">
            <label for="email">E-mail institucional</label>
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" name="email" id="email" placeholder="seu.email@instituicao.edu.br" required>
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
            <input type="tel" name="telefone" id="telefone" placeholder="(XX) 9XXXX-XXXX" oninput="mascaraTelefone(event)" required>
          </div>

          <!-- Senha -->
          <div class="input-group">
            <label for="senha">Senha</label>
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="senha" id="senha" placeholder="********" required>
            <i class="fas fa-eye password-toggle" id="toggle-password"></i>
          </div>

          <!-- Confirmar senha (não é enviado, apenas validado no front-end) -->
          <div class="input-group">
            <label for="confirmar_senha">Confirmar Senha</label>
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="confirmar_senha" placeholder="********" required>
            <i class="fas fa-eye password-toggle" id="toggle-password"></i>
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

          <!-- Curso (selecionado dinamicamente do banco) -->
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

          <!-- Botão de envio -->
          <button type="submit" name="submit" class="btn btn-primary">Cadastrar</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts JavaScript adicionais -->
  <script src="JS/Login.js"></script> <!-- Funções de login (olho da senha etc.) -->
  <script src="JS/Vlibras.js"></script> <!-- Acessibilidade -->
  <script src="JS/validar_cad.js"></script> <!-- Validações de formulário -->
  
</body>
</html>
