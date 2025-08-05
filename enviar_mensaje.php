<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['sala_id'])) {
    exit('No autorizado');
}

$user_id = $_SESSION['user_id'];
$sala_id = $_SESSION['sala_id'];
if (!filter_var($user_id, FILTER_VALIDATE_INT) || !filter_var($sala_id, FILTER_VALIDATE_INT)) {
    exit('Datos inválidos');
}

$mensaje = trim($_POST['mensaje'] ?? '');

if ($mensaje === '') {
    exit('Mensaje vacío');
}

$stmt = $conn->prepare("INSERT INTO mensajes (user_id, sala_id, mensaje, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $user_id, $sala_id, $mensaje);

if ($stmt->execute()) {
    echo "OK";
    $stmt->execute();
} else {
    echo "Error: " . $stmt->error;
}
?>