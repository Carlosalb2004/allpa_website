<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';

header('Content-Type: application/json');

// Clave secreta del reCAPTCHA
$recaptcha_secret = '6Lfg5mcrAAAAAF3dDczIttN-NZxsuxj15MbazUBd';
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

if (!$recaptcha_response) {
    echo json_encode(['response' => 'error', 'message' => 'Por favor completa el reCAPTCHA.']);
    exit;
}

// Verifica con la API de Google
$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
$captcha_success = json_decode($verify);

if (!$captcha_success->success) {
    echo json_encode(['response' => 'error', 'message' => 'Error en la verificación de reCAPTCHA.']);
    exit;
}

// Recoge los datos del formulario
$data = [
    'service'     => $_POST['service']     ?? '',
    'phone'       => $_POST['phone']       ?? '',
    'name'        => $_POST['name']        ?? '',
    'adress'      => $_POST['adress']      ?? '',
    'cellphone'   => $_POST['cellphone']   ?? '',
    'email'       => $_POST['email']       ?? '',
    'radios'      => $_POST['radios']      ?? '',
    'otros'       => $_POST['otros']       ?? '',
    'description' => $_POST['description'] ?? '',
    'namerec'     => $_POST['namerec']     ?? '',
    'arearec'     => $_POST['arearec']     ?? '',
];

// Arma el mensaje del correo
$mensaje = "Nuevo Reclamo Recibido:\n\n";
foreach ($data as $key => $value) {
    $mensaje .= ucfirst($key) . ": " . strip_tags($value) . "\n";
}

$destinatario = 'pruebas3@allpasac.com';
$asunto = "Reclamo de " . $data['name'];

$mail = new PHPMailer(true);

try {
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($data['email'], $data['name']);
    $mail->addAddress($destinatario);
    $mail->addReplyTo($data['email']);

    $mail->Subject = $asunto;
    $mail->Body = nl2br($mensaje);
    $mail->isHTML(true);

    $mail->send();
    echo json_encode(['response' => 'success', 'message' => 'Reclamo enviado con éxito.']);

} catch (Exception $e) {
    echo json_encode(['response' => 'error', 'message' => 'Error al enviar: ' . $mail->ErrorInfo]);
}
?>
