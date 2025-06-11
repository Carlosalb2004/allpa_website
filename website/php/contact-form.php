<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $asunto = isset($_POST['asunto']) ? $_POST['asunto'] : '';
    $mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : '';
    

    $destinatario = "pruebas3@allpasac.com";  
    $subject = "Nuevo mensaje desde el formulario de contacto: " . $asunto;
    $cuerpo = "Nombre: $nombre\nTeléfono: $telefono\nEmail: $email\nAsunto: $asunto\n\nMensaje:\n$mensaje";


    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();                                      
        $mail->Host = 'mail.allpasac.com';                      
        $mail->SMTPAuth = true;                                
        $mail->Username = 'pruebas3@allpasac.com';             
        $mail->Password = 'cu!KDk4[-[k%MJ,l@;';               
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;       
        $mail->Port = 465;          

        // Receptores
        $mail->setFrom($email, $nombre);                       
        $mail->addAddress($destinatario);                       

        // Asunto y cuerpo del mensaje
        $mail->Subject = $subject;
        $mail->Body    = $cuerpo;
        $mail->AltBody = strip_tags($cuerpo);                   

        // Adjuntar el archivo (si hay)
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_name = $_FILES['file']['name'];
            $file_type = mime_content_type($file_tmp);
            $mail->addAttachment($file_tmp, $file_name, null, $file_type);  // Adjuntar archivo
        }

        // Enviar el correo
        $mail->send();
        echo "Se envió tu mensaje exitosamente.";
    } catch (Exception $e) {
        echo "Hubo un problema al enviar tu mensaje. Error: {$mail->ErrorInfo}";
    }
}
?>

