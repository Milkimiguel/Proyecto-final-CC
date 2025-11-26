<?php
    require 'seguridad_sesion.php';
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
</head>

<body>

<div class="background">
    <div class="lines"></div>
</div>

<!-- ===== ENCABEZADO ===== -->
<header class="main-header">
    <a href="blog_inicio.php" style="text-decoration: none"><h2 class="logo">Blog Gamer</h2></a>

    <nav class="header-nav">
        <a href="blog_juegos.php">Juegos</a>
        <a href="blog_agregarJuego.php">Agregar</a>
        <a href="blog_descargas.php">Descargas</a>
    </nav>

    <a href="cerrarsesion.php" class="logout-btn-header">Cerrar Sesión</a>
</header>

<!-- ===== CONTENIDO DEL CENTRO ===== -->
<main class="dashboard">

    <h1 class="dashboard-title">Panel Principal</h1>

    <div class="cards-grid">

        <a href="blog_juegos.php" class="card">
            <h3>🕹️ Ver Juegos</h3>
            <p>Consulta y gestiona todos los videojuegos de la base.</p>
        </a>

        <a href="blog_agregarJuego.php" class="card">
            <h3>➕ Agregar Juego</h3>
            <p>Sube un nuevo título y agrégalo al catálogo.</p>
        </a>

        <a href="blog_descargas.php" class="card">
            <h3>📓 Descargas</h3>
            <p>Contenido descargable, recursos y archivos del blog.</p>
        </a>
    </div>
</main>



<!-- ===== PIE DE PÁGINA ===== -->
<footer class="main-footer">
    <p>© 2025 Blog Gamer – Proyecto de Cloud Computing</p>
</footer>

</body>

</html>
