<?php
session_start();
// Iniciamos sesión para acceder a variables de sesión

require 'db.php';
// Incluimos la conexión a la base de datos

// Verificamos que el usuario esté logueado; si no, redirigimos a login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Procesamos el formulario solo si es una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenemos el id de la sala enviado desde el formulario y lo convertimos a entero
    $sala_id = intval($_POST['sala_id']);

    // Obtenemos la contraseña enviada, o cadena vacía si no se envió
    $contrasena = $_POST['contrasena'] ?? '';

    // Preparamos la consulta para obtener si la sala es privada y su contraseña hasheada
    $stmt = $conn->prepare("SELECT es_privada, contrasena FROM salas WHERE id = ?");
    $stmt->bind_param("i", $sala_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si la sala existe
    if ($row = $result->fetch_assoc()) {
        // Si la sala es privada, verificamos la contraseña con password_verify
        if ($row['es_privada'] && !password_verify($contrasena, $row['contrasena'])) {
            // Si la contraseña no coincide, mostramos mensaje de error y detenemos ejecución
            echo "Contraseña incorrecta.";
            exit;
        }
        // Si todo está bien, guardamos el id de sala en sesión para usar en otras páginas
        $_SESSION['sala_id'] = $sala_id;

        // Redirigimos al usuario a la página principal de la sala
        header("Location: sala.php");
        exit;
    } else {
        // Si no se encontró la sala, mostramos mensaje de error
        echo "Sala no encontrada.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Unirse a Sala</title>
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

        select,
        input[type="password"] {
            font-size: 1.2rem;
            padding: 14px 12px;
            border-radius: 10px;
            border: 1.8px solid #ccc;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        select:focus,
        input[type="password"]:focus {
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

            select,
            input[type="password"] {
                font-size: 1.1rem;
                padding: 12px 10px;
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

    <h2>Unirse a una Sala</h2>

    <form method="POST">
        <select name="sala_id" required>
            <option value="">Selecciona una sala</option>
            <?php
            // Obtenemos todas las salas de la base de datos para listarlas en el select
            $res = $conn->query("SELECT id, nombre FROM salas");
            while ($sala = $res->fetch_assoc()) {
                // Imprimimos cada sala como opción en el select, escapando caracteres especiales
                echo "<option value='{$sala['id']}'>" . htmlspecialchars($sala['nombre']) . "</option>";
            }
            ?>
        </select>

        <!-- Campo para ingresar contraseña, si la sala es privada -->
        <input type="password" name="contrasena" placeholder="Contraseña (si es privada)">

        <button type="submit">Unirse</button>
    </form>
</div>

</body>
</html>

