<?php
$page_title = "Menu Professor";
include 'header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'professor') {
    header("Location: index.php");
    exit();
}
?>

<h2>Bem-vindo, <?php echo $_SESSION['nome']; ?>!</h2>
<ul>
    <li><a href="cadastrar_aviso.php">Cadastrar Aviso</a></li>
</ul>

</div>
</body>
</html>