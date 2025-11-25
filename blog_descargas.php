<?php
    require 'seguridad_sesion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargas - Blog</title>
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
    <h2 class="logo">Blog Gamer</h2>

    <nav class="header-nav">
        <a href="blog_juegos.php">Juegos</a>
        <a href="blog_agregarJuego.php">Agregar</a>
        <a href="blog_descargas.php">Descargas</a>
    </nav>

    <a href="cerrarsesion.php" class="logout-btn-header">Cerrar Sesión</a>
</header>

<div class="container">
    <div class="glass-form" style="margin-top:10%; width: 600px; text-align:left;">
        <h2>Descargas Disponibles</h2>

        <table style="width:100%; border-collapse:collapse;">
            <tr style="background: rgba(255,255,255,0.15);">
                <th style="padding:10px;">Documentos</th>
                <th style="padding:10px;">Descargar</th>
            </tr>

            <?php
            $archivos = [
                ["nombre" => "Códigos PHP", "file" => "Codigos_PHP.zip"],
                ["nombre" => "Ensayos AWS", "file" => "Ensayos_AWSEducate.zip"],
                ["nombre" => "Ensayo Oracle", "file" => "Ensayo_Oracle_MyLearn.zip"]
            ];

            foreach ($archivos as $a) {
                echo "
                    <tr style='background: rgba(255,255,255,0.08);'>
                        <td style='padding:10px;'>{$a['nombre']}</td>
                        <td style='padding:10px;'><a href='descargas/{$a['file']}'><button>Descargar</button></a></td>
                    </tr>
                ";
            }
            ?>
        </table>

        <br>
        <a href="blog_inicio.php"><button>Volver</button></a>
    </div>
</div>

</body>
</html>
