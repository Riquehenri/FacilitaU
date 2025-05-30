<?php
// Inicia a sessão para acesso às variáveis de sessão
session_start();

// Define o título da página
$page_title = "Listar Avisos";

// Inclui o arquivo de configuração (conexão com banco, constantes, etc)
include 'config.php';

// Inclui o cabeçalho padrão (header.php)
include 'header.php';

// Verifica se o usuário está logado e é do tipo 'estudante'
// Caso contrário, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();
}

// Obtém o termo de busca enviado via GET (se existir)
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';

// Inicializa a cláusula WHERE da consulta SQL
$where = '';

// Se o termo de busca não for vazio, prepara a cláusula WHERE com filtro por título ou descrição
if (!empty($busca)) {
    // Escapa caracteres especiais para evitar SQL Injection
    $busca_escapada = $conn->real_escape_string($busca);
    $where = " WHERE titulo LIKE '%$busca_escapada%' OR descricao LIKE '%$busca_escapada%'";
}

// Monta a consulta para buscar os avisos, ordenados pela data de publicação (mais recentes primeiro)
$sql = "SELECT * FROM AvisosComAutor $where ORDER BY data_publicacao DESC";

// Executa a consulta no banco de dados
$result = $conn->query($sql);
?>

<!-- Inclui arquivos CSS específicos para a página -->
<link rel="stylesheet" href="CSS/Busca_lista.css">
<link rel="stylesheet" href="CSS/listar_avisos.css">

<!-- Fonte de ícones FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Container do cabeçalho da listagem -->
<div class="header-container">
    <h2>Listar Avisos</h2>
    
    <!-- Formulário de busca -->
    <div class="search-container">
        <form action="" method="get">
            <!-- Campo de texto para pesquisa, mantém o valor digitado após envio -->
            <input type="text" name="busca" placeholder="Pesquisar avisos..." value="<?php echo htmlspecialchars($busca); ?>">
            
            <!-- Botão para enviar o formulário -->
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

<?php
// Verifica se a consulta retornou algum resultado
if ($result->num_rows > 0) {
    // Inicia a tabela HTML para exibir os avisos
    echo "<table>";
    echo "<tr><th>Tipo</th><th>Título</th><th>Descrição</th><th>Autor</th><th>Data de Publicação</th></tr>";
    
    // Percorre cada linha do resultado e exibe os dados na tabela
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        // Exibe o tipo de aviso com a primeira letra maiúscula
        echo "<td>" . ucfirst($row['tipo_aviso']) . "</td>";
        
        // Exibe o título do aviso, escapando caracteres especiais para segurança
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        
        // Exibe a descrição do aviso, escapando caracteres especiais para segurança
        echo "<td>" . htmlspecialchars($row['descricao']) . "</td>";
        
        // Exibe o nome do autor e seu tipo (ex: estudante, professor), com primeira letra maiúscula
        echo "<td>" . htmlspecialchars($row['autor']) . " (" . ucfirst($row['tipo_autor']) . ")</td>";
        
        // Exibe a data da publicação do aviso
        echo "<td>" . $row['data_publicacao'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    // Caso não existam avisos, mostra mensagem apropriada
    echo "<p>Nenhum aviso " . (!empty($busca) ? "encontrado para '".htmlspecialchars($busca)."'" : "publicado") . ".</p>";
}

// Fecha a conexão com o banco de dados
$conn->close();
?>

</div>

<!-- Script para acessibilidade com VLibras -->
<script src="JS/Vlibras.js"></script>
</body>
</html>
