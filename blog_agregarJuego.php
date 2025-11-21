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
    <title>Agregar Juego</title>
    <!-- Fuente Comfortaa -->
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600&display=swap" rel="stylesheet">

    <!-- Enlace al CSS -->
    <link rel="stylesheet" href="styles\style.css">
</head>
<body>

<div class="background">
    <div class="lines"></div>
</div>

<div class="container">
    <form class="glass-form" action="procesarJuego.php" method="POST" enctype="multipart/form-data">
        <h2>Agregar Nuevo Juego</h2>

        <div class="input-group">
            <label>Título del Juego</label>
            <input type="text" name="titulo" required>
        </div>

        <div class="input-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" required>
        </div>

        <div class="input-group">
            <label>Archivo del Juego (ZIP)</label>
            <input type="file" name="archivo" required>
        </div>

        <button type="submit">Guardar Juego</button>

        <br><br>
        <a href="blog_inicio.php"><button type="button">Volver</button></a>
    </form>
</div>

</body>
</html>
