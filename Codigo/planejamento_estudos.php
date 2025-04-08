<?php
$page_title = "Planejamento de Estudos";
include 'header.php';
include 'config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dia_semana = $_POST['dia_semana'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $atividade = $_POST['atividade'];

    $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade);

    if ($stmt->execute()) {
        echo "<p class='success'>Planejamento cadastrado com sucesso!</p>";
    } else {
        echo "<p class='error'>Erro ao cadastrar: " . $conn->error . "</p>";
    }
    $stmt->close();
}

$sql = "SELECT * FROM PlanejamentoPorEstudante WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Planejamento de Estudos</h2>
<h3>Cadastrar Novo Planejamento</h3>
<form method="POST" action="planejamento_estudos.php">
    <label for="dia_semana">Dia da Semana:</label>
    <select name="dia_semana" id="dia_semana" required>
        <option value="">Selecione</option>
        <option value="segunda">Segunda-feira</option>
        <option value="terca">Terça-feira</option>
        <option value="quarta">Quarta-feira</option>
        <option value="quinta">Quinta-feira</option>
        <option value="sexta">Sexta-feira</option>
        <option value="sabado">Sábado</option>
        <option value="domingo">Domingo</option>
    </select>

    <label for="horario_inicio">Horário de Início:</label>
    <input type="time" name="horario_inicio" id="horario_inicio" required>

    <label for="horario_fim">Horário de Fim:</label>
    <input type="time" name="horario_fim" id="horario_fim" required>

    <label for="atividade">Atividade:</label>
    <input type="text" name="atividade" id="atividade" required>

    <button type="submit">Cadastrar</button>
</form>

<h3>Seu Planejamento</h3>
<?php
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Dia da Semana</th><th>Horário Início</th><th>Horário Fim</th><th>Atividade</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . ucfirst($row['dia_semana']) . "</td>";
        echo "<td>" . $row['horario_inicio'] . "</td>";
        echo "<td>" . $row['horario_fim'] . "</td>";
        echo "<td>" . $row['atividade'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nenhum planejamento cadastrado.</p>";
}
$stmt->close();
$conn->close();
?>

</div>
</body>
</html>