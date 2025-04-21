<?php
session_start(); // Inicia a sessão para acessar variáveis de sessão do usuário.

$page_title = "Planejamento de Estudos"; // Define o título da página, usado no <title> e possivelmente no cabeçalho.
include 'config.php'; // Inclui o arquivo de configuração com a conexão ao banco de dados.
include 'header.php'; // Inclui o cabeçalho da página, como menu, estilos globais, etc.

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    // Verifica se o usuário está logado e se é do tipo "estudante".
    // Se não estiver logado ou não for estudante, redireciona para a página inicial.
    header("Location: index.php");
    exit(); // Encerra o script após o redirecionamento.
}

$usuario_id = $_SESSION['usuario_id']; // Armazena o ID do usuário logado para utilizar nas queries.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se o formulário foi enviado via POST (submissão do planejamento).
    $dia_semana = $_POST['dia_semana']; // Pega o valor selecionado no campo "dia da semana".
    $horario_inicio = $_POST['horario_inicio']; // Pega o horário de início informado.
    $horario_fim = $_POST['horario_fim']; // Pega o horário de fim informado.
    $atividade = $_POST['atividade']; // Pega o texto digitado no campo "atividade".

    // Prepara a query SQL para inserir os dados no banco de forma segura.
    $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql); // Prepara a instrução SQL.
    $stmt->bind_param("issss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade); // Associa os parâmetros.

    if ($stmt->execute()) {
        // Se a execução for bem-sucedida, exibe mensagem de sucesso.
        echo "<p class='success'>Planejamento cadastrado com sucesso!</p>";
    } else {
        // Se houver erro ao inserir, mostra mensagem de erro.
        echo "<p class='error'>Erro ao cadastrar: " . $conn->error . "</p>";
    }
    $stmt->close(); // Fecha a instrução SQL.
}

// Após cadastro ou ao carregar a página, busca os planejamentos do usuário logado.
$sql = "SELECT * FROM PlanejamentoPorEstudante WHERE usuario_id = ?";
$stmt = $conn->prepare($sql); // Prepara a query SQL.
$stmt->bind_param("i", $usuario_id); // Passa o ID do usuário como parâmetro.
$stmt->execute(); // Executa a consulta.
$result = $stmt->get_result(); // Armazena o resultado da consulta.
?>
<link rel="stylesheet" href="CSS/planejar_estudos.css"> <!-- Importa o CSS para estilizar a página. -->

<h2>Planejamento de Estudos</h2>

<h3>Cadastrar Novo Planejamento</h3>
<form method="POST" action="planejamento_estudos.php">
    <!-- Formulário para cadastrar novo planejamento -->
    
    <label for="dia_semana">Dia da Semana:</label>
    <select name="dia_semana" id="dia_semana" required>
        <!-- Lista de opções para escolher o dia da semana -->
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
    <input type="time" name="horario_inicio" id="horario_inicio" required> <!-- Campo para horário inicial -->

    <label for="horario_fim">Horário de Fim:</label>
    <input type="time" name="horario_fim" id="horario_fim" required> <!-- Campo para horário final -->

    <label for="atividade">Atividade:</label>
    <input type="text" name="atividade" id="atividade" required> <!-- Campo para descrição da atividade -->

    <button type="submit">Cadastrar</button> <!-- Botão que envia o formulário -->
</form>

<h3>Seu Planejamento</h3>
<?php
// Verifica se existem resultados retornados da consulta.
if ($result->num_rows > 0) {
    // Se houver planejamentos cadastrados, monta uma tabela para exibir.
    echo "<table>";
    echo "<tr><th>Dia da Semana</th><th>Horário Início</th><th>Horário Fim</th><th>Atividade</th></tr>";
    while ($row = $result->fetch_assoc()) {
        // Para cada registro, cria uma linha na tabela com os dados.
        echo "<tr>";
        echo "<td>" . ucfirst($row['dia_semana']) . "</td>"; // Exibe o dia com a primeira letra maiúscula.
        echo "<td>" . $row['horario_inicio'] . "</td>"; // Exibe o horário de início.
        echo "<td>" . $row['horario_fim'] . "</td>"; // Exibe o horário de fim.
        echo "<td>" . $row['atividade'] . "</td>"; // Exibe a descrição da atividade.
        echo "</tr>";
    }
    echo "</table>"; // Fecha a tabela.
} else {
    // Caso não tenha nenhum planejamento salvo, mostra essa mensagem.
    echo "<p>Nenhum planejamento cadastrado.</p>";
}
$stmt->close(); // Fecha o statement da consulta de SELECT.
$conn->close(); // Fecha a conexão com o banco de dados.
?>

</div> <!-- Fecha um container aberto provavelmente no header.php -->

</body>
</html>
