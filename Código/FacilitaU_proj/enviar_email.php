<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';
require_once 'config.php';

function enviarEmail($conn, $usuario_id, $assunto, $mensagem) {
    $sql = "SELECT email FROM Usuarios WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario || !$usuario['email']) {
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        // Configurações do servidor SMTP (exemplo com Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seuemail@gmail.com'; // Substitua pelo seu e-mail
        $mail->Password = 'sua_senha_ou_app_password'; // Substitua pela sua senha ou App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remetente e destinatário
        $mail->setFrom('seuemail@gmail.com', 'FacilitaU');
        $mail->addAddress($usuario['email']);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;
        $mail->AltBody = strip_tags($mensagem);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>