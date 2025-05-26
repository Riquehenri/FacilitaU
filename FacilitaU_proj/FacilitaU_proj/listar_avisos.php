<?php
session_start();

$page_title = "Listar Avisos";
include 'config.php';
include 'header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();
}

$busca = isset($_GET['busca']) ? $_GET['busca'] : '';
$where = '';
if (!empty($busca)) {
    $where = " WHERE titulo LIKE '%".$conn->real_escape_string($busca)."%' 
               OR descricao LIKE '%".$conn->real_escape_string($busca)."%'";
}

$sql = "SELECT * FROM AvisosComAutor $where ORDER BY data_publicacao DESC";
$result = $conn->query($sql);
?>
<link rel="stylesheet" href="CSS/Busca_lista.css">
<link rel="stylesheet" href="CSS/listar_avisos.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="header-container">
    <h2>Listar Avisos</h2>
    <div class="search-container">
        <form action="" method="get">
            <input type="text" name="busca" placeholder="Pesquisar avisos..." value="<?php echo htmlspecialchars($busca); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

<?php
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Tipo</th><th>Título</th><th>Descrição</th><th>Autor</th><th>Data de Publicação</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . ucfirst($row['tipo_aviso']) . "</td>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descricao']) . "</td>";
        echo "<td>" . htmlspecialchars($row['autor']) . " (" . ucfirst($row['tipo_autor']) . ")</td>";
        echo "<td>" . $row['data_publicacao'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nenhum aviso " . (!empty($busca) ? "encontrado para '".htmlspecialchars($busca)."'" : "publicado") . ".</p>";
}
$conn->close();
?>

</div>
<script src="JS/Vlibras.js"></script>
</body>
</html>