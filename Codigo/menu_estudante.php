<?php
session_start();
include 'config.php';
include 'idiomas.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: login_usuario.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Mudar tema
if (isset($_GET['tema'])) {
    $tema = $_GET['tema'];
    $sql = "UPDATE Usuarios SET tema = ? WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $tema, $usuario_id);
    $stmt->execute();
    header("Location: menu_estudante.php");
    exit();
}

// Obter tema do usuário
$sql = "SELECT tema FROM Usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$tema = $stmt->get_result()->fetch_assoc()['tema'];
?>

<!DOCTYPE html>
<html lang="<?php echo $idioma; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo traduzir('menu_estudante'); ?> - FacilitaU</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $tema; ?>">
    <header>
        <h1><?php echo traduzir('menu_estudante'); ?></h1>
        <div>
            <a href="?idioma=pt">Português</a> | 
            <a href="?idioma=en">English</a>
        </div>
        <div>
            <a href="?tema=claro"><?php echo traduzir('tema_claro'); ?></a> | 
            <a href="?tema=escuro"><?php echo traduzir('tema_escuro'); ?></a>
        </div>
    </header>
    <main>
        <p>Escolha uma opção:</p>
        <a href="planejamento_estudos.php">Planejamento de Estudos</a><br>
        <!-- Links pra outras funcionalidades serão adicionados depois -->
        <a href="logout.php">Sair</a>
    </main>
</body>
</html>