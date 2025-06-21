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

// Obtener datos del formulario
$nombre    = $_POST['name'] ?? '';
$apellido  = $_POST['surname'] ?? '';
$correo    = $_POST['email'] ?? '';
$telefono  = $_POST['phone'] ?? '';
$puesto    = $_POST['puesto'] ?? '';
$archivo   = $_FILES['attachment'] ?? null;

// Validar reCAPTCHA
$recaptchaSecret = '6Lfg5mcrAAAAAF3dDczIttN-NZxsuxj15MbazUBd';
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

// Validación básica
if (!$nombre || !$apellido || !$correo || !$telefono || !$puesto) {
	echo json_encode(['response' => 'error', 'message' => 'Todos los campos son obligatorios.']);
	exit;
}

// Configura tu correo destino
$destinatario = 'pruebas3@allpasac.com';  // ← Cámbialo si es necesario
$asunto = "Postulación de $nombre $apellido";
$mensaje = <<<EOT
Nombres: $nombre
Apellidos: $apellido
Correo: $correo
Teléfono: $telefono
Puesto: $puesto
EOT;

$mail = new PHPMailer(true);

try {
	$mail->CharSet = 'UTF-8';
	$mail->setFrom($correo ?: $destinatario, "$nombre $apellido");
	$mail->addAddress($destinatario);
	$mail->addReplyTo($correo);

	$mail->Subject = $asunto;
	$mail->Body    = nl2br($mensaje);
	$mail->isHTML(true);

	// Validar y adjuntar archivo PDF
	if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
		$tmp = $archivo['tmp_name'];
		$nombreArchivo = $archivo['name'];
		$ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

		if ($ext !== 'pdf') {
			echo json_encode(['response' => 'error', 'message' => 'Solo se permiten archivos PDF.']);
			exit;
		}

		if ($archivo['size'] > 2 * 1024 * 1024) {
			echo json_encode(['response' => 'error', 'message' => 'Archivo demasiado grande (máx 2MB).']);
			exit;
		}

		$mail->addAttachment($tmp, $nombreArchivo);
	} else {
		echo json_encode(['response' => 'error', 'message' => 'Error al subir el archivo.']);
		exit;
	}

	// Enviar correo
	$mail->send();
	echo json_encode(['response' => 'success', 'message' => 'Correo enviado con éxito.']);

} catch (Exception $e) {
	echo json_encode(['response' => 'error', 'message' => 'Error al enviar: ' . $mail->ErrorInfo]);
}
