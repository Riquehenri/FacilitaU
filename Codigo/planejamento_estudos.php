<?php
session_start();

$page_title = "Planejamento de Estudos";

// Inclui arquivo de configuração (conexão com banco) e cabeçalho padrão
include 'config.php';
include 'header.php';

// Verifica se o usuário está logado e se é do tipo 'estudante'
// Se não for, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// ** Exclusão de planejamento **
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];

    // Soft delete para compatibilidade com calendário
    $sql = "UPDATE Planejamento_Estudos SET ativo = FALSE WHERE planejamento_id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $usuario_id);
    
    if ($stmt->execute()) {
        // Mensagem de sucesso para exclusão
        $_SESSION['mensagem'] = "Planejamento excluído com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        // Mensagem de erro se falhar
        $_SESSION['mensagem'] = "Erro ao excluir: " . $conn->error;
        $_SESSION['tipo_mensagem'] = "error";
    }
    $stmt->close();

    // Redireciona para a mesma página para evitar reenvio do form
    header("Location: planejamento_estudos.php");
    exit();
}

// ** Inserção ou atualização do planejamento via POST **
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe dados do formulário
    $dia_semana = $_POST['dia_semana'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $atividade = $_POST['atividade'];
    
    // NOVOS CAMPOS para compatibilidade com calendário
    $data_inicial = $_POST['data_inicial'] ?? date('Y-m-d');
    $tipo_recorrencia = $_POST['tipo_recorrencia'] ?? 'semanal'; // Padrão semanal para planejamentos
    
    // Valida se o horário de fim é depois do início
    if (strtotime($horario_fim) <= strtotime($horario_inicio)) {
        $_SESSION['mensagem'] = "O horário de término deve ser após o horário de início!";
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: planejamento_estudos.php");
        exit();
    }

    if (isset($_POST['editar_id'])) {
        // Atualiza um planejamento existente
        $id = $_POST['editar_id'];
        $sql = "UPDATE Planejamento_Estudos SET dia_semana = ?, horario_inicio = ?, horario_fim = ?, atividade = ?, data_inicial = ?, tipo_recorrencia = ? 
                WHERE planejamento_id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssii", $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia, $id, $usuario_id);

        if ($stmt->execute()) {
            $mensagem_recorrencia = match($tipo_recorrencia) {
                'nao' => '',
                'diario' => ' (repetição diária)',
                'semanal' => ' (repetição semanal)',
                'mensal' => ' (repetição mensal)',
                'anual' => ' (repetição anual)',
                default => ''
            };
            $_SESSION['mensagem'] = "Planejamento atualizado com sucesso!" . $mensagem_recorrencia;
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    } else {
        // Insere novo planejamento com campos necessários para o calendário
        $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia);

        if ($stmt->execute()) {
            $mensagem_recorrencia = match($tipo_recorrencia) {
                'nao' => '',
                'diario' => ' (repetição diária)',
                'semanal' => ' (repetição semanal)',
                'mensal' => ' (repetição mensal)',
                'anual' => ' (repetição anual)',
                default => ''
            };
            $_SESSION['mensagem'] = "Planejamento cadastrado com sucesso!" . $mensagem_recorrencia;
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    }
    // Redireciona para evitar reenvio do formulário
    header("Location: planejamento_estudos.php");
    exit();
}

// ** Busca os dados para edição, caso a requisição contenha 'editar' **
$editar_id = null;
$editar_dados = null;
if (isset($_GET['editar'])) {
    $editar_id = $_GET['editar'];

    // Seleciona o planejamento para o usuário logado
    $sql = "SELECT * FROM Planejamento_Estudos WHERE planejamento_id = ? AND usuario_id = ? AND ativo = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $editar_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editar_dados = $result->fetch_assoc();
    $stmt->close();
}

// ** Busca todos os planejamentos do usuário para listar na tabela **
// Ordena por dia da semana (usando FIELD para ordem personalizada) e horário de início
$sql = "SELECT * FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE ORDER BY 
        FIELD(dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'), 
        horario_inicio";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Exibe mensagem (sucesso ou erro) caso exista na sessão
if (isset($_SESSION['mensagem'])) {
    echo "<p class='" . $_SESSION['tipo_mensagem'] . "'>" . $_SESSION['mensagem'] . "</p>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>

<!-- Links para arquivos CSS externos -->
<link rel="stylesheet" href="CSS/planejar_estudos.css">
<link rel="stylesheet" href="CSS/editarPlanejamento.css">

<script>
// Validação no front-end para evitar horário fim menor ou igual ao início
function validarHorarios() {
    const inicio = document.getElementById('horario_inicio').value;
    const fim = document.getElementById('horario_fim').value;
    
    if (fim && inicio && fim <= inicio) {
        alert('O horário de término deve ser após o horário de início!');
        return false; // Cancela o envio do formulário
    }
    return true;
}

// Atualiza o atributo min do horário fim para não permitir horários anteriores ao início
document.addEventListener('DOMContentLoaded', () => {
    const inicio = document.getElementById('horario_inicio');
    const fim = document.getElementById('horario_fim');
    const selectRecorrencia = document.getElementById('tipo_recorrencia');
    const infoTexto = document.getElementById('info-texto-recorrencia');
    
    if(inicio && fim){
        inicio.addEventListener('change', function() {
            fim.min = this.value;
        });
    }
    
    // Textos informativos para recorrência
    const textosRecorrencia = {
        'nao': 'O planejamento será criado apenas para o dia selecionado.',
        'diario': 'O planejamento aparecerá todos os dias a partir da data selecionada.',
        'semanal': 'O planejamento aparecerá toda semana no mesmo dia da semana.',
        'mensal': 'O planejamento aparecerá todo mês no mesmo dia do mês.',
        'anual': 'O planejamento aparecerá todo ano na mesma data.'
    };
    
    if (selectRecorrencia && infoTexto) {
        selectRecorrencia.addEventListener('change', function() {
            infoTexto.textContent = textosRecorrencia[this.value];
        });
        
        // Definir texto inicial
        infoTexto.textContent = textosRecorrencia[selectRecorrencia.value];
    }
});
</script>

<h2>Planejamento de Estudos</h2>

<!-- Título muda dependendo se está editando ou cadastrando -->
<h3><?php echo isset($editar_id) ? "Editar Planejamento" : "Cadastrar Novo Planejamento"; ?></h3>

<!-- Formulário para cadastro/edição -->
<form method="POST" action="planejamento_estudos.php" onsubmit="return validarHorarios()">
    <?php if (isset($editar_id)): ?>
        <!-- Campo oculto para identificar qual planejamento será editado -->
        <input type="hidden" name="editar_id" value="<?php echo $editar_id; ?>">
    <?php endif; ?>
    
    <label for="dia_semana">Dia da Semana:</label>
    <select name="dia_semana" id="dia_semana" required>
        <option value="">Selecione</option>
        <?php
        // Array para exibir dias da semana com nomes amigáveis
        $dias_semana = [
            'segunda' => 'Segunda-feira',
            'terca' => 'Terça-feira',
            'quarta' => 'Quarta-feira',
            'quinta' => 'Quinta-feira',
            'sexta' => 'Sexta-feira',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo'
        ];
        
        // Gera as opções do select, marcando como selecionado o dia já salvo (se estiver editando)
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
           value="<?php echo isset($editar_dados) ? $editar_dados['horario_fim'] : ''; ?>" required
           min="<?php echo isset($editar_dados) ? $editar_dados['horario_inicio'] : ''; ?>">

    <label for="atividade">Atividade:</label>
    <input type="text" name="atividade" id="atividade" 
           value="<?php echo isset($editar_dados) ? htmlspecialchars($editar_dados['atividade']) : ''; ?>" required>

    <!-- NOVOS CAMPOS PARA COMPATIBILIDADE COM CALENDÁRIO -->
    <label for="data_inicial">Data de Início:</label>
    <input type="date" name="data_inicial" id="data_inicial" 
           value="<?php echo isset($editar_dados) ? ($editar_dados['data_inicial'] ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>

    <label for="tipo_recorrencia">Repetir Planejamento:</label>
    <select name="tipo_recorrencia" id="tipo_recorrencia">
        <option value="nao" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'nao') ? 'selected' : ''; ?>>Não repetir (apenas neste dia)</option>
        <option value="diario" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'diario') ? 'selected' : ''; ?>>Repetir diariamente</option>
        <option value="semanal" <?php echo (!isset($editar_dados) || $editar_dados['tipo_recorrencia'] == 'semanal') ? 'selected' : ''; ?>>Repetir semanalmente</option>
        <option value="mensal" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'mensal') ? 'selected' : ''; ?>>Repetir mensalmente</option>
        <option value="anual" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'anual') ? 'selected' : ''; ?>>Repetir anualmente</option>
    </select>

    <div class="info-recorrencia" style="margin: 10px 0; padding: 10px; background: #f0f8ff; border-left: 4px solid #007bff; font-size: 14px;">
        <strong>ℹ️ Informação:</strong>
        <span id="info-texto-recorrencia">O planejamento aparecerá toda semana no mesmo dia da semana.</span>
    </div>

    <button type="submit"><?php echo isset($editar_id) ? "Atualizar" : "Cadastrar"; ?></button>
    
    <?php if (isset($editar_id)): ?>
        <!-- Link para cancelar a edição -->
        <a href="planejamento_estudos.php" class="cancel-button">Cancelar</a>
    <?php endif; ?>
</form>

<h3>Seu Planejamento</h3>

<?php
// Exibe a tabela com os planejamentos cadastrados
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Dia da Semana</th><th>Horário Início</th><th>Horário Fim</th><th>Atividade</th><th>Data Início</th><th>Repetição</th><th>Ações</th></tr>";
    
    // Loop pelos planejamentos e exibição dos dados na tabela
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $dias_semana[$row['dia_semana']] . "</td>";
        echo "<td>" . substr($row['horario_inicio'], 0, 5) . "</td>"; // Exibe HH:MM
        echo "<td>" . substr($row['horario_fim'], 0, 5) . "</td>";
        echo "<td>" . htmlspecialchars($row['atividade']) . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($row['data_inicial'] ?? date('Y-m-d'))) . "</td>";
        
        // Mostrar tipo de recorrência
        $recorrencia_texto = match($row['tipo_recorrencia'] ?? 'semanal') {
            'nao' => 'Não repete',
            'diario' => 'Diário',
            'semanal' => 'Semanal',
            'mensal' => 'Mensal',
            'anual' => 'Anual',
            default => 'Semanal'
        };
        echo "<td>" . $recorrencia_texto . "</td>";
        
        echo "<td class='actions'>";
        // Links para editar e excluir, com confirmação para exclusão
        echo "<a href='planejamento_estudos.php?editar=" . $row['planejamento_id'] . "' class='edit-btn'>Editar</a> ";
        echo "<a href='planejamento_estudos.php?excluir=" . $row['planejamento_id'] . "' class='delete-btn' onclick='return confirm(\"Tem certeza que deseja excluir este planejamento?\")'>Excluir</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    // Caso não existam planejamentos cadastrados
    echo "<p>Nenhum planejamento cadastrado.</p>";
}

// Fecha a declaração e a conexão com o banco
$stmt->close();
$conn->close();
?>

<!-- Inclusão de script de acessibilidade (VLibras) -->
<script src="JS/Vlibras.js"></script>

</body>
</html>
