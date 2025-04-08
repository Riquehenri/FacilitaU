<?php
$page_title = "Login";
include 'header.php';
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['recuperar'])) {
        $email = $_POST['email'];
        $sql = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $nova_senha = bin2hex(random_bytes(8));
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql_proc = "CALL AtualizarSenhaUsuario(?, ?)";
            $stmt_proc = $conn->prepare($sql_proc);
            $stmt_proc->bind_param("ss", $email, $senha_hash);
            if ($stmt_proc->execute()) {
                echo "<p class='success'>Nova senha temporária: $nova_senha. Faça login e altere-a.</p>";
            } else {
                echo "<p class='error'>Erro ao redefinir senha: " . $conn->error . "</p>";
            }
            $stmt_proc->close();
        } else {
            echo "<p class='error'>E-mail não encontrado.</p>";
        }
        $stmt->close();
    } else {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $sql = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($senha, $row['senha'])) {
                $_SESSION['usuario_id'] = $row['usuario_id'];
                $_SESSION['nome'] = $row['nome'];
                $_SESSION['tipo'] = $row['tipo'];
                if ($row['tipo'] == 'estudante') {
                    header("Location: menu_estudante.php");
                } elseif ($row['tipo'] == 'professor') {
                    header("Location: menu_professor.php");
                } else {
                    header("Location: menu_coordenador.php");
                }
                exit();
            } else {
                echo "<p class='error'>E-mail ou senha incorretos.</p>";
            }
        } else {
            echo "<p class='error'>E-mail ou senha incorretos.</p>";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<h2>Login</h2>
<form method="POST" action="login_usuario.php">
    <label for="email">E-mail:</label>
    <input type="email" name="email" id="email" required>
    <label for="senha">Senha:</label>
    <input type="password" name="senha" id="senha" required>
    <button type="submit">Login</button>
</form>

<h3>Recuperar Senha</h3>
<form method="POST" action="login_usuario.php">
    <input type="hidden" name="recuperar" value="1">
    <label for="email_recuperar">E-mail:</label>
    <input type="email" name="email" id="email_recuperar" required>
    <button type="submit">Recuperar Senha</button>
</form>

</div>
</body>
</html>