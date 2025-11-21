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
    <title>Juegos - Blog</title>
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
    <div class="glass-form" style="width: 600px; text-align:left;">
        <h2>Lista de Juegos</h2>

        <?php
        // Aquí irían juegos desde BD. Ejemplo visual:
        $juegos = [
            ["titulo" => "Aventura Cósmica", "desc" => "Explora un universo infinito."],
            ["titulo" => "Rally Turbo", "desc" => "Carreras intensas en pistas extremas."],
            ["titulo" => "Dungeon Quest", "desc" => "Derrota monstruos y consigue tesoros."]
        ];

        foreach ($juegos as $juego) {
            echo "
                <div style='background: rgba(255,255,255,0.10); padding:15px; margin:15px 0; border-radius:12px;'>
                    <h3>{$juego['titulo']}</h3>
                    <p>{$juego['desc']}</p>
                </div>
            ";
        }
        ?>

        <br>
        <a href="blog_inicio.php"><button>Volver</button></a>
    </div>
</div>

</body>
</html>
