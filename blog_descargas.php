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
        <h2>Documentos Disponibles</h2>

        <table style="width:100%; border-collapse:collapse;">
            <tr style="background: rgba(255,255,255,0.15);">
                <th style="padding:10px;">Nombre del Documento</th>
                <th style="padding:10px;">Visualización</th>
            </tr>

            <?php
            $rutaCarpeta = 'descargas/';

            if (is_dir($rutaCarpeta)) {
                $archivos = array_diff(scandir($rutaCarpeta), array('.', '..'));
                
                // Ordenamos naturalmente (Tarea 1, Tarea 2... Tarea 10)
                natsort($archivos);

                if (count($archivos) > 0) {
                    foreach ($archivos as $archivo) {
                        echo "
                        <tr style='background: rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.1);'>
                            <td style='padding:10px;'>{$archivo}</td>
                            <td style='padding:10px;'>
                                <a href='descargas/{$archivo}' target='_blank'>
                                    <button style='padding: 5px 15px; font-size: 0.9em; cursor: pointer;'>Ver Documento</button>
                                </a>
                            </td>
                        </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='2' style='padding:10px; text-align:center;'>No hay documentos disponibles.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2' style='padding:10px; text-align:center; color: #ff6b6b;'>Error: Carpeta no encontrada.</td></tr>";
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