<?php
session_start();
// Iniciamos sesión para manejar variables de sesión como usuario, admin, sala, etc.

// Si no está ni el usuario ni el admin en sesión, redirigimos al login para evitar acceso no autorizado
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
    header("Location: login.html");
    exit;
}

// Si es el admin quien entra y hay un parámetro 'id' en la URL, definimos la sala_id desde GET (para que admin pueda elegir sala)
if (isset($_SESSION['admin']) && isset($_GET['id'])) {
    $sala_id = intval($_GET['id']); // Convertimos el id recibido a entero para evitar inyección
    $_SESSION['sala_id'] = $sala_id; // Guardamos la sala_id en sesión
}
// Si no es admin pero la sala_id ya está en sesión (usuario normal que ya entró a una sala), la usamos
elseif (isset($_SESSION['sala_id'])) {
    $sala_id = $_SESSION['sala_id'];
} else {
    // Si no tenemos sala_id en sesión ni en GET, redirigimos al inicio
    header("Location: inicio.php");
    exit;
}

// Por seguridad, reafirmamos que sala_id viene de la sesión (puede omitirse, está redundante)
$sala_id = $_SESSION['sala_id'];

require 'db.php';
// Incluimos la conexión a la base de datos

// Preparamos consulta para obtener nombre y creador de la sala actual
$stmt = $conn->prepare("SELECT nombre, creador_id FROM salas WHERE id = ?");
$stmt->bind_param("i", $sala_id); // Asociamos parámetro entero (id de sala)
$stmt->execute(); // Ejecutamos la consulta
$result = $stmt->get_result(); // Obtenemos el resultado
$datosSala = $result->fetch_assoc(); // Guardamos en un arreglo asociativo los datos de la sala

// Preparamos consulta para obtener la imagen de fondo del chat del usuario actual
$stmt = $conn->prepare("SELECT fondo_chat FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']); // Asignamos id usuario de la sesión
$stmt->execute();
$stmt->bind_result($fondo_chat); // Asignamos el resultado a la variable $fondo_chat
$stmt->fetch(); // Ejecutamos fetch para obtener el resultado
$stmt->close(); // Cerramos la consulta

// Si el usuario tiene un fondo personalizado para el chat, aplicamos un estilo inline para ponerlo de fondo
if ($fondo_chat) {
    echo "<style>body { background: url('$fondo_chat') no-repeat center center fixed; background-size: cover; }</style>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chat de Sala</title>
    <style>
        /* Contenedor de mensajes con borde, tamaño fijo y scroll vertical */
        #mensajes {
          border: 1px solid #ccc;
          height: 300px;
          overflow-y: scroll;
          padding: 10px;
          margin-bottom: 10px;
          background-color: rgba(255, 255, 255, 0.85);
          border-radius: 20px;
          font-size: 1rem;
          line-height: 1.4;
          max-width: 100%;
          box-sizing: border-box;
          word-wrap: break-word;
        }

        /* Área de texto para escribir mensajes */
        textarea {
            width: 100%;
            max-width: 100%;
            height: 80px;
            resize: none; /* No permitir cambiar tamaño */
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            padding: 10px;
            margin: 3px 0 10px 0;
            font-size: 1.1rem;
            box-sizing: border-box;
            border: 1.5px solid #ccc;
            font-family: inherit;
        }

        /* Estilo para el título del chat */
        h2 {
            background-color: rgba(255, 255, 255, 0.85);
            width: fit-content;
            max-width: 90vw;
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 1.5rem;
            margin: 0 auto 15px auto;
            text-align: center;
            box-sizing: border-box;
        }

        /* Estilo para el enlace de salir o similares */
        a {
            background-color: rgba(255, 255, 255, 0.85);
            width: fit-content;
            max-width: 90vw;
            border-radius: 30px;
            padding: 10px 20px;
            text-decoration: none;
            color: black;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto 0 auto;
            box-sizing: border-box;
            transition: background-color 0.3s ease;
        }

        a:hover,
        a:focus {
            background-color: rgba(230, 230, 230, 0.9);
        }

        /* Estilo para botones */
        button {
            background-color: rgba(255, 255, 255, 0.85);
            width: 100%;
            max-width: 120px;
            border-radius: 30px;
            padding: 12px;
            font-size: 1.1rem;
            margin: 10px auto 0 auto;
            cursor: pointer;
            border: none;
            box-sizing: border-box;
            display: block;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        button:hover,
        button:focus {
            background-color: rgba(230, 230, 230, 0.9);
        }

        /* Centrar el formulario */
        form#form-mensaje {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 15px;
            box-sizing: border-box;
            min-height: 100vh;
        }

        /* Responsive */
        @media (max-width: 640px) {
            h2 {
                font-size: 1.3rem;
                padding: 8px 16px;
            }

            textarea {
                font-size: 1rem;
                height: 70px;
            }

            button {
                max-width: 100%;
                font-size: 1rem;
                padding: 10px;
            }

            a {
                font-size: 0.9rem;
                padding: 8px 16px;
            }

            #mensajes {
                height: 250px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<!-- Título del chat con el nombre seguro para mostrar -->
<h2>Chat de la sala: <?= htmlspecialchars($datosSala['nombre']) ?></h2>

<!-- Contenedor donde se cargarán los mensajes -->
<div id="mensajes">Cargando mensajes...</div>

<!-- Formulario para enviar nuevos mensajes -->
<form id="form-mensaje">
    <textarea name="mensaje" id="mensaje" required placeholder="Escribe tu mensaje..."></textarea><br>
    <button type="submit">Enviar</button>
</form>

<!-- Enlace para salir de la sala -->
<a href="salir_sala.php">Salir de la sala</a>

<script>
    let autoscroll = true; 
    // Variable que controla si el scroll debe mantenerse al final automáticamente

    const mensajesDiv = document.getElementById('mensajes');

    // Escuchamos el scroll para saber si el usuario está al final o no
    mensajesDiv.addEventListener('scroll', function() {
        // Verificamos si el scroll está cerca del final (10px de margen)
        const alFinal = mensajesDiv.scrollTop + mensajesDiv.clientHeight >= mensajesDiv.scrollHeight - 10;
        autoscroll = alFinal; // Actualizamos la variable según la posición
    });

    // Función para cargar los mensajes desde el servidor
    function cargarMensajes() {
        fetch('obtener_mensajes.php')
            .then(res => res.json()) // Convertimos respuesta a JSON
            .then(data => {
                mensajesDiv.innerHTML = data.html; // Actualizamos el HTML del contenedor de mensajes

                if (autoscroll) {
                    // Si el usuario está al final, hacemos scroll automático al final para mostrar el mensaje nuevo
                    mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
                }
            });
    }

    // Evento para capturar el envío del formulario de mensaje
    document.getElementById('form-mensaje').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevenimos el comportamiento por defecto (recargar página)

        const mensaje = document.getElementById('mensaje').value.trim(); // Obtenemos y limpiamos el mensaje

        if (mensaje === '') return; // Si el mensaje está vacío, no hacemos nada

        // Enviamos el mensaje al servidor usando fetch con método POST y contenido urlencoded
        fetch('enviar_mensaje.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'mensaje=' + encodeURIComponent(mensaje)
        })
        .then(res => res.text()) // Obtenemos respuesta como texto plano
        .then(response => {
            if (response === 'OK') {
                // Si todo salió bien, limpiamos el textarea y recargamos los mensajes
                document.getElementById('mensaje').value = '';
                cargarMensajes();
            } else {
                // Si hubo error, mostramos alerta con el mensaje recibido
                alert('Error: ' + response);
            }
        });
    });

    // Permite enviar el mensaje presionando Enter sin Shift, y evitar salto de línea
    document.getElementById('mensaje').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault(); // Previene salto de línea
            document.getElementById('form-mensaje').requestSubmit(); // Envía el formulario
        }
    });

    // Cargamos mensajes por primera vez
    cargarMensajes();

    // Actualizamos los mensajes cada 1 segundo para simular chat en tiempo real
    setInterval(cargarMensajes, 1000);
</script>

</body>
</html>
