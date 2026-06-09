<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
  
    $form_type = $_POST['form_type'] ?? '';
    
    
    $destinatario = "logistica.nexus7@gmail.com"; 

    if ($form_type === 'clientes') {
        $empresa = htmlspecialchars($_POST['empresa_name'] ?? '');
        $nombre = htmlspecialchars($_POST['cliente_fullname'] ?? '');
        $telefono = htmlspecialchars($_POST['cliente_phone'] ?? '');
        $email = htmlspecialchars($_POST['cliente_email'] ?? '');
        $envios = htmlspecialchars($_POST['select_envios'] ?? '');
        $bulto = htmlspecialchars($_POST['select_bulto'] ?? '');
        $mensaje = htmlspecialchars($_POST['cliente_msg'] ?? '');
        
        $asunto = "Nueva Consulta de Empresa: $empresa";
        $cuerpo = "Has recibido una nueva consulta empresarial desde la Web:\n\n" .
                  "• Empresa/Web: $empresa\n" .
                  "• Contacto: $nombre\n" .
                  "• WhatsApp: $telefono\n" .
                  "• Email de contacto: $email\n" .
                  "• Envíos mensuales estimados: $envios\n" .
                  "• Tamaño de bulto: $bulto\n\n" .
                  "• Mensaje o requerimiento:\n$mensaje\n";
                  
    } else if ($form_type === 'choferes') {
        $nombre = htmlspecialchars($_POST['chofer_fullname'] ?? '');
        $telefono = htmlspecialchars($_POST['chofer_phone'] ?? '');
        $email = htmlspecialchars($_POST['chofer_email'] ?? '');
        $vehiculo = htmlspecialchars($_POST['select_vehiculo'] ?? '');
        $cobertura = htmlspecialchars($_POST['select_cobertura'] ?? '');
        $mensaje = htmlspecialchars($_POST['chofer_msg'] ?? '');
        
        $asunto = "Nueva Postulación de Chofer: $nombre";
        $cuerpo = "Un transportista se ha postulado desde la Web:\n\n" .
                  "• Nombre Completo: $nombre\n" .
                  "• WhatsApp: $telefono\n" .
                  "• Email: $email\n" .
                  "• Tipo de Unidad: $vehiculo\n" .
                  "• Zona de Cobertura: $cobertura\n\n" .
                  "• Detalles del equipo/Experiencia:\n$mensaje\n";
    } else {
        echo json_encode(["success" => false, "message" => "Formulario no reconocido."]);
        exit;
    }

    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8" . "\r\n";
    $headers .= "From: Web Nexus <no-reply@logistica.nexus7@gmail.com>" . "\r\n"; 
    $headers .= "Reply-To: $email" . "\r\n";

    
    if (mail($destinatario, $asunto, $cuerpo, $headers)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "El servidor de correo falló."]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>