<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mostrar errores (útil en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir PHPMailer
require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';

// Devolver respuesta JSON
header('Content-Type: application/json');


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
$archivo  = $_FILES['attachment'] ?? null;

// Obtener fecha y hora actuales
$fecha = date("d/m/Y");   
$hora = date("H:i:s");

/*
// Validar reCAPTCHA
$recaptchaSecret = '6Ldz_GkrAAAAALFDNf8NpvQO5grNii0LA-_DN2F_';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

if (empty($recaptchaResponse)) {
    echo json_encode(['response' => 'error', 'message' => 'Por favor, completa el reCAPTCHA.']);
    exit;
}

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
$captchaSuccess = json_decode($verify);

if (!$captchaSuccess->success) {
    echo json_encode(['response' => 'error', 'message' => 'reCAPTCHA inválido. Intenta de nuevo.']);
    exit;
}
*/

// Validar reCAPTCHA
$recaptchaSecret = '6Lfg5mcrAAAAAF3dDczIttN-NZxsuxj15MbazUBd';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

if (empty($recaptchaResponse)) {
    echo json_encode(['response' => 'error', 'message' => 'Por favor, completa el reCAPTCHA.']);
    exit;
}
/*
// Opción 1: usando file_get_contents (requiere allow_url_fopen habilitado)
$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
$captchaSuccess = json_decode($verify);
*/
// Opción 2: usando cURL (recomendado si file_get_contents falla)

$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret'   => $recaptchaSecret,
    'response' => $recaptchaResponse
]);
$response = curl_exec($ch);
curl_close($ch);
$captchaSuccess = json_decode($response);

// DESTINATARIO
$destinatario = 'pruebas3@allpasac.com'; // ← Cambia si es necesario

// CONSTRUCCIÓN DEL MENSAJE
$tipoReclamo = $_POST['radios'];
$asunto = "Nuevo Reclamo desde el Libro de Reclamaciones - $tipoReclamo";

// Si se ha seleccionado "Otros", incluir el campo "otros" en el mensaje
$otrosInfo = ($radios === 'Otros' && !empty($otros)) ? "<strong>OTROS SERVICIOS:</strong> $otros<br>" : "";


$contenido = "
<strong>PROYECTO/SERVICIO:</strong> $service<br>
<strong>FECHA DE REGISTRO:</strong> $fecha<br>
<strong>HORA DE REGISTRO:</strong> $hora<br><br>
<strong>------------------------------------------------------</strong><br><br>

<strong>TELÉFONO:</strong> $phone<br>
<strong>CLIENTE:</strong> $name<br>
<strong>DIRECCIÓN:</strong> $adress<br>
<strong>CONTACTO:</strong> $cellphone<br>
<strong>E-MAIL:</strong> $email<br>
<strong>TIPO DE RECLAMO:</strong> $radios<br>
$otrosInfo
<strong>DESCRIPCIÓN DEL RECLAMO:</strong><br>$description<br><br>
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

    // Validar y adjuntar archivo (solo si fue enviado)
if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
    $tmp = $archivo['tmp_name'];
    $nombreArchivo = $archivo['name'];
    $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        echo json_encode(['response' => 'error', 'message' => 'Solo se permiten archivos PDF.']);
        exit;
    }

    if ($archivo['size'] > 25 * 1024 * 1024) {
        echo json_encode(['response' => 'error', 'message' => 'Archivo demasiado grande (máx 25MB).']);
        exit;
    }

    $mail->addAttachment($tmp, $nombreArchivo);
}


    $mail->send();
    echo json_encode(['response' => 'success', 'message' => 'Reclamo enviado con éxito.']);
} catch (Exception $e) {
    echo json_encode(['response' => 'error', 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
