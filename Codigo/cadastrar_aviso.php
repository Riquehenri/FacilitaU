<?php
// Inicia a sessão
session_start();

// Define o fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Define o título da página
$page_title = "Cadastrar Aviso";

// Inclui arquivos de configuração e layout
include 'config.php';
include 'header.php';

// Verifica se o usuário está logado e tem permissão (coordenador ou professor)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'], ['coordenador', 'professor'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// ========================================
// EXCLUSÃO DE AVISO
// ========================================
if (isset($_GET['excluir'])) {
    $aviso_id = $_GET['excluir'];
    
    // Prepara e executa a exclusão (soft delete para compatibilidade com calendário)
    $sql = "UPDATE Avisos SET ativo = FALSE WHERE aviso_id = ? AND usuario_id = ?";
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
    
    // Redireciona para evitar múltiplas execuções
    header("Location: cadastrar_aviso.php");
    exit();
}

// ========================================
// CADASTRO OU EDIÇÃO DE AVISO
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_aviso = $_POST['tipo_aviso'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_inicial = $_POST['data_inicial'] ?? date('Y-m-d'); // Nova: data inicial para o calendário
    $tipo_recorrencia = $_POST['tipo_recorrencia'] ?? 'nao'; // Nova: tipo de recorrência
    
    // EDITAR AVISO
    if (isset($_POST['editar_id'])) {
        $aviso_id = $_POST['editar_id'];
        
        $sql = "UPDATE Avisos SET tipo_aviso = ?, titulo = ?, descricao = ?, data_inicial = ?, tipo_recorrencia = ? 
                WHERE aviso_id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $tipo_aviso, $titulo, $descricao, $data_inicial, $tipo_recorrencia, $aviso_id, $usuario_id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Aviso atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();

    // NOVO AVISO
    } else {
        $data_publicacao = date('Y-m-d');
        
        // SQL atualizado para incluir os novos campos necessários para o calendário
        $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao, data_inicial, tipo_recorrencia, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_publicacao, $data_inicial, $tipo_recorrencia);

        if ($stmt->execute()) {
            // Após inserir o aviso, envia notificações
            $aviso_id = $conn->insert_id;
            
            // Criar notificações para todos os estudantes
            $sql_notif = "INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id)
                          SELECT u.usuario_id, 'aviso', CONCAT('Novo aviso: ', ?), ?, ?
                          FROM Usuarios u WHERE u.tipo = 'estudante'";
            $stmt_notif = $conn->prepare($sql_notif);
            if ($stmt_notif) {
                $stmt_notif->bind_param("ssi", $titulo, $data_inicial, $aviso_id);
                $stmt_notif->execute();
                $stmt_notif->close();
            }

            $mensagem_recorrencia = match($tipo_recorrencia) {
                'nao' => '',
                'semanal' => ' (repetição semanal)',
                'mensal' => ' (repetição mensal)',
                'anual' => ' (repetição anual)',
                default => ''
            };

            $_SESSION['mensagem'] = "Aviso cadastrado com sucesso! Notificações enviadas aos estudantes." . $mensagem_recorrencia;
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    }

    // Redireciona após POST
    header("Location: cadastrar_aviso.php");
    exit();
}

// ========================================
// VERIFICAÇÃO DE EDIÇÃO
// ========================================
$editar_id = null;
$editar_dados = null;
if (isset($_GET['editar'])) {
    $editar_id = $_GET['editar'];
    $sql = "SELECT * FROM Avisos WHERE aviso_id = ? AND usuario_id = ? AND ativo = TRUE";
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

// ========================================
// LISTAGEM DE AVISOS DO USUÁRIO
// ========================================
$sql = "SELECT * FROM Avisos WHERE usuario_id = ? AND ativo = TRUE ORDER BY data_publicacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- CSS específico -->
<link rel="stylesheet" href="CSS/cadastrar_avisos.css">
<link rel="stylesheet" href="CSS/editarCadastroPC.css">

<h2>Cadastrar Aviso</h2>

<!-- Exibe mensagens de sucesso ou erro -->
<?php
if (isset($_SESSION['mensagem'])) {
    echo "<div class='" . $_SESSION['tipo_mensagem'] . "'>" . $_SESSION['mensagem'] . "</div>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>

<!-- Formulário de Cadastro / Edição -->
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

    <!-- NOVOS CAMPOS PARA COMPATIBILIDADE COM CALENDÁRIO -->
    <label for="data_inicial">Data do Aviso:</label>
    <input type="date" name="data_inicial" id="data_inicial" 
           value="<?php echo isset($editar_dados) ? $editar_dados['data_inicial'] : date('Y-m-d'); ?>" required>

    <label for="tipo_recorrencia">Repetir Aviso:</label>
    <select name="tipo_recorrencia" id="tipo_recorrencia">
        <option value="nao" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'nao') ? 'selected' : ''; ?>>Não repetir (apenas neste dia)</option>
        <option value="semanal" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'semanal') ? 'selected' : ''; ?>>Repetir semanalmente</option>
        <option value="mensal" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'mensal') ? 'selected' : ''; ?>>Repetir mensalmente</option>
        <option value="anual" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'anual') ? 'selected' : ''; ?>>Repetir anualmente</option>
    </select>

    <div class="info-recorrencia" style="margin: 10px 0; padding: 10px; background: #f0f8ff; border-left: 4px solid #007bff; font-size: 14px;">
        <strong>ℹ️ Informação:</strong>
        <span id="info-texto-recorrencia">O aviso será criado apenas para o dia selecionado.</span>
    </div>

    <div class="button-group">
        <button type="submit"><?php echo isset($editar_id) ? "Atualizar" : "Cadastrar Aviso"; ?></button>
        <?php if (isset($editar_id)): ?>
            <a href="cadastrar_aviso.php" class="cancel-button">Cancelar</a>
        <?php endif; ?>
    </div>
</form>

<!-- Listagem dos avisos já cadastrados -->
<h3>Seus Avisos Cadastrados</h3>
<?php
if ($result->num_rows > 0) {
    echo "<div class='avisos-container'>";
    echo "<table>";
    echo "<thead><tr><th>Tipo</th><th>Título</th><th>Descrição</th><th>Data do Aviso</th><th>Repetição</th><th>Data Cadastro</th><th>Ações</th></tr></thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . ($row['tipo_aviso'] == 'aviso' ? 'Aviso Geral' : 'Oportunidade') . "</td>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['descricao'], 0, 100)) . (strlen($row['descricao']) > 100 ? '...' : '') . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($row['data_inicial'] ?? $row['data_publicacao'])) . "</td>";
        
        // Mostrar tipo de recorrência
        $recorrencia_texto = match($row['tipo_recorrencia'] ?? 'nao') {
            'nao' => 'Não repete',
            'semanal' => 'Semanal',
            'mensal' => 'Mensal',
            'anual' => 'Anual',
            default => 'Não repete'
        };
        echo "<td>" . $recorrencia_texto . "</td>";
        
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

// Fecha conexões
$stmt->close();
$conn->close();
?>

<script>
// JavaScript para atualizar texto informativo sobre recorrência
document.addEventListener('DOMContentLoaded', function() {
    const selectRecorrencia = document.getElementById('tipo_recorrencia');
    const infoTexto = document.getElementById('info-texto-recorrencia');
    
    const textosRecorrencia = {
        'nao': 'O aviso será criado apenas para o dia selecionado.',
        'semanal': 'O aviso aparecerá toda semana no mesmo dia da semana.',
        'mensal': 'O aviso aparecerá todo mês no mesmo dia do mês.',
        'anual': 'O aviso aparecerá todo ano na mesma data.'
    };
    
    selectRecorrencia.addEventListener('change', function() {
        infoTexto.textContent = textosRecorrencia[this.value];
    });
    
    // Definir texto inicial
    infoTexto.textContent = textosRecorrencia[selectRecorrencia.value];
});
</script>

</div>

<!-- Script do VLibras para acessibilidade -->
<script src="JS/Vlibras.js"></script>

</body>
</html>
