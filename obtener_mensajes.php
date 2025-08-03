<?php
session_start(); // Inicia la sesión para usar variables de sesión
require 'db.php'; // Conexión a la base de datos

// Verifica que exista la variable de sesión sala_id
if (!isset($_SESSION['sala_id'])) {
    // Devuelve un JSON con mensaje de error y termina
    echo json_encode(['html' => '<p>Error: sala_id no está definido en sesión.</p>']);
    exit;
}

$sala_id = $_SESSION['sala_id']; // Obtiene el id de la sala

// Prepara la consulta para obtener mensajes y datos del usuario que los envió
$stmt = $conn->prepare("
    SELECT m.mensaje, m.created_at, u.username, u.foto_perfil
    FROM mensajes m
    JOIN usuarios u ON m.user_id = u.id
    WHERE m.sala_id = ?
    ORDER BY m.created_at ASC
");

// Verifica que la preparación de la consulta fue exitosa
if (!$stmt) {
    // Devuelve un JSON con el error y termina
    echo json_encode(['html' => '<p>Error en prepare: ' . $conn->error . '</p>']);
    exit;
}

$stmt->bind_param("i", $sala_id); // Asocia el parámetro sala_id a la consulta

// Ejecuta la consulta y verifica errores
if (!$stmt->execute()) {
    echo json_encode(['html' => '<p>Error al ejecutar: ' . $stmt->error . '</p>']);
    exit;
}

$result = $stmt->get_result(); // Obtiene el resultado de la consulta
$html = ''; // Inicializa la variable para almacenar el HTML generado

// Si no hay mensajes en la sala, muestra un mensaje indicándolo
if ($result->num_rows === 0) {
    $html = "<p>No hay mensajes en esta sala aún.</p>";
} else {
    // Recorre cada fila con un mensaje
    while ($row = $result->fetch_assoc()) {
        $hora = date('H:i', strtotime($row['created_at'])); // Formatea la hora del mensaje
        $usuario = htmlspecialchars($row['username']); // Escapa caracteres especiales del usuario
        $mensaje = htmlspecialchars($row['mensaje']); // Escapa caracteres especiales del mensaje
        // Si el usuario tiene foto, usa esa, si no, usa 'default.jpg'
        $foto = $row['foto_perfil'] ? htmlspecialchars($row['foto_perfil']) : 'default.jpg';

        // Construye el bloque HTML para cada mensaje
        $html .= "
            <div style='display: flex; align-items: center; margin-bottom: 10px;'>
                <img src='$foto' width='40' height='40' style='border-radius: 50%; margin-right: 10px;'>
                <div>
                    <strong>$usuario</strong> <span style='font-size: small;'>[$hora]</span><br>
                    $mensaje
                </div>
            </div>
        ";
    }
}

// Envía el HTML generado en formato JSON para que pueda ser usado en AJAX u otro frontend
echo json_encode(['html' => $html]);
