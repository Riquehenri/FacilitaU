<?php
include("config.php");

$erro = [];
$sucesso = false;

if (isset($_POST['ok'])) {
    $email = trim($_POST['email']);

    // Validação do e-mail
    if (empty($email)) {
        $erro[] = "O campo de e-mail não pode estar vazio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = "E-mail inválido.";
    } else {
        // Consulta ao banco de dados
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $total = $stmt->num_rows;

        if ($total == 0) {
            $erro[] = "O e-mail informado não existe no banco de dados.";
        } else {
            // Geração de nova senha
            $novasenha = substr(md5(time()), 0, 6);
            $nscriptografada = password_hash($novasenha, PASSWORD_DEFAULT);

            // Simulação de envio de e-mail (substitua por mail() real em produção)
            if(1==1) {
                // Atualização da senha no banco de dados
                $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
                $stmt->bind_param("ss", $nscriptografada, $email);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $sucesso = true;
                    // Redireciona para login após 3 segundos
                    header("Refresh: 3; url=login.php");
                } else {
                    $erro[] = "Erro ao atualizar a senha.";
                }
            }
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
    <link rel="stylesheet" href="css/Recuperar_senha.css" />
</head>
<body>
    <div class="container">
        <div class="form-container">
            <?php if($sucesso): ?>
                <div class="mensagem-sucesso">
                    <h2>Senha alterada com sucesso!</h2>
                </div>
            <?php else: ?>
                <h2>Recuperar senha</h2>
                
                <?php if (count($erro) > 0): ?>
                    <div class="mensagem-erro">
                        <?php foreach ($erro as $msg): ?>
                            <p><?php echo $msg; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="input-group">
                    <form method="POST" action="">
                        <input placeholder="Seu e-mail" name="email" type="text" required />
                        <button name="ok" value="ok" type="submit">
                            Recuperar Senha
                        </button>
                    </form>
                </div>
                <div class="or">OU</div>
                <button class="btn-secondary" onclick="window.location.href='login.php';">
                    Cancelar Recuperação
                </button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>