<?php
// Inclui o arquivo de configuração (conexão com banco, etc)
include("config.php");
// Inclui o cabeçalho da página
include("header.php");

// Inicializa arrays para mensagens de erro e flag de sucesso
$erro = [];
$sucesso = false;

// Verifica se o formulário foi enviado (botão 'ok' clicado)
if (isset($_POST['ok'])) {
    // Remove espaços no início e fim do email recebido
    $email = trim($_POST['email']);

    // Validações básicas do campo email
    if (empty($email)) {
        $erro[] = "O campo de e-mail não pode estar vazio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = "E-mail inválido.";
    } else {
        // Prepara consulta para verificar se o email existe no banco
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Se nenhum usuário com esse email foi encontrado
        if ($stmt->num_rows == 0) {
            $erro[] = "O e-mail informado não existe no banco de dados.";
        } else {
            // Gera uma nova senha temporária de 6 caracteres (hash MD5 do timestamp)
            $novasenha = substr(md5(time()), 0, 6);
            // Cria o hash seguro para armazenar no banco
            $nscriptografada = password_hash($novasenha, PASSWORD_DEFAULT);

            // Atualiza a senha do usuário com o novo hash
            $stmt_update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $nscriptografada, $email);
            $stmt_update->execute();

            // Verifica se a atualização foi realizada
            if ($stmt_update->affected_rows > 0) {
                $sucesso = true;
                // Aqui você pode adicionar o código para enviar a nova senha para o email do usuário

                // Redireciona para a página de login após 3 segundos
                header("Refresh: 3; url=login_usuario.php");
            } else {
                $erro[] = "Erro ao atualizar a senha.";
            }
            $stmt_update->close();
        }
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
    <link rel="stylesheet" href="CSS/Recuperar_senha.css" />
</head>
<body>
    <div class="container">
        <div class="form-container">

            <!-- Se a recuperação foi feita com sucesso -->
            <?php if ($sucesso): ?>
                <div class="mensagem-sucesso">
                    <h2>Senha alterada com sucesso!</h2>
                    <p>Você será redirecionado para o login em instantes.</p>
                </div>

            <!-- Caso contrário, exibe o formulário e possíveis erros -->
            <?php else: ?>
                <h2>Recuperar senha</h2>

                <!-- Mostra erros, caso existam -->
                <?php if (count($erro) > 0): ?>
                    <div class="mensagem-erro">
                        <?php foreach ($erro as $msg): ?>
                            <p><?php echo htmlspecialchars($msg); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Formulário para o usuário informar o email -->
                <form method="POST" action="">
                    <input placeholder="Seu e-mail" name="email" type="email" required />
                    <button name="ok" value="ok" type="submit">Recuperar Senha</button>
                </form>

                <div class="or">OU</div>
                <!-- Botão para cancelar a recuperação e voltar ao login -->
                <button class="btn-secondary" onclick="window.location.href='login_usuario.php';">
                    Cancelar Recuperação
                </button>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>
