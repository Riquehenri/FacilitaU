<?php
include("config.php"); // Inclui o arquivo de configuração com conexão ao banco de dados

$erro = []; // Array para armazenar mensagens de erro
$sucesso = false; // Flag para saber se a operação foi bem-sucedida

// Verifica se o formulário foi enviado
if (isset($_POST['ok'])) {
    $email = trim($_POST['email']); // Remove espaços em branco do início/fim do e-mail

    // Valida se o campo de e-mail está vazio
    if (empty($email)) {
        $erro[] = "O campo de e-mail não pode estar vazio.";
    } 
    // Valida se o e-mail tem um formato válido
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = "E-mail inválido.";
    } else {
        // Prepara consulta para verificar se o e-mail existe no banco
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $total = $stmt->num_rows; // Conta o número de resultados

        // Verifica se o e-mail existe
        if ($total == 0) {
            $erro[] = "O e-mail informado não existe no banco de dados.";
        } else {
            // Gera nova senha com os 6 primeiros caracteres de um hash MD5 baseado no tempo atual
            $novasenha = substr(md5(time()), 0, 6);
            // Criptografa a nova senha com password_hash
            $nscriptografada = password_hash($novasenha, PASSWORD_DEFAULT);

            // Condição verdadeira (aparentemente para futura lógica condicional)
            if(1==1) {
                // Atualiza a senha do usuário no banco
                $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
                $stmt->bind_param("ss", $nscriptografada, $email);
                $stmt->execute();

                // Verifica se alguma linha foi afetada (ou seja, se a senha foi atualizada)
                if ($stmt->affected_rows > 0) {
                    $sucesso = true; // Marca operação como bem-sucedida
                    // Redireciona para a tela de login após 3 segundos
                    header("Refresh: 3; url=login_usuario.php");
                } else {
                    $erro[] = "Erro ao atualizar a senha.";
                }
            }
        }
        $stmt->close(); // Fecha a instrução preparada
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FacilitaU - Recuperar Senha</title>
    <link rel="stylesheet" href="CSS/Recuperar_senha.css" /> <!-- Importa o CSS da página -->
</head>
<body>
    
    <div class="container">
        <div class="form-container">
            <!-- Se a operação foi bem-sucedida, exibe mensagem de sucesso -->
            <?php if($sucesso): ?>
                <div class="mensagem-sucesso">
                    <h2>Senha alterada com sucesso!</h2>
                </div>
            <?php else: ?>
                <h2>Recuperar senha</h2>
                
                <!-- Exibe mensagens de erro, se houver -->
                <?php if (count($erro) > 0): ?>
                    <div class="mensagem-erro">
                        <?php foreach ($erro as $msg): ?>
                            <p><?php echo $msg; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário de recuperação de senha -->
                <div class="input-group">
                    <form method="POST" action="">
                        <input placeholder="Seu e-mail" name="email" type="text" required />
                        <button name="ok" value="ok" type="submit">
                            Recuperar Senha
                        </button>
                    </form>
                </div>

                <!-- Botão para cancelar e voltar ao login -->
                <div class="or">OU</div>
                <button class="btn-secondary" onclick="window.location.href='login_usuario.php';">
                    Cancelar Recuperação
                </button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
