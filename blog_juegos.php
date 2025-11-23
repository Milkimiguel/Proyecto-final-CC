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
    <!-- Iconos para las etiquetas -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Enlace al CSS -->
    <link rel="stylesheet" href="styles\style.css">
</head>
<body>

<div class="background">
    <div class="lines"></div>
</div>

<div class="container">
    <h2>Lista de Juegos</h2>

    <?php
    $conexion = mysqli_connect("localhost","root","","clouddb");

    $query = "SELECT titulo, descripcion, horas_juego, genero, imagen FROM juegos";
    $resultadoquery = mysqli_query($conexion, $query);

    $juegos = [];

    while ($registro = mysqli_fetch_assoc($resultadoquery)) {
        $juego = [
            'titulo' => $registro["titulo"],
            'descripcion' => $registro["descripcion"],
            'horas_juego' => $registro["horas_juego"],
            'genero' => $registro["genero"],
            'imagen' => $registro["imagen"]
        ];
        array_push($juegos, $juego);
    }

    // Mostrar cada juego en su propio contenedor glass
    foreach ($juegos as $juego) {
        echo "
        <div class='game-container'>
            <div class='glass-form'>
                <div class='game-card'>
                    <div class='game-image'>
                        <img src='{$juego['imagen']}' alt='{$juego['titulo']}'>
                    </div>
                    <div class='game-content'>
                        <h3 class='game-title'>{$juego['titulo']}</h3>
                        <div class='game-details'>
                            <span class='game-tag hours'>
                                <i class='fas fa-clock'></i>
                                {$juego['horas_juego']} horas
                            </span>
                            <span class='game-tag genre'>
                                <i class='fas fa-tag'></i>
                                {$juego['genero']}
                            </span>
                        </div>
                        <p class='game-description'>{$juego['descripcion']}</p>
                    </div>
                </div>
            </div>
        </div>
        ";
    }
    
    mysqli_close($conexion);
    ?> 

    <div class="button-container">
        <a href="blog_inicio.php"><button>Volver</button></a>
    </div>
</div>

</body>
</html>