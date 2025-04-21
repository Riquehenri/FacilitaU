<?php
// Inicia a sessão - necessário para acessar informações do usuário logado
session_start();

// Define o título da página que aparecerá na aba do navegador
$page_title = "Listar Avisos";

// Inclui arquivos importantes:
include 'config.php';  // Contém as configurações de conexão com o banco de dados
include 'header.php';  // Contém o cabeçalho HTML que aparece em todas as páginas

// Verifica se o usuário está logado e se é do tipo 'estudante'
// Se NÃO estiver logado OU não for estudante, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    header("Location: index.php");
    exit();  // Termina a execução do script
}

// Cria a consulta SQL para buscar os avisos
// A consulta ordena os resultados pela data de publicação, do mais recente para o mais antigo
$sql = "SELECT * FROM AvisosComAutor ORDER BY data_publicacao DESC";

// Executa a consulta no banco de dados e armazena o resultado
$result = $conn->query($sql);
?>

<!-- Link para o arquivo CSS que estiliza esta página -->
<link rel="stylesheet" href="CSS/listar_avisos.css">

<!-- Título principal da página -->
<h2>Listar Avisos</h2>

<?php
// Verifica se encontrou algum aviso (se o número de linhas no resultado é maior que 0)
if ($result->num_rows > 0) {
    // Se tem avisos, começa a criar a tabela HTML
    
    // Cria a abertura da tabela
    echo "<table>";
    
    // Cria o cabeçalho da tabela com os títulos das colunas
    echo "<tr><th>Tipo</th><th>Título</th><th>Descrição</th><th>Autor</th><th>Data de Publicação</th></tr>";
    
    // Loop que pega cada linha do resultado e transforma em uma linha da tabela
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";  // Inicia uma nova linha na tabela
        
        // Coluna Tipo: ucfirst() deixa a primeira letra maiúscula
        echo "<td>" . ucfirst($row['tipo_aviso']) . "</td>";
        
        // Coluna Título: mostra o título do aviso
        echo "<td>" . $row['titulo'] . "</td>";
        
        // Coluna Descrição: mostra o conteúdo do aviso
        echo "<td>" . $row['descricao'] . "</td>";
        
        // Coluna Autor: mostra o nome e o tipo (professor/coordenador)
        echo "<td>" . $row['autor'] . " (" . ucfirst($row['tipo_autor']) . ")</td>";
        
        // Coluna Data: mostra quando o aviso foi publicado
        echo "<td>" . $row['data_publicacao'] . "</td>";
        
        echo "</tr>";  // Fecha a linha da tabela
    }
    
    echo "</table>";  // Fecha a tabela HTML
} else {
    // Se não encontrou nenhum aviso, mostra uma mensagem amigável
    echo "<p>Nenhum aviso publicado.</p>";
}

// Fecha a conexão com o banco de dados para liberar recursos
$conn->close();
?>

<!-- Fecha as tags que foram abertas no header.php -->
</div>
</body>
</html>