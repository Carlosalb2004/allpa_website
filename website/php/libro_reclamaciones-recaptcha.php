<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';

header('Content-Type: application/json');

// RECAPTCHA
$recaptchaSecret = '6Lfg5mcrAAAAAF3dDczIttN-NZxsuxj15MbazUBd'; // ← tu clave secreta
$recaptchaResponse = $_POST['g-recaptcha-response'];

$recaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
$recaptcha = json_decode($recaptcha);

if (!$recaptcha->success) {
    echo json_encode(['response' => 'error', 'message' => 'Error de validación reCAPTCHA.']);
    exit;
}

// DATOS DEL FORMULARIO
$service     = $_POST['service']    ?? '';
$phone       = $_POST['phone']      ?? '';
$name        = $_POST['name']       ?? '';
$adress      = $_POST['adress']     ?? '';
$cellphone   = $_POST['cellphone']  ?? '';
$email       = $_POST['email']      ?? '';
$radios      = $_POST['radios']     ?? '';
$otros       = $_POST['otros']      ?? '';
$description = $_POST['description']?? '';
$namerec     = $_POST['namerec']    ?? '';
$arearec     = $_POST['arearec']    ?? '';

// DESTINATARIO
$destinatario = 'pruebas3@allpasac.com'; // ← Cambia si es necesario

// CONSTRUCCIÓN DEL MENSAJE
$tipoReclamo = $_POST['radios'];
$asunto = "Nuevo Reclamo desde el Libro de Reclamaciones - $tipoReclamo";

$contenido = "
<strong>PROYECTO/SERVICIO:</strong> $service<br>
<strong>TELÉFONO:</strong> $phone<br>
<strong>CLIENTE:</strong> $name<br>
<strong>DIRECCIÓN:</strong> $adress<br>
<strong>CONTACTO:</strong> $cellphone<br>
<strong>E-MAIL:</strong> $email<br>
<strong>TIPO DE RECLAMO:</strong> $radios<br>
<strong>OTROS:</strong> $otros<br>
<strong>DESCRIPCIÓN DEL RECLAMO:</strong><br>$description<br><br>
<strong>NOMBRE DEL RESPONSABLE:</strong> $namerec<br>
<strong>ÁREA:</strong> $arearec<br>
";

$mail = new PHPMailer(true);

try {
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($email ?: $destinatario, $name);
    $mail->addAddress($destinatario);
    $mail->addReplyTo($email);
    
    $mail->Subject = $asunto;
    $mail->Body    = $contenido;
    $mail->isHTML(true);

    $mail->send();
    echo json_encode(['response' => 'success', 'message' => 'Reclamo enviado con éxito.']);
} catch (Exception $e) {
    echo json_encode(['response' => 'error', 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
