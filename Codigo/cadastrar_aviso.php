<?php
// Inicia a sessão para manter o estado do usuário entre páginas
session_start();

// Configura o fuso horário para o horário de São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Define o título da página
$page_title = "Cadastrar Aviso";

// Inclui arquivos de configuração e cabeçalho
include 'config.php';  // Arquivo com configurações do banco de dados
include 'header.php';  // Arquivo com o cabeçalho HTML comum

// Verifica se o usuário está logado e tem permissão (coordenador ou professor)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'], ['coordenador', 'professor'])) {
    // Redireciona para a página inicial se não tiver permissão
    header("Location: index.php");
    exit();  // Termina a execução do script
}

// Obtém o ID do usuário da sessão
$usuario_id = $_SESSION['usuario_id'];

// Verifica se o formulário foi submetido (método POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados do formulário
    $tipo_aviso = $_POST['tipo_aviso'];      // Tipo do aviso (geral/oportunidade)
    $titulo = $_POST['titulo'];              // Título do aviso
    $descricao = $_POST['descricao'];        // Descrição detalhada
    $data_publicacao = date('Y-m-d');        // Data atual no formato YYYY-MM-DD

    // Prepara a query SQL para inserir o aviso no banco de dados
    $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);  // Prepara a declaração SQL
    
    // Associa os parâmetros à query (i = inteiro, s = string)
    $stmt->bind_param("issss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_publicacao);

    // Executa a inserção do aviso
    if ($stmt->execute()) {
        // Obtém o ID do aviso recém-criado
        $aviso_id = $conn->insert_id;
        
        // Chama a stored procedure para criar notificações
        $sql_proc = "CALL InserirNotificacaoAviso(?, ?, ?, ?)";
        $stmt_proc = $conn->prepare($sql_proc);
        // Associa os parâmetros da procedure
        $stmt_proc->bind_param("iiss", $aviso_id, $usuario_id, $titulo, $data_publicacao);
        $stmt_proc->execute();  // Executa a procedure
        $stmt_proc->close();    // Fecha a declaração
        
        // Mensagem de sucesso
        echo "<p class='success'>Aviso cadastrado com sucesso! Notificações enviadas aos estudantes.</p>";
    } else {
        // Mensagem de erro (se houver falha na inserção)
        echo "<p class='error'>Erro ao cadastrar: " . $conn->error . "</p>";
    }
    
    // Fecha a declaração
    $stmt->close();
}

// Fecha a conexão com o banco de dados
$conn->close();
?>

<!-- Inclui o CSS específico da página -->
<link rel="stylesheet" href="CSS/cadastrar_avisos.css">

<!-- Formulário para cadastrar avisos -->
<h2>Cadastrar Aviso</h2>
<form method="POST" action="cadastrar_aviso.php">
    <!-- Campo para selecionar o tipo de aviso -->
    <label for="tipo_aviso">Tipo de Aviso:</label>
    <select name="tipo_aviso" id="tipo_aviso" required>
        <option value="aviso">Aviso Geral</option>
        <option value="oportunidade">Oportunidade de Emprego</option>
    </select>

    <!-- Campo para o título do aviso -->
    <label for="titulo">Título:</label>
    <input type="text" name="titulo" id="titulo" required>

    <!-- Campo para a descrição do aviso -->
    <label for="descricao">Descrição:</label>
    <textarea name="descricao" id="descricao" required></textarea>

    <!-- Botão para submeter o formulário -->
    <button type="submit">Cadastrar Aviso</button>
</form>

</div>
</body>
</html>