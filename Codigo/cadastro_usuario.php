<?php
session_start();
include 'config.php';
include 'idiomas.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo = $_POST['tipo'];

    // Verificar se o e-mail já existe
    $sql = "SELECT COUNT(*) FROM Usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_row()[0];

    if ($count > 0) {
        $msg = traduzir('email_ja_cadastrado');
    } else {
        try {
            $sql = "INSERT INTO Usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

            if ($stmt->execute()) {
                $msg = traduzir('usuario_cadastrado_sucesso');
            } else {
                $msg = traduzir('erro_cadastrar_usuario');
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Código de erro MySQL pra duplicidade
                $msg = traduzir('email_ja_cadastrado');
            } else {
                $msg = traduzir('erro_cadastrar_usuario') . ': ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $idioma; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo traduzir('cadastro_usuario'); ?> - FacilitaU</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="claro">
    <header>
        <h1><?php echo traduzir('cadastro_usuario'); ?></h1>
        <div>
            <a href="?idioma=pt">Português</a> | 
            <a href="?idioma=en">English</a>
        </div>
    </header>
    <main>
        <?php if ($msg) echo "<p role='alert'>$msg</p>"; ?>
        <form method="POST">
            <label for="nome"><?php echo traduzir('nome'); ?>:</label>
            <input type="text" id="nome" name="nome" required aria-required="true">

            <label for="email"><?php echo traduzir('email'); ?>:</label>
            <input type="email" id="email" name="email" required aria-required="true">

            <label for="senha"><?php echo traduzir('senha'); ?>:</label>
            <input type="password" id="senha" name="senha" required aria-required="true">

            <label for="tipo"><?php echo traduzir('tipo'); ?>:</label>
            <select id="tipo" name="tipo" required aria-required="true">
                <option value="estudante"><?php echo traduzir('estudante'); ?></option>
                <option value="professor"><?php echo traduzir('professor'); ?></option>
                <option value="coordenador"><?php echo traduzir('coordenador'); ?></option>
            </select>

            <button type="submit"><?php echo traduzir('cadastrar'); ?></button>
        </form>
        <p><a href="login_usuario.php"><?php echo traduzir('logar'); ?></a></p>
    </main>
</body>
</html>