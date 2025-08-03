<?php
session_start(); // Inicia la sesión para acceder a variables como el ID del usuario
require 'db.php'; // Conexión a la base de datos

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirige al login si no está autenticado
    exit;
}

// Si se envió el formulario por POST (crear sala)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']); // Obtiene y limpia el nombre de la sala
    $es_privada = isset($_POST['es_privada']) ? 1 : 0; // Verifica si es privada (checkbox)
    // Si es privada, hashea la contraseña, si no, queda en null
    $contrasena = $es_privada ? password_hash($_POST['contrasena'], PASSWORD_DEFAULT) : null;

    // Prepara la consulta para insertar la nueva sala
    $stmt = $conn->prepare("INSERT INTO salas (nombre, es_privada, contrasena, creador_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisi", $nombre, $es_privada, $contrasena, $_SESSION['user_id']); // Asocia valores a la consulta

    if ($stmt->execute()) {
        $_SESSION['sala_id'] = $stmt->insert_id; // Guarda el ID de la nueva sala creada en la sesión
        header("Location: sala.php"); // Redirige a la sala recién creada
        exit;
    } else {
        echo "Error al crear sala: " . $stmt->error; // Muestra error si falla la creación
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Sala</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        /* Reset y box-sizing */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 480px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            padding: 30px 25px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        a.button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 14px 25px;
            border-radius: 10px;
            font-size: 1.3rem;
            font-weight: 600;
            user-select: none;
            transition: background-color 0.3s ease;
            width: fit-content;
            margin-bottom: 20px;
        }

        a.button:hover,
        a.button:focus {
            background-color: #0056b3;
        }

        h2 {
            font-size: 2rem;
            color: #333;
            text-align: center;
            margin-bottom: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        input[type="text"],
        input[type="password"] {
            font-size: 1.2rem;
            padding: 14px 12px;
            border-radius: 10px;
            border: 1.8px solid #ccc;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0,123,255,0.3);
        }

        label {
            font-size: 1.1rem;
            color: #444;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        input[type="checkbox"] {
            transform: scale(1.3);
            cursor: pointer;
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
        }

        button:hover,
        button:focus {
            background-color: #0056b3;
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

            input[type="text"],
            input[type="password"] {
                font-size: 1.1rem;
                padding: 12px 10px;
            }

            label {
                font-size: 1rem;
            }

            button {
                font-size: 1.1rem;
                padding: 12px 0;
            }

            a.button {
                font-size: 1.1rem;
                padding: 12px 20px;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Botón para regresar a la página de inicio -->
    <a href="inicio.php" class="button">Volver</a>

    <!-- Título del formulario -->
    <h2>Crear Sala</h2>

    <!-- Formulario para crear una nueva sala -->
    <form action="crear_sala.php" method="POST">
        <!-- Campo para el nombre de la sala -->
        <input type="text" name="nombre" required placeholder="Nombre de la sala">

        <!-- Checkbox para hacer la sala privada -->
        <label>
            <input type="checkbox" name="es_privada" id="checkPrivada" onchange="togglePassword()"> Privada
        </label>

        <!-- Campo para ingresar la contraseña si es privada -->
        <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña (si es privada)" style="display: none;">

        <!-- Botón para crear la sala -->
        <button type="submit">Crear sala</button>
    </form>
</div>

<!-- Script para mostrar/ocultar el campo de contraseña al marcar el checkbox -->
<script>
function togglePassword() {
    const checkbox = document.getElementById('checkPrivada'); // Obtiene el checkbox
    const passwordInput = document.getElementById('contrasena'); // Obtiene el campo de contraseña
    passwordInput.style.display = checkbox.checked ? 'block' : 'none'; // Muestra u oculta según el estado del checkbox
}
</script>

</body>
</html>
