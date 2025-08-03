<?php
session_start(); 
// Inicia la sesión para manejar variables de sesión, como mensajes de usuario o estado

require 'db.php'; 
// Incluye el archivo con la conexión a la base de datos

// Verifica si el formulario fue enviado por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    $username = trim($_POST['username']); 
    // Obtiene el nombre de usuario enviado y elimina espacios en blanco al inicio y final

    $password = $_POST['password']; 
    // Obtiene la contraseña ingresada

    $password2 = $_POST['password2']; 
    // Obtiene la confirmación de la contraseña ingresada

    // Validación básica de campos obligatorios
    if ($username == '' || $password == '') {
        $error = "Completa todos los campos."; 
        // Si el usuario o contraseña está vacío, define mensaje de error
    } 
    // Verifica que ambas contraseñas coincidan
    elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden."; 
        // Define error si no coinciden
    } else {
        // Validación de seguridad para la contraseña
        if (
            strlen($password) < 8 ||             // Comprueba que tenga al menos 8 caracteres
            !preg_match('/[A-Z]/', $password) || // Que contenga al menos una letra mayúscula
            !preg_match('/[a-z]/', $password) || // Que contenga al menos una letra minúscula
            !preg_match('/[0-9]/', $password) || // Que contenga al menos un número
            !preg_match('/[\W_]/', $password)    // Que contenga al menos un carácter especial (no alfanumérico)
        ) {
            $error = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
            // Si no cumple con todos los requisitos, define mensaje de error
        } else {
            // Consulta para verificar si el usuario ya existe en la base de datos
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
            $stmt->bind_param("s", $username); 
            // Vincula el parámetro de nombre de usuario para prevenir inyección SQL
            $stmt->execute(); 
            // Ejecuta la consulta
            $stmt->store_result(); 
            // Guarda el resultado para poder contar filas

            if ($stmt->num_rows > 0) {
                $error = "El usuario ya existe."; 
                // Si ya hay un usuario con ese nombre, muestra error
            } else {
                // Si el usuario no existe, se procede a crear uno nuevo
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                // Hashea la contraseña para almacenarla segura

                $stmt2 = $conn->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
                $stmt2->bind_param("ss", $username, $pass_hash);
                // Prepara la consulta y vincula parámetros para insertar usuario

                if ($stmt2->execute()) {
                    $_SESSION['user_id'] = $stmt2->insert_id;
                    // Guarda el ID generado para el usuario en la sesión (opcional aquí)
                    $_SESSION['username'] = $username;
                    // Guarda el nombre de usuario en sesión

                    header("Location: login.php");
                    // Redirige al login para iniciar sesión
                    exit();
                } else {
                    $error = "Error al registrar usuario.";
                    // Mensaje en caso de fallo al insertar en la base de datos
                }
            }
            $stmt->close();
            // Cierra la primera consulta
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Registro</title>

<style>
    /* Reset y box-sizing para controlar espacios y tamaño */
    *, *::before, *::after {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Estilos para el cuerpo: centrar contenido y fuente */
    body {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f0f2f5;
        font-family: Arial, sans-serif;
        padding: 20px;
    }

    /* Estilo del formulario: fondo blanco, padding, bordes redondeados y sombra */
    form {
        background-color: white;
        padding: 40px 30px;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 450px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Estilo título principal del formulario */
    h2 {
        font-size: 2.6rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 30px;
        color: #333;
    }

    /* Estilo para las etiquetas de inputs */
    label {
        font-size: 1.3rem;
        margin-bottom: 8px;
        align-self: flex-start;
        color: #444;
        width: 100%;
    }

    /* Inputs de texto y contraseña: tamaño, bordes y espacio */
    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 14px 12px;
        font-size: 1.2rem;
        border-radius: 10px;
        border: 1.8px solid #ccc;
        margin-bottom: 25px;
        transition: border-color 0.3s ease;
    }

    /* Cambios en inputs al recibir foco */
    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0,123,255,0.3);
    }

    /* Botón: color, tamaño, bordes redondeados y cursor */
    button {
        background-color: #007bff;
        color: white;
        font-size: 1.4rem;
        padding: 16px 0;
        width: 100%;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s ease;
        margin-bottom: 20px;
        user-select: none;
    }

    /* Efecto hover y focus para botón */
    button:hover,
    button:focus {
        background-color: #0056b3;
    }

    /* Texto bajo el botón para registro o link */
    p {
        font-size: 1.1rem;
        color: #333;
        user-select: none;
    }

    /* Estilo para links dentro del párrafo */
    p a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
        margin-left: 5px;
    }

    /* Efectos hover y focus para links */
    p a:hover,
    p a:focus {
        text-decoration: underline;
    }

    /* Estilo para mensaje de error */
    .error-message {
        color: #cc0000;
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 1.2rem;
        text-align: center;
        width: 100%;
    }

    /* Responsive para pantallas pequeñas */
    @media (max-width: 480px) {
        form {
            padding: 30px 20px;
            max-width: 100%;
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 25px;
        }

        label {
            font-size: 1.1rem;
        }

        input[type="text"],
        input[type="password"] {
            font-size: 1.1rem;
            padding: 12px 10px;
            margin-bottom: 20px;
        }

        button {
            font-size: 1.3rem;
            padding: 14px 0;
        }

        p {
            font-size: 1rem;
        }
    }
</style>
</head>
<body>

<!-- Muestra el mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error-message"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario de registro, envía datos a esta misma página -->
<form method="POST" action="">
    <h2>Registro</h2>

    <label for="username">Usuario:</label>
    <input type="text" name="username" id="username" required autofocus>
    <!-- Campo para usuario, obligatorio y con foco inicial -->

    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" required>
    <!-- Campo para contraseña, obligatorio -->

    <label for="password2">Repetir Contraseña:</label>
    <input type="password" name="password2" id="password2" required>
    <!-- Campo para repetir la contraseña, obligatorio -->

    <button type="submit">Registrar</button>
    <!-- Botón para enviar el formulario -->

    <p>¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
    <!-- Enlace para ir a la página de login -->
</form>

</body>
</html>
