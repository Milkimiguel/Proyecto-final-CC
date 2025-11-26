<?php
    require 'seguridad_sesion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargas - Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles\style.css">
</head>
<body>

<div class="background">
    <div class="lines"></div>
</div>
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
        <h2>Archivos Disponibles</h2>

        <table style="width:100%; border-collapse:collapse;">
            <tr style="background: rgba(255,255,255,0.15);">
                <th style="padding:10px;">Nombre del Archivo</th>
                <th style="padding:10px;">Acción</th>
            </tr>

            <?php
            $rutaCarpeta = 'descargas/';

            if (is_dir($rutaCarpeta)) {
                // Se escanea la carpeta
                $archivos = array_diff(scandir($rutaCarpeta), array('.', '..'));
                //ORDENAMIENTO NATURAL
                natsort($archivos);

                if (count($archivos) > 0) {
                    foreach ($archivos as $archivo) {
                        echo "
                        <tr style='background: rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.1);'>
                            <td style='padding:10px;'>{$archivo}</td>
                            <td style='padding:10px;'>
                                <a href='descargas/{$archivo}' download>
                                    <button style='padding: 5px 15px; font-size: 0.9em;'>Descargar</button>
                                </a>
                            </td>
                        </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='2' style='padding:10px; text-align:center;'>La carpeta está vacía.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2' style='padding:10px; text-align:center; color: #ff6b6b;'>Error: No se encuentra la carpeta 'descargas/'.</td></tr>";
            }
            ?>
        </table>

        <br>
        <div style="text-align: center;">
            <a href="blog_inicio.php"><button>Volver</button></a>
        </div>
    </div>
</div>

</body>
</html>