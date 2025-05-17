<?php
session_start();
include 'config.php';
include 'idiomas.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT usuario_id, tipo FROM Usuarios WHERE email = ? AND senha = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $_SESSION['usuario_id'] = $usuario['usuario_id'];
        $_SESSION['tipo'] = $usuario['tipo'];
        $_SESSION['ultima_atividade'] = time();
        if (isset($_POST['lembrar']) && $_POST['lembrar'] == 1) {
            setcookie('email', $email, time() + (7 * 86400)); // 7 dias
        }
        header("Location: verificar_2fa.php");
        exit();
    } else {
        $msg = traduzir('login_invalido');
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $idioma; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo traduzir('login_usuario'); ?> - FacilitaU</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="claro">
    <header>
        <h1><?php echo traduzir('login_usuario'); ?></h1>
        <div>
            <a href="?idioma=pt">Português</a> | 
            <a href "?idioma=en">English</a>
        </div>
    </header>
    <main>
        <?php if ($msg) echo "<p role='alert'>$msg</p>"; ?>
        <form method="POST">
            <label for="email"><?php echo traduzir('email'); ?>:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_COOKIE['email']) ? $_COOKIE['email'] : ''; ?>" required aria-required="true">
            <label for="senha"><?php echo traduzir('senha'); ?>:</label>
            <input type="password" id="senha" name="senha" required aria-required="true">
            <label>
                <input type="checkbox" name="lembrar" value="1"> <?php echo traduzir('lembrar_me'); ?>
            </label>
            <button type="submit"><?php echo traduzir('logar'); ?></button>
        </form>
        <p><a href="cadastro_usuario.php"><?php echo traduzir('cadastrar'); ?></a></p>
    </main>
</body>
</html>