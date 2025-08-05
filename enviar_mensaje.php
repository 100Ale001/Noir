<?php
session_start(); // Inicia la sesión para acceder a variables como user_id y sala_id
require 'db.php'; // Conexión a la base de datos

// Verifica que el usuario esté autenticado y que tenga una sala activa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['sala_id'])) {
    exit('No autorizado'); // Detiene el script si no está autorizado
}

$user_id = $_SESSION['user_id']; // ID del usuario que envía el mensaje
$sala_id = $_SESSION['sala_id']; // ID de la sala donde se envía el mensaje
$mensaje = trim($_POST['mensaje'] ?? ''); // El mensaje enviado, quitando espacios

// Si no se envió mensaje, no se guarda nada
if ($mensaje === '') {
    exit('Mensaje vacío'); // Termina el script
}

if (strlen($mensaje) > 255) {
    exit('Mensaje demasiado largo'); // Termina el script si es muy largo
}

// Prepara la inserción en la tabla mensajes (sin imagen)
$stmt = $conn->prepare("INSERT INTO mensajes (user_id, sala_id, mensaje, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $user_id, $sala_id, $msj); //Asocia los valores

// Ejecuta la consulta e informa si fue exitoso o no
if ($stmt->execute()) {
    echo "OK"; // Éxito
} else {
    echo "Error: " . $stmt->error; // Muestra error si falla la consulta
}
?>
