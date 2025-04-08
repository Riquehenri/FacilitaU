<?php
$page_title = "Listar Avisos";
include 'header.php';
include 'config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM AvisosComAutor ORDER BY data_publicacao DESC";
$result = $conn->query($sql);
?>

<h2>Listar Avisos</h2>
<?php
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Tipo</th><th>Título</th><th>Descrição</th><th>Autor</th><th>Data de Publicação</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . ucfirst($row['tipo_aviso']) . "</td>";
        echo "<td>" . $row['titulo'] . "</td>";
        echo "<td>" . $row['descricao'] . "</td>";
        echo "<td>" . $row['autor'] . " (" . ucfirst($row['tipo_autor']) . ")</td>";
        echo "<td>" . $row['data_publicacao'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nenhum aviso publicado.</p>";
}
$conn->close();
?>

</div>
</body>
</html>