<?php
session_start();
$page_title = "Planejamento de Estudos";
include 'config.php';
include 'header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $sql = "DELETE FROM Planejamento_Estudos WHERE planejamento_id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $usuario_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Planejamento excluído com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "Erro ao excluir: " . $conn->error;
        $_SESSION['tipo_mensagem'] = "error";
    }
    $stmt->close();
    header("Location: planejamento_estudos.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dia_semana = $_POST['dia_semana'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $atividade = $_POST['atividade'];
    
    if (strtotime($horario_fim) <= strtotime($horario_inicio)) {
        $_SESSION['mensagem'] = "O horário de término deve ser após o horário de início!";
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: planejamento_estudos.php");
        exit();
    }

    if (isset($_POST['editar_id'])) {
        $id = $_POST['editar_id'];
        $sql = "UPDATE Planejamento_Estudos SET dia_semana = ?, horario_inicio = ?, horario_fim = ?, atividade = ? 
                WHERE planejamento_id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $dia_semana, $horario_inicio, $horario_fim, $atividade, $id, $usuario_id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Planejamento atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    } else {
        $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Planejamento cadastrado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    }
    header("Location: planejamento_estudos.php");
    exit();
}

$editar_id = null;
$editar_dados = null;
if (isset($_GET['editar'])) {
    $editar_id = $_GET['editar'];
    $sql = "SELECT * FROM Planejamento_Estudos WHERE planejamento_id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $editar_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editar_dados = $result->fetch_assoc();
    $stmt->close();
}

$sql = "SELECT * FROM Planejamento_Estudos WHERE usuario_id = ? ORDER BY 
        FIELD(dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'), 
        horario_inicio";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if (isset($_SESSION['mensagem'])) {
    echo "<p class='" . $_SESSION['tipo_mensagem'] . "'>" . $_SESSION['mensagem'] . "</p>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>
<link rel="stylesheet" href="CSS/planejar_estudos.css">
<link rel="stylesheet" href="CSS/editarPlanejamento.css">
<script>
function validarHorarios() {
    const inicio = document.getElementById('horario_inicio').value;
    const fim = document.getElementById('horario_fim').value;
    
    if (fim && inicio && fim <= inicio) {
        alert('O horário de término deve ser após o horário de início!');
        return false;
    }
    return true;
}
</script>

<h2>Planejamento de Estudos</h2>

<h3><?php echo isset($editar_id) ? "Editar Planejamento" : "Cadastrar Novo Planejamento"; ?></h3>
<form method="POST" action="planejamento_estudos.php" onsubmit="return validarHorarios()">
    <?php if (isset($editar_id)): ?>
        <input type="hidden" name="editar_id" value="<?php echo $editar_id; ?>">
    <?php endif; ?>
    
    <label for="dia_semana">Dia da Semana:</label>
    <select name="dia_semana" id="dia_semana" required>
        <option value="">Selecione</option>
        <?php
        $dias_semana = [
            'segunda' => 'Segunda-feira',
            'terca' => 'Terça-feira',
            'quarta' => 'Quarta-feira',
            'quinta' => 'Quinta-feira',
            'sexta' => 'Sexta-feira',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo'
        ];
        
        foreach ($dias_semana as $valor => $nome) {
            $selected = (isset($editar_dados) && $editar_dados['dia_semana'] == $valor) ? 'selected' : '';
            echo "<option value='$valor' $selected>$nome</option>";
        }
        ?>
    </select>

    <label for="horario_inicio">Horário de Início:</label>
    <input type="time" name="horario_inicio" id="horario_inicio" 
           value="<?php echo isset($editar_dados) ? $editar_dados['horario_inicio'] : ''; ?>" required>

    <label for="horario_fim">Horário de Fim:</label>
    <input type="time" name="horario_fim" id="horario_fim" 
           value="<?php echo isset($editar_dados) ? $editar_dados['horario_fim'] : ''; ?>" required min="<?php echo isset($editar_dados) ? $editar_dados['horario_inicio'] : ''; ?>">

    <label for="atividade">Atividade:</label>
    <input type="text" name="atividade" id="atividade" 
           value="<?php echo isset($editar_dados) ? htmlspecialchars($editar_dados['atividade']) : ''; ?>" required>

    <button type="submit"><?php echo isset($editar_id) ? "Atualizar" : "Cadastrar"; ?></button>
    
    <?php if (isset($editar_id)): ?>
        <a href="planejamento_estudos.php" class="cancel-button">Cancelar</a>
    <?php endif; ?>
</form>

<h3>Seu Planejamento</h3>
<?php
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Dia da Semana</th><th>Horário Início</th><th>Horário Fim</th><th>Atividade</th><th>Ações</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $dias_semana[$row['dia_semana']] . "</td>";
        echo "<td>" . substr($row['horario_inicio'], 0, 5) . "</td>";
        echo "<td>" . substr($row['horario_fim'], 0, 5) . "</td>";
        echo "<td>" . htmlspecialchars($row['atividade']) . "</td>";
        echo "<td class='actions'>";
        echo "<a href='planejamento_estudos.php?editar=" . $row['planejamento_id'] . "' class='edit-btn'>Editar</a> ";
        echo "<a href='planejamento_estudos.php?excluir=" . $row['planejamento_id'] . "' class='delete-btn' onclick='return confirm(\"Tem certeza que deseja excluir este planejamento?\")'>Excluir</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nenhum planejamento cadastrado.</p>";
}
$stmt->close();
$conn->close();
?>

<script>
document.getElementById('horario_inicio').addEventListener('change', function() {
    document.getElementById('horario_fim').min = this.value;
});
</script>

</div>
    <script src="JS/Vlibras.js"></script>

</body>
</html>