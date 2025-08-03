<?php
// Inicia la sesión para poder acceder a las variables de sesión del usuario
session_start();

// Conecta con la base de datos
require 'db.php';  

// Verifica si el usuario está logueado
// Si no lo está, lo redirige al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirecciona al login
    exit; // Detiene la ejecución del script
}

// Obtiene el ID del usuario desde la sesión
$user_id = $_SESSION['user_id'];

// Comprueba si el formulario fue enviado por método POST
// y que el campo 'nuevo_nombre' no esté vacío
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['nuevo_nombre'])) {
    
    // Limpia los espacios en blanco del nuevo nombre ingresado
    $nuevo_nombre = trim($_POST['nuevo_nombre']);

    // Validación: el nombre debe tener entre 3 y 20 caracteres
    if (strlen($nuevo_nombre) < 3 || strlen($nuevo_nombre) > 20) {
        echo "El nombre debe tener entre 3 y 20 caracteres.";
        exit; // Detiene la ejecución si el nombre no cumple la validación
    }

    // Prepara una consulta SQL para actualizar el nombre de usuario
    $stmt = $conn->prepare("UPDATE usuarios SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_nombre, $user_id); // "s" = string, "i" = integer

    // Ejecuta la consulta y verifica si fue exitosa
    if ($stmt->execute()) {
        // Actualiza también el nombre en la sesión para reflejar el cambio
        $_SESSION['username'] = $nuevo_nombre;
        echo "Nombre actualizado con éxito. <a href='perfil.php'>Volver al perfil</a>";
    } else {
        // Si algo falla, muestra un mensaje de error
        echo "Hubo un error al actualizar el nombre.";
    }

    // Cierra la consulta preparada para liberar recursos
    $stmt->close();

} else {
    // Si no se envió el formulario correctamente o el campo está vacío
    echo "Nombre inválido.";
}
?>
