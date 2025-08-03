<?php
session_start(); // Inicia la sesión para acceder a variables como user_id o admin
require 'db.php'; // Conexión a la base de datos

// Verifica que se haya enviado el ID de la sala por POST
if (!isset($_POST['id'])) {
    exit("Acceso denegado"); // Si no hay ID, termina el script
}

$sala_id = (int) $_POST['id']; // Convierte el ID a entero por seguridad

// Si el que intenta eliminar es un usuario normal (no admin), validar que sea el creador
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // ID del usuario autenticado

    // Verifica si el usuario es el creador de la sala
    $stmt = $conn->prepare("SELECT creador_id FROM salas WHERE id = ?");
    $stmt->bind_param("i", $sala_id); // Asocia el ID de la sala
    $stmt->execute(); // Ejecuta la consulta
    $stmt->bind_result($creador_id); // Almacena el resultado en $creador_id
    $stmt->fetch(); // Obtiene el valor
    $stmt->close(); // Cierra la consulta

    if ($creador_id !== $user_id) {
        // Si no es el creador de la sala, no tiene permisos para eliminarla
        exit("No tienes permisos para eliminar esta sala.");
    }
} elseif (!isset($_SESSION['admin'])) {
    // Si no es usuario ni admin, denegar el acceso
    exit("Acceso denegado");
}

// Elimina todos los mensajes relacionados con la sala
$conn->query("DELETE FROM mensajes WHERE sala_id = $sala_id");

// Prepara la eliminación de la sala como tal
$stmt = $conn->prepare("DELETE FROM salas WHERE id = ?");
$stmt->bind_param("i", $sala_id); // Asocia el ID de la sala

// Ejecuta la eliminación
if ($stmt->execute()) {
    // Si lo elimina un usuario normal, lo redirige a sala.php con mensaje
    if (isset($_SESSION['user_id'])) {
        unset($_SESSION['sala_id']); // Borra la sala activa de la sesión
        header("Location: sala.php?msg=Sala eliminada");
    } else {
        // Si lo elimina un admin, redirige al panel de administrador
        header("Location: adminicio.php?msg=Sala eliminada");
    }
    exit(); // Termina el script
} else {
    // Si hay un error al eliminar, muestra el mensaje
    echo "Error al eliminar sala: " . $stmt->error;
}
?>
