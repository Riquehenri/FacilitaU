<?php
session_start();
include 'config.php';
include 'idiomas.php';

// Carregar o PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$msg = '';

// Função pra gerar código 2FA
function gerarCodigo2FA() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Função pra enviar e-mail usando PHPMailer
function enviarEmail2FA($email, $codigo) {
    $mail = new PHPMailer(true);
    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fernandolopesduarte@gmail.com'; // E-mail real pra autenticação SMTP
        $mail->Password = 'fpfo iikl fabp wire'; // Senha de app do Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configurações do e-mail
        $mail->setFrom('no-reply@facilitau.com', 'FacilitaU');
        $mail->addAddress($email);
        $mail->Subject = 'Seu Código 2FA - FacilitaU';
        $mail->Body = "Seu código 2FA é: $codigo\nEste código expira em 5 minutos.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Obter e-mail do usuário
$sql = "SELECT email FROM Usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$email = $usuario['email'];

// Obter tema do usuário
$sql = "SELECT tema FROM Usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$tema = $stmt->get_result()->fetch_assoc()['tema'] ?? 'claro';

// Verificar se já existe um código 2FA
$sql = "SELECT codigo_2fa, codigo_2fa_expiracao FROM Usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$codigo_2fa = $result['codigo_2fa'];
$expiracao = $result['codigo_2fa_expiracao'];

// Gerar novo código se necessário
$agora = new DateTime();
if (!$codigo_2fa || !$expiracao || new DateTime($expiracao) < $agora) {
    $codigo_2fa = gerarCodigo2FA();
    $expiracao = $agora->add(new DateInterval('PT5M'))->format('Y-m-d H:i:s');
    
    $sql = "UPDATE Usuarios SET codigo_2fa = ?, codigo_2fa_expiracao = ? WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $codigo_2fa, $expiracao, $usuario_id);
    $stmt->execute();

    if (enviarEmail2FA($email, $codigo_2fa)) {
        $msg = traduzir('codigo_enviado');
    } else {
        $msg = traduzir('erro_enviar_2fa') . " (Código para teste: $codigo_2fa)";
    }
}

// Validar código 2FA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_inserido = $_POST['codigo_2fa'];
    $expiracao_dt = new DateTime($expiracao);

    if ($expiracao_dt < $agora) {
        $msg = traduzir('codigo_expirado');
        $codigo_2fa = gerarCodigo2FA();
        $expiracao = $agora->add(new DateInterval('PT5M'))->format('Y-m-d H:i:s');
        
        $sql = "UPDATE Usuarios SET codigo_2fa = ?, codigo_2fa_expiracao = ? WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $codigo_2fa, $expiracao, $usuario_id);
        $stmt->execute();

        if (enviarEmail2FA($email, $codigo_2fa)) {
            $msg .= ' ' . traduzir('codigo_enviado');
        } else {
            $msg .= ' ' . traduzir('erro_enviar_2fa') . " (Código para teste: $codigo_2fa)";
        }
    } elseif ($codigo_inserido === $codigo_2fa) {
        $sql = "UPDATE Usuarios SET codigo_2fa = NULL, codigo_2fa_expiracao = NULL WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $tipo = $_SESSION['tipo'];
        if ($tipo == 'estudante') {
            header("Location: menu_estudante.php");
        } elseif ($tipo == 'professor') {
            header("Location: menu_professor.php");
        } else {
            header("Location: menu_coordenador.php");
        }
        exit();
    } else {
        $msg = traduzir('codigo_invalido');
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $idioma; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo traduzir('verificar_2fa'); ?> - FacilitaU</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $tema; ?>">
    <header>
        <h1><?php echo traduzir('verificar_2fa'); ?></h1>
        <div>
            <a href="?idioma=pt">Português</a> | 
            <a href="?idioma=en">English</a>
        </div>
    </header>
    <main>
        <?php if ($msg) echo "<p role='alert'>$msg</p>"; ?>
        <form method="POST">
            <label for="codigo_2fa"><?php echo traduzir('codigo_2fa'); ?>:</label>
            <input type="text" id="codigo_2fa" name="codigo_2fa" required aria-required="true">
            <button type="submit"><?php echo traduzir('verificar'); ?></button>
        </form>
    </main>
</body>
</html>