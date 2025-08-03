<?php
session_start(); // Inicia la sesión para acceder a datos del usuario
require 'db.php'; // Conecta con la base de datos

// Verifica que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Si no, redirige a la página de login
    exit;
}

$user_id = $_SESSION['user_id']; // Obtiene el ID del usuario activo

// Prepara la consulta para obtener el nombre de usuario y foto de perfil
$stmt = $conn->prepare("SELECT username, foto_perfil FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id); // Asocia el parámetro de usuario
$stmt->execute(); // Ejecuta la consulta
$result = $stmt->get_result(); // Obtiene el resultado
$usuario = $result->fetch_assoc(); // Extrae los datos del usuario
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Perfil de Usuario</title> <!-- Título de la página -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        /* Reset y caja modelo */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container {
            background: white;
            max-width: 480px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            padding: 30px 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 25px;
            box-sizing: border-box;
        }

        h2 {
            font-size: 2rem;
            color: #333;
            text-align: center;
        }

        img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #007bff;
        }

        p.no-photo {
            font-size: 1.2rem;
            color: #777;
            text-align: center;
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 600;
            font-size: 1.1rem;
            color: #444;
        }

        input[type="file"],
        input[type="text"] {
            padding: 12px 10px;
            font-size: 1.1rem;
            border-radius: 10px;
            border: 1.8px solid #ccc;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="file"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0,123,255,0.3);
        }

        button {
            background-color: #007bff;
            color: white;
            font-size: 1.3rem;
            padding: 14px 0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
            user-select: none;
            width: 100%;
        }

        button:hover,
        button:focus {
            background-color: #0056b3;
        }

        a.volver {
            margin-top: 20px;
            display: inline-block;
            color: #007bff;
            font-weight: 600;
            font-size: 1.2rem;
            text-decoration: none;
            user-select: none;
        }

        a.volver:hover,
        a.volver:focus {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container {
                padding: 25px 20px;
                max-width: 100%;
            }

            h2 {
                font-size: 1.6rem;
            }

            img {
                width: 120px;
                height: 120px;
            }

            label {
                font-size: 1rem;
            }

            input[type="file"],
            input[type="text"] {
                font-size: 1rem;
                padding: 10px 8px;
            }

            button {
                font-size: 1.1rem;
                padding: 12px 0;
            }

            a.volver {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Muestra el nombre del usuario con protección contra XSS -->
    <h2>Perfil de <?= htmlspecialchars($usuario['username']) ?></h2>

    <?php if ($usuario['foto_perfil']): ?>
        <!-- Si tiene foto, la muestra -->
        <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil"><br>
    <?php else: ?>
        <!-- Si no tiene foto, muestra un mensaje -->
        <p class="no-photo">No has subido una foto de perfil.</p>
    <?php endif; ?>

    <!-- Formulario para subir foto de perfil -->
    <form action="subir_foto.php" method="POST" enctype="multipart/form-data">
        <label for="foto">Subir Foto de Perfil:</label>
        <input type="file" id="foto" name="foto" accept="image/*" required> <!-- Input para archivo imagen -->
        <button type="submit">Subir Foto de Perfil</button> <!-- Botón para enviar -->
    </form>

    <!-- Formulario para subir imagen de fondo -->
    <form action="subir_fondo.php" method="POST" enctype="multipart/form-data">
        <label for="fondo">Sube una imagen de fondo:</label>
        <input type="file" id="fondo" name="fondo" accept="image/*"> <!-- Input para imagen de fondo -->
        <button type="submit">Guardar fondo</button> <!-- Botón para enviar -->
    </form>

    <!-- Formulario para cambiar nombre de usuario -->
    <form action="cambiar_nombre.php" method="POST">
        <label for="nuevo_nombre">Nuevo nombre de usuario:</label>
        <input type="text" id="nuevo_nombre" name="nuevo_nombre" required>
        <button type="submit">Cambiar Nombre</button>
    </form>

    <!-- Enlace para volver a la página de inicio -->
    <a href="inicio.php" class="volver">Volver al inicio</a>
</div>

</body>
</html>
