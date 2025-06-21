<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';

header('Content-Type: application/json');

$nombre = $_POST['name'] ?? '';
$apellido = $_POST['surname'] ?? '';
$correo = $_POST['email'] ?? '';
$telefono = $_POST['phone'] ?? '';
$puesto = $_POST['puesto'] ?? '';
$archivo = $_FILES['attachment'] ?? null;

$destinatario = 'pruebas3@allpasac.com'; // <---- CAMBIA ESTO
$asunto = "Postulación de $nombre $apellido";
$mensaje = "Nombres: $nombre\nApellidos: $apellido\nCorreo: $correo\nTeléfono: $telefono\nPuesto: $puesto";

$mail = new PHPMailer(true);

try {
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($correo ?: $destinatario, "$nombre $apellido");
    $mail->addAddress($destinatario);
    $mail->addReplyTo($correo);

    $mail->Subject = $asunto;
    $mail->Body = nl2br($mensaje);
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

        if ($archivo['size'] > 2 * 1024 * 1024) {
            echo json_encode(['response' => 'error', 'message' => 'Archivo demasiado grande (máx 2MB).']);
            exit;
        }

        $mail->addAttachment($tmp, $nombreArchivo);
    } else {
        echo json_encode(['response' => 'error', 'message' => 'Error al subir el archivo.']);
        exit;
    }

    $mail->send();
    echo json_encode(['response' => 'success', 'message' => 'Correo enviado con éxito.']);
} catch (Exception $e) {
    echo json_encode(['response' => 'error', 'message' => $mail->ErrorInfo]);
}
