<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
$page_title = "Cadastrar Aviso";
include 'config.php';
include 'header.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'], ['coordenador', 'professor'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if (isset($_GET['excluir'])) {
    $aviso_id = $_GET['excluir'];
    $sql = "DELETE FROM Avisos WHERE aviso_id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $aviso_id, $usuario_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Aviso excluído com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "Erro ao excluir: " . $conn->error;
        $_SESSION['tipo_mensagem'] = "error";
    }
    $stmt->close();
    header("Location: cadastrar_aviso.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_aviso = $_POST['tipo_aviso'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    
    if (isset($_POST['editar_id'])) {
  
        $aviso_id = $_POST['editar_id'];
        
        $sql = "UPDATE Avisos SET tipo_aviso = ?, titulo = ?, descricao = ? 
                WHERE aviso_id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $tipo_aviso, $titulo, $descricao, $aviso_id, $usuario_id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Aviso atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    } else {
        $data_publicacao = date('Y-m-d');
        
        $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_publicacao);

        if ($stmt->execute()) {
            $aviso_id = $conn->insert_id;
            $sql_proc = "CALL InserirNotificacaoAviso(?, ?, ?, ?)";
            $stmt_proc = $conn->prepare($sql_proc);
            $stmt_proc->bind_param("iiss", $aviso_id, $usuario_id, $titulo, $data_publicacao);
            $stmt_proc->execute();
            $stmt_proc->close();

            $_SESSION['mensagem'] = "Aviso cadastrado com sucesso! Notificações enviadas aos estudantes.";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    }
    
    header("Location: cadastrar_aviso.php");
    exit();
}

$editar_id = null;
$editar_dados = null;
if (isset($_GET['editar'])) {
    $editar_id = $_GET['editar'];
    $sql = "SELECT * FROM Avisos WHERE aviso_id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $editar_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $editar_dados = $result->fetch_assoc();
    } else {
        $_SESSION['mensagem'] = "Aviso não encontrado ou você não tem permissão para editá-lo.";
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: cadastrar_aviso.php");
        exit();
    }
    $stmt->close();
}

$sql = "SELECT * FROM Avisos WHERE usuario_id = ? ORDER BY data_publicacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<link rel="stylesheet" href="CSS/cadastrar_avisos.css">
<link rel="stylesheet" href="CSS/editarCadastroPC.css">
<h2>Cadastrar Aviso</h2>

<?php
if (isset($_SESSION['mensagem'])) {
    echo "<div class='" . $_SESSION['tipo_mensagem'] . "'>" . $_SESSION['mensagem'] . "</div>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>

<h3><?php echo isset($editar_id) ? "Editar Aviso" : "Cadastrar Novo Aviso"; ?></h3>
<form method="POST" action="cadastrar_aviso.php">
    <?php if (isset($editar_id)): ?>
        <input type="hidden" name="editar_id" value="<?php echo $editar_id; ?>">
    <?php endif; ?>
    
    <label for="tipo_aviso">Tipo de Aviso:</label>
    <select name="tipo_aviso" id="tipo_aviso" required>
        <option value="aviso" <?php echo (isset($editar_dados) && $editar_dados['tipo_aviso'] == 'aviso') ? 'selected' : ''; ?>>Aviso Geral</option>
        <option value="oportunidade" <?php echo (isset($editar_dados) && $editar_dados['tipo_aviso'] == 'oportunidade') ? 'selected' : ''; ?>>Oportunidade de Emprego</option>
    </select>

    <label for="titulo">Título:</label>
    <input type="text" name="titulo" id="titulo" 
           value="<?php echo isset($editar_dados) ? htmlspecialchars($editar_dados['titulo']) : ''; ?>" required>

    <label for="descricao">Descrição:</label>
    <textarea name="descricao" id="descricao" required><?php echo isset($editar_dados) ? htmlspecialchars($editar_dados['descricao']) : ''; ?></textarea>

    <div class="button-group">
        <button type="submit"><?php echo isset($editar_id) ? "Atualizar" : "Cadastrar Aviso"; ?></button>
        <?php if (isset($editar_id)): ?>
            <a href="cadastrar_aviso.php" class="cancel-button">Cancelar</a>
        <?php endif; ?>
    </div>
</form>

<h3>Seus Avisos Cadastrados</h3>
<?php
if ($result->num_rows > 0) {
    echo "<div class='avisos-container'>";
    echo "<table>";
    echo "<thead><tr><th>Tipo</th><th>Título</th><th>Descrição</th><th>Data</th><th>Ações</th></tr></thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . ($row['tipo_aviso'] == 'aviso' ? 'Aviso Geral' : 'Oportunidade') . "</td>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['descricao'], 0, 100)) . (strlen($row['descricao']) > 100 ? '...' : '') . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($row['data_publicacao'])) . "</td>";
        echo "<td class='actions'>";
        echo "<a href='cadastrar_aviso.php?editar=" . $row['aviso_id'] . "' class='edit-btn'>Editar</a> ";
        echo "<a href='cadastrar_aviso.php?excluir=" . $row['aviso_id'] . "' class='delete-btn' onclick='return confirm(\"Tem certeza que deseja excluir este aviso?\")'>Excluir</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "<p>Nenhum aviso cadastrado.</p>";
}
$stmt->close();
$conn->close();
?>


</div>
    <script src="JS/Vlibras.js"></script>

</body>
</html>