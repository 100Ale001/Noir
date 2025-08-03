<?php
require_once 'db.php'; // Conexión a la base de datos

// Verifica si se ha recibido el ID del usuario por POST
if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // Convierte el ID recibido a entero por seguridad

    // Prepara una consulta para eliminar al usuario con ese ID
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id); // Asocia el parámetro ID a la consulta
    $stmt->execute(); // Ejecuta la consulta
}

// Redirige al panel de administrador después de eliminar
header("Location: adminicio.php");
exit(); // Termina la ejecución del script
?>
