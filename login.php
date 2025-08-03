<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Muestra todos los errores y advertencias (útil para desarrollo)

require 'db.php';
// Incluye el archivo de conexión a la base de datos

session_start();
// Inicia la sesión para manejar variables de sesión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica si el formulario fue enviado usando método POST

    $username = trim($_POST['username']);
    // Obtiene el valor del input 'username' y elimina espacios al inicio y final

    $password = $_POST['password'];
    // Obtiene el valor del input 'password'

    $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE username = ?");
    // Prepara una consulta para buscar el id y hash de la contraseña del usuario

    $stmt->bind_param("s", $username);
    // Vincula el parámetro username para prevenir inyección SQL

    $stmt->execute();
    // Ejecuta la consulta preparada

    $stmt->store_result();
    // Almacena el resultado para usar num_rows y bind_result

    $stmt->bind_result($id, $hash);
    // Vincula las columnas resultado a variables PHP ($id y $hash)

    if ($stmt->num_rows == 1) {
        // Si existe exactamente un usuario con ese nombre

        $stmt->fetch();
        // Trae los datos vinculados ($id y $hash)

        if (password_verify($password, $hash)) {
            // Verifica que la contraseña ingresada coincida con el hash almacenado

            $_SESSION['user_id'] = $id;
            // Guarda el id del usuario en sesión

            $_SESSION['username'] = $username;
            // Guarda el nombre de usuario en sesión

            header("Location: inicio.php");
            // Redirige al usuario a la página principal o dashboard

            exit();
            // Termina la ejecución del script después de la redirección
        } else {
            $error = "Contraseña incorrecta.";
            // Define mensaje de error si la contraseña no coincide
        }
    } else {
        $error = "Usuario no encontrado.";
        // Define mensaje de error si no existe el usuario
    }

    $stmt->close();
    // Cierra la consulta preparada
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login</title>

<style>
    /* Reset general y box-sizing para controlar margenes y padding */
    *, *::before, *::after {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Estilo para el body: centrado con flexbox, fuente y fondo claro */
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

    /* Estilo para el título con clase 'letras' */
    h2.letras {
        font-size: 2.8rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 30px;
        color: #333;
    }

    /* Etiquetas del formulario */
    label {
        font-size: 1.3rem;
        margin-bottom: 8px;
        align-self: flex-start;
        color: #444;
    }

    /* Inputs texto y password: tamaño, bordes, margen */
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

    /* Cambios en inputs cuando reciben foco */
    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0,123,255,0.3);
    }

    /* Estilo del botón enviar */
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

    /* Efectos hover y focus para el botón */
    button:hover,
    button:focus {
        background-color: #0056b3;
    }

    /* Estilo para el párrafo que incluye el enlace a registro */
    #asd {
        font-size: 1.1rem;
        color: #333;
        user-select: none;
    }

    /* Estilo para el enlace dentro del párrafo #asd */
    #asd a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
        margin-left: 5px;
    }

    /* Efectos hover y focus para el enlace */
    #asd a:hover,
    #asd a:focus {
        text-decoration: underline;
    }

    /* Estilo para mostrar mensaje de error */
    .error-message {
        color: #cc0000;
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 1.2rem;
        text-align: center;
        width: 100%;
    }

    /* Responsive: adapta el formulario para pantallas pequeñas */
    @media (max-width: 480px) {
        form {
            padding: 30px 20px;
            max-width: 100%;
        }

        h2.letras {
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

        #asd {
            font-size: 1rem;
        }
    }
</style>
</head>
<body>

<!-- Muestra el mensaje de error si existe la variable $error -->
<?php if (isset($error)): ?>
    <p class="error-message"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario para iniciar sesión -->
<form method="POST" action="">
    <h2 class="letras">Iniciar sesión</h2>

    <label for="username">Usuario:</label>
    <input type="text" name="username" id="username" required autofocus>
    <!-- Campo para usuario, obligatorio, con foco inicial -->

    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" required>
    <!-- Campo para contraseña, obligatorio -->

    <button type="submit">Entrar</button>
    <!-- Botón para enviar formulario -->

    <p id="asd">¿No tienes cuenta? <a id="qwe" href="registro.php">Registrarse</a></p>
    <!-- Enlace para ir a página de registro -->
</form>

</body>
</html>
