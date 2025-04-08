<?php
$page_title = "Cadastrar Aviso";
include 'header.php';
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'], ['coordenador', 'professor'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_aviso = $_POST['tipo_aviso'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_publicacao = date('Y-m-d');

    $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_publicacao);

    if ($stmt->execute()) {
        $aviso_id = $conn->insert_id;
        $sql_proc = "CALL InserirNotificacaoAviso(?, ?, ?, ?)";
        $stmt_proc = $conn->prepare($sql_proc);
        $stmt_proc->bind_param("iiss", $aviso_id, $usuario_id, $titulo, $data_publicacao);
        $stmt_proc->execute();
        $stmt_proc->close();

        echo "<p class='success'>Aviso cadastrado com sucesso! Notificações enviadas aos estudantes.</p>";
    } else {
        echo "<p class='error'>Erro ao cadastrar: " . $conn->error . "</p>";
    }
    $stmt->close();
}
$conn->close();
?>

<h2>Cadastrar Aviso</h2>
<form method="POST" action="cadastrar_aviso.php">
    <label for="tipo_aviso">Tipo de Aviso:</label>
    <select name="tipo_aviso" id="tipo_aviso" required>
        <option value="aviso">Aviso Geral</option>
        <option value="oportunidade">Oportunidade de Emprego</option>
    </select>

    <label for="titulo">Título:</label>
    <input type="text" name="titulo" id="titulo" required>

    <label for="descricao">Descrição:</label>
    <textarea name="descricao" id="descricao" required></textarea>

    <button type="submit">Cadastrar Aviso</button>
</form>

</div>
</body>
</html>