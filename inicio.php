<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Inicio - Selección de Sala</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        header {
            margin-bottom: 50px;
            width: 100%;
        }

        h1 {
            font-size: 2.4rem;
            color: #333;
            margin: 0 auto;
            max-width: 600px;
        }

        section {
            display: flex;
            flex-direction: column; /* Columna */
            gap: 25px;
            width: 100%;
            max-width: 400px;
            margin-bottom: 40px;
        }

        a.button {
            padding: 20px 40px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 22px;
            font-weight: 600;
            display: block;
            transition: background-color 0.3s ease;
            user-select: none;
        }

        a.button:hover,
        a.button:focus {
            background-color: #0056b3;
        }

        a.simple-link {
            color: red;
            font-weight: bold;
            text-decoration: none;
            font-size: 20px;
            margin: 10px auto;
            max-width: 400px;
            user-select: none;
        }

        a.simple-link:hover,
        a.simple-link:focus {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 25px 15px;
            }

            h1 {
                font-size: 1.8rem;
                max-width: 100%;
            }

            a.button {
                font-size: 20px;
                padding: 18px 35px;
                max-width: 100%;
            }

            a.simple-link {
                font-size: 18px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Bienvenido, <?= htmlspecialchars($_SESSION['username'] ?? 'Usuario') ?></h1>
    </header>

    <section>
        <a href="crear_sala.php" class="button">Crear Sala</a>
        <a href="unirse_sala.php" class="button">Unirse a Sala</a>
    </section>

    <a href="logout.php" class="simple-link">Cerrar sesión</a>
    <a href="perfil.php" class="simple-link">Perfil</a>
</body>
</html>

