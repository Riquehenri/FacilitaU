<?php
// Inclui a conexão com o banco de dados
include("config.php");

// Inclui o cabeçalho da página (provavelmente contém a navbar, logo, etc.)
include("header.php");

// Inicializa um array para armazenar mensagens de erro
$erro = [];

// Flag que será verdadeira se a senha for alterada com sucesso
$sucesso = false;

// Verifica se o botão de envio do formulário foi clicado
if (isset($_POST['ok'])) {
    // Obtém o e-mail digitado e remove espaços em branco
    $email = trim($_POST['email']);

    // Verifica se o campo de e-mail está vazio
    if (empty($email)) {
        $erro[] = "O campo de e-mail não pode estar vazio.";
    }
    // Verifica se o formato do e-mail é válido
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = "E-mail inválido.";
    } else {
        // Prepara uma consulta SQL para verificar se o e-mail existe na tabela 'usuarios'
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $total = $stmt->num_rows;

        // Se nenhum usuário for encontrado com o e-mail fornecido
        if ($total == 0) {
            $erro[] = "O e-mail informado não existe no banco de dados.";
        } else {
            // Gera uma nova senha aleatória com 6 caracteres (a partir de um hash MD5 baseado na hora atual)
            $novasenha = substr(md5(time()), 0, 6);

            // Criptografa a nova senha com bcrypt usando password_hash
            $nscriptografada = password_hash($novasenha, PASSWORD_DEFAULT);

            // Atualiza a senha do usuário no banco de dados
            $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
            $stmt->bind_param("ss", $nscriptografada, $email);
            $stmt->execute();

            // Verifica se alguma linha foi afetada (ou seja, senha atualizada com sucesso)
            if ($stmt->affected_rows > 0) {
                $sucesso = true;

                // Redireciona o usuário para a tela de login após 3 segundos
                header("Refresh: 3; url=login_usuario.php");
            } else {
                $erro[] = "Erro ao atualizar a senha.";
            }
        }

        // Fecha a consulta preparada para liberar recursos
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FacilitaU - Recuperar Senha</title>
    <link rel="stylesheet" href="css/Recuperar_senha.css" /> <!-- Estilo da página -->
</head>
<body>

<div class="container"> <!-- Container principal da página -->
    <div class="form-container"> <!-- Área do formulário -->

        <?php if($sucesso): ?> <!-- Se a senha foi alterada com sucesso -->
            <div class="mensagem-sucesso">
                <h2>Senha alterada com sucesso!</h2>
                <!-- Aqui poderia ser exibida a nova senha ou enviada por e-mail -->
            </div>

        <?php else: ?> <!-- Se ainda não houve sucesso, exibe o formulário -->
            <h2>Recuperar senha</h2>

            <?php if (count($erro) > 0): ?> <!-- Se houver mensagens de erro -->
                <div class="mensagem-erro">
                    <?php foreach ($erro as $msg): ?> <!-- Mostra cada erro individualmente -->
                        <p><?php echo $msg; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="input-group"> <!-- Formulário de envio do e-mail -->
                <form method="POST" action="">
                    <input placeholder="Seu e-mail" name="email" type="text" required />
                    <button name="ok" value="ok" type="submit">
                        Recuperar Senha
                    </button>
                </form>
            </div>

            <div class="or">OU</div> <!-- Separador visual -->

            <!-- Botão de cancelamento que redireciona de volta para a tela de login -->
            <button class="btn-secondary" onclick="window.location.href='login_usuario.php';">
                Cancelar Recuperação
            </button>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
