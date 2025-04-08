<?php
$page_title = "Menu Estudante";
include 'header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();
}
?>

<h2>Bem-vindo, <?php echo $_SESSION['nome']; ?>!</h2>
<ul>
    <li><a href="planejamento_estudos.php">Planejamento de Estudos</a></li>
    <li><a href="listar_avisos.php">Listar Avisos</a></li>
</ul>

</div>
</body>
</html>