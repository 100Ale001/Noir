<?php
session_start(); 
// Iniciamos sesión para acceder a variables de sesión

require 'db.php'; 
// Incluimos conexión a la base de datos

// Verificamos que el usuario esté logueado, si no, detenemos la ejecución con mensaje
if (!isset($_SESSION['user_id'])) {
    die("No has iniciado sesión.");
}

$user_id = $_SESSION['user_id']; 
// Guardamos el id del usuario actual para usar en la consulta

// Verificamos que se haya enviado un archivo en el campo 'fondo' y que no haya error al subirlo
if (isset($_FILES['fondo']) && $_FILES['fondo']['error'] === UPLOAD_ERR_OK) {
    // Obtenemos el nombre original del archivo subido
    $nombreOriginal = basename($_FILES['fondo']['name']);
    
    // Extraemos la extensión del archivo y la convertimos a minúsculas
    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    
    // Definimos extensiones permitidas para el fondo
    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

    // Validamos que la extensión esté en la lista permitida
    if (!in_array($ext, $permitidas)) {
        die("Solo se permiten imágenes JPG, PNG o GIF."); // Detenemos si no es permitido
    }

    $carpeta = 'fondos_usuarios/'; 
    // Carpeta donde se guardarán los fondos personalizados

    // Verificamos si la carpeta existe, si no, la creamos con permisos 0755
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }

    // Creamos un nombre único para el archivo para evitar colisiones
    $nuevoNombre = uniqid() . "." . $ext;
    $ruta = $carpeta . $nuevoNombre;

    // Movemos el archivo temporal subido a la carpeta destino con el nuevo nombre
    if (move_uploaded_file($_FILES['fondo']['tmp_name'], $ruta)) {
        // Preparamos la consulta para actualizar el campo fondo_chat del usuario en la BD
        $stmt = $conn->prepare("UPDATE usuarios SET fondo_chat = ? WHERE id = ?");
        $stmt->bind_param("si", $ruta, $user_id); // Asignamos parámetros: ruta de la imagen y id de usuario
        
        // Ejecutamos la consulta y verificamos si fue exitosa
        if ($stmt->execute()) {
            // Si todo va bien, redirigimos al perfil con un parámetro para notificar éxito
            header("Location: perfil.php?fondo=ok");
            exit;
        } else {
            // Si falla la consulta, mostramos el error
            echo "Error al guardar en la base de datos: " . $stmt->error;
        }
    } else {
        // Si no se pudo mover el archivo, mostramos error
        echo "No se pudo mover el archivo subido.";
    }
} else {
    // Si no se subió archivo o hubo error en la subida, mostramos error con código
    echo "No se subió ningún archivo o hubo un error: " . $_FILES['fondo']['error'];
}
?>
