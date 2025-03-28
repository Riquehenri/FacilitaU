<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FacilitaU - Cadastrar Estudante</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Cadastrar Estudante</h1>
        <a href="index.php">Voltar ao Menu</a>

        <?php
        // Incluir a conexão com o banco de dados
        include 'config.php';

        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $faculdade_id = $_POST['faculdade_id'];
            $nome = $_POST['nome'];
            $email = $_POST['email'];

            // Validar os dados (simples)
            if (!empty($faculdade_id) && !empty($nome) && !empty($email)) {
                // Verificar se o email já existe
                $sql_check = "SELECT * FROM Estudante WHERE email = '$email'";
                $result_check = $conn->query($sql_check);

                if ($result_check->num_rows == 0) {
                    // Inserir o estudante
                    $sql = "INSERT INTO Estudante (faculdade_id, nome, email) VALUES ('$faculdade_id', '$nome', '$email')";
                    if ($conn->query($sql) === TRUE) {
                        echo "<p class='success'>Estudante cadastrado com sucesso!</p>";
                    } else {
                        echo "<p>Erro ao cadastrar: " . $conn->error . "</p>";
                    }
                } else {
                    echo "<p>Erro: Este email já está cadastrado.</p>";
                }
            } else {
                echo "<p>Por favor, preencha todos os campos.</p>";
            }
        }

        // Consultar as faculdades para o dropdown
        $sql_faculdades = "SELECT faculdade_id, nome FROM Faculdade";
        $result_faculdades = $conn->query($sql_faculdades);
        ?>

        <form method="POST" action="estudantes.php">
            <label for="faculdade_id">Faculdade:</label>
            <select name="faculdade_id" id="faculdade_id" required>
                <option value="">Selecione uma faculdade</option>
                <?php
                if ($result_faculdades->num_rows > 0) {
                    while ($row = $result_faculdades->fetch_assoc()) {
                        echo "<option value='" . $row['faculdade_id'] . "'>" . $row['nome'] . "</option>";
                    }
                }
                ?>
            </select>

            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <button type="submit">Cadastrar</button>
        </form>

        <?php
        // Fechar a conexão
        $conn->close();
        ?>
    </div>
</body>
</html>