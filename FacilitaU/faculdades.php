<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - Lista de Faculdades</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Lista de Faculdades</h1>
        <a href="index.php">Voltar ao Menu</a>
        <?php
        // Incluir a conexão com o banco de dados
        include 'config.php';

        // Consultar todas as faculdades
        $sql = "SELECT * FROM Faculdade";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Sigla</th><th>Nome</th><th>Cidade</th><th>Estado</th><th>Telefone</th><th>Email</th><th>Responsável</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['sigla'] . "</td>";
                echo "<td>" . $row['nome'] . "</td>";
                echo "<td>" . $row['cidade'] . "</td>";
                echo "<td>" . $row['estado'] . "</td>";
                echo "<td>" . $row['telefone'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['responsavel'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhuma faculdade encontrada.</p>";
        }

        // Fechar a conexão
        $conn->close();
        ?>
    </div>
</body>
</html>