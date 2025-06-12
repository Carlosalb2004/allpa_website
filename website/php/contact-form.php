<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir el autoload de Composer
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->SMTPDebug = 2;                         // 0 para producción, 2 para depuración detallada
    $mail->isSMTP();
    $mail->Host = 'mail.allpasac.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pruebas3@allpasac.com';
    $mail->Password = 'cu!KDk4[-[k%MJ,l@;';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Datos del formulario
    $nombre   = $_POST['nombre'];
    $email    = $_POST['email'];
    $telefono = $_POST['telefono'];
    $asunto   = $_POST['asunto'];
    $mensaje  = $_POST['mensaje'];

    // Configurar remitente y destinatario
    $mail->setFrom($email, $nombre);
    $mail->addAddress('pruebas3@allpasac.com', 'Contacto Web');

    // Validación y adjunto
    $archivoAdjuntado = false;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment($_FILES['file']['tmp_name'], $_FILES['file']['name']);
        $archivoAdjuntado = true;
    }

    // Contenido del mensaje
    $mail->isHTML(false);
    $mail->Subject = "Formulario Web: $asunto";
    $mail->Body =
        "Nombre: $nombre\n" .
        "Correo: $email\n" .
        "Teléfono: $telefono\n\n" .
        "Mensaje:\n$mensaje\n\n" .
        ($archivoAdjuntado ? "Se adjuntó un archivo: " . $_FILES['file']['name'] : "No se adjuntó ningún archivo.");

    // Enviar mensaje
    $mail->send();

    // Confirmación
    echo '¡El mensaje se envió correctamente!';
    if ($archivoAdjuntado) {
        echo ' El archivo "' . $_FILES['file']['name'] . '" fue adjuntado.';
    } else {
        echo ' No se adjuntó ningún archivo.';
    }

} catch (Exception $e) {
    echo 'Hubo un error al enviar el mensaje: ' . $mail->ErrorInfo;
}
