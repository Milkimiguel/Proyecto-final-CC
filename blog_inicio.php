<?php
    session_start();
    if (!isset($_SESSION["log"])) {
        header("Location: form.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Blog de Juegos</title>
    <!-- Fuente Comfortaa -->
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600&display=swap" rel="stylesheet">

    <!-- Enlace al CSS -->
    <link rel="stylesheet" href="styles\style.css">

    <style>
        /* Botón de cerrar sesión estilo glass */
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;

            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 10px 16px;
            border-radius: 12px;
            cursor: pointer;

            color: #fff;
            font-weight: bold;
            font-size: 0.9em;

            backdrop-filter: blur(10px);
            transition: 0.25s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 80, 80, 0.25);
            border-color: rgba(255, 80, 80, 0.5);
            box-shadow: 0 0 10px rgba(255, 50, 50, 0.4);
        }
    </style>
</head>

<body>

<div class="background">
    <div class="lines"></div>
</div>

<!-- BOTÓN CERRAR SESIÓN -->
<a href="cerrarsesion.php">
    <div class="logout-btn">Cerrar Sesión</div>
</a>

<div class="container">
    <div class="glass-form" style="width: 450px;">
        <h2>Bienvenido al Blog de Videojuegos</h2>
        <p>Explora juegos, sube nuevos proyectos y descarga contenido exclusivo.</p>

        <br><br>

        <a href="blog_juegos.php">
            <button>Ver Juegos</button>
        </a>
        <br><br>

        <a href="blog_agregarJuego.php">
            <button>Agregar Juego</button>
        </a>
        <br><br>

        <a href="blog_descargas.php">
            <button>Descargas</button>
        </a>
    </div>
</div>

</body>
</html>
