<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mostrar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir PHPMailer
require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';

// Respuesta JSON
header('Content-Type: application/json');

// Capturar datos del formulario
$nombre   = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$email    = $_POST['email'] ?? '';
$asunto   = $_POST['asunto'] ?? '';
$mensaje  = $_POST['mensaje'] ?? '';
$archivo  = $_FILES['attachment'] ?? null;

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

// Validación de campos obligatorios
if (!$nombre || !$telefono || !$email || !$asunto || !$mensaje) {
    echo json_encode(['response' => 'error', 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

// Configuración de correo
$destinatario = 'pruebas3@allpasac.com'; // Cambiar a destino real
$asuntoCorreo = "Mensaje de Contacto: $asunto";
$cuerpoMensaje = <<<EOT
Nombre: $nombre
Teléfono: $telefono
Correo: $email
Asunto: $asunto

Mensaje:
$mensaje
EOT;

$mail = new PHPMailer(true);

try {
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($email ?: $destinatario, $nombre);
    $mail->addAddress($destinatario);
    $mail->addReplyTo($email);

    $mail->Subject = $asuntoCorreo;
    $mail->Body    = nl2br($cuerpoMensaje);
    $mail->isHTML(true);

    // Validar y adjuntar archivo
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
    } else {
        echo json_encode(['response' => 'error', 'message' => 'Error al subir el archivo.']);
        exit;
    }

    // Enviar
    $mail->send();
    echo json_encode(['response' => 'success', 'message' => 'Mensaje enviado con éxito.']);
} catch (Exception $e) {
    echo json_encode(['response' => 'error', 'message' => 'Error al enviar: ' . $mail->ErrorInfo]);
}
?>
