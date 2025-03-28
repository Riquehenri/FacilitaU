<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - Comunicados por Faculdade</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Comunicados por Faculdade</h1>
        <a href="index.php">Voltar ao Menu</a>

        <?php
        // Incluir a conexão com o banco de dados
        include 'config.php';

        // Consultar as faculdades para o dropdown
        $sql_faculdades = "SELECT faculdade_id, nome FROM Faculdade";
        $result_faculdades = $conn->query($sql_faculdades);

        // Verificar se uma faculdade foi selecionada
        $faculdade_id = isset($_GET['faculdade_id']) ? $_GET['faculdade_id'] : '';
        ?>

        <form method="GET" action="comunicados.php">
            <label for="faculdade_id">Selecione uma Faculdade:</label>
            <select name="faculdade_id" id="faculdade_id" onchange="this.form.submit()">
                <option value="">Selecione uma faculdade</option>
                <?php
                if ($result_faculdades->num_rows > 0) {
                    while ($row = $result_faculdades->fetch_assoc()) {
                        $selected = ($faculdade_id == $row['faculdade_id']) ? 'selected' : '';
                        echo "<option value='" . $row['faculdade_id'] . "' $selected>" . $row['nome'] . "</option>";
                    }
                }
                ?>
            </select>
        </form>

        <?php
        // Se uma faculdade foi selecionada, listar os comunicados
        if (!empty($faculdade_id)) {
            $sql = "SELECT * FROM ComunicadosPorFaculdade WHERE faculdade_id = '$faculdade_id'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Título</th><th>Data</th><th>Autor</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['titulo'] . "</td>";
                    echo "<td>" . $row['data'] . "</td>";
                    echo "<td>" . $row['autor'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Nenhum comunicado encontrado para esta faculdade.</p>";
            }
        }

        // Fechar a conexão
        $conn->close();
        ?>
    </div>
</body>
</html>