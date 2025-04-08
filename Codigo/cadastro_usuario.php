<?php
$page_title = "Cadastro de Usuário";
include 'header.php';
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];

    $sql_check = "SELECT * FROM Usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        $sql = "INSERT INTO Usuarios (email, senha, tipo, nome) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $senha, $tipo, $nome);

        if ($stmt->execute()) {
            echo "<p class='success'>Cadastro realizado com sucesso! <a href='login_usuario.php'>Faça login</a></p>";
        } else {
            echo "<p class='error'>Erro ao cadastrar: " . $conn->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p class='error'>Este e-mail já está cadastrado.</p>";
    }
    $stmt_check->close();
}
$conn->close();
?>

<h2>Cadastro de Usuário</h2>
<form method="POST" action="cadastro_usuario.php">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" id="nome" required>

    <label for="email">E-mail:</label>
    <input type="email" name="email" id="email" required>

    <label for="senha">Senha:</label>
    <input type="password" name="senha" id="senha" required>

    <label for="tipo">Tipo de Usuário:</label>
    <select name="tipo" id="tipo" required>
        <option value="">Selecione</option>
        <option value="estudante">Estudante</option>
        <option value="professor">Professor</option>
        <option value="coordenador">Coordenador</option>
    </select>

    <button type="submit">Cadastrar</button>
</form>

</div>
</body>
</html>