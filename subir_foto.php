<?php
session_start();
// Iniciamos la sesión para acceder a variables de sesión

require 'db.php';
// Incluimos la conexión a la base de datos

// Guardamos el ID del usuario actual desde la sesión
$user_id = $_SESSION['user_id'];

// --- PROCESO PARA SUBIR EL FONDO DE USUARIO ---

$carpeta = 'fondos_usuarios/'; 
// Carpeta donde se guardarán los fondos personalizados

// Obtenemos el nombre original del archivo subido para el fondo
$nombre = basename($_FILES['fondo']['name']);

// Generamos una ruta única para el archivo combinando carpeta, id único y nombre original
$ruta = $carpeta . uniqid() . "_" . $nombre;

// Intentamos mover el archivo temporal a la ruta destino
if (move_uploaded_file($_FILES['fondo']['tmp_name'], $ruta)) {
    // Preparamos la consulta para actualizar el campo fondo_chat en la base de datos
    $stmt = $conn->prepare("UPDATE usuarios SET fondo_chat = ? WHERE id = ?");
    $stmt->bind_param("si", $ruta, $user_id);
    $stmt->execute();

    // Si se actualizó correctamente, redirigimos a perfil.php
    header("Location: perfil.php");
} else {
    // Si no se pudo mover el archivo, mostramos mensaje de error
    echo "Error al subir fondo.";
}



// --- PROCESO PARA SUBIR LA FOTO DE PERFIL ---

// Validamos que el usuario esté logueado antes de procesar la foto
if (!isset($_SESSION['user_id'])) {
    exit("No autorizado");
}

// Validamos que la foto se haya subido sin errores
if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Obtenemos el nombre original del archivo foto
    $nombre = basename($_FILES['foto']['name']);

    // Definimos ruta destino con carpeta uploads y un prefijo de tiempo para hacer el nombre único
    $ruta = 'uploads/' . time() . '_' . $nombre;

    // Si la carpeta uploads no existe, la creamos con permisos 0777 recursivamente
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Movemos el archivo temporal a la carpeta uploads
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta)) {
        // Preparamos la consulta para actualizar foto_perfil del usuario
        $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("si", $ruta, $_SESSION['user_id']);
        $stmt->execute();

        // Redirigimos a perfil.php si todo fue exitoso
        header("Location: perfil.php");
        exit;
    } else {
        // Mensaje de error si no se pudo mover la foto
        echo "Error al mover la foto.";
    }
} else {
    // Mensaje de error si hubo un problema en la carga del archivo
    echo "Error en la carga del archivo.";
}
?>
