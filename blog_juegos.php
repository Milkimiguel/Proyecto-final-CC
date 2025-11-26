<?php
require 'seguridad_sesion.php';

$conexion = mysqli_connect("localhost", "root", "", "clouddb");
$conexion->set_charset("utf8mb4"); // Importante para tildes y ñ

// --- 1. LÓGICA PARA GUARDAR COMENTARIO (Si se envió el formulario) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_comentar'])) {
    $juego_id = $_POST['juego_id'];
    $usuario = $_SESSION['user'];
    $comentario = trim($_POST['comentario']);

    if (!empty($comentario)) {
        $stmt = $conexion->prepare("INSERT INTO comentarios (juego_id, usuario, comentario) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $juego_id, $usuario, $comentario);
        $stmt->execute();
        $stmt->close();
        // Recargar página para evitar reenvío de formulario
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Juegos - Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <h2>Lista de Juegos</h2>

    <?php
    // --- 2. OBTENER JUEGOS CON ID, AUTOR Y FECHA ---
    // Ordenamos por fecha de creación (los nuevos primero)
    $query = "SELECT id, titulo, descripcion, horas_juego, genero, imagen, autor, fecha_creacion FROM juegos ORDER BY fecha_creacion DESC";
    $resultado = mysqli_query($conexion, $query);

    while ($juego = mysqli_fetch_assoc($resultado)): 
        // Formatear la fecha bonita (Ej: 24/11/2023)
        $fecha_formato = date("d/m/Y", strtotime($juego['fecha_creacion']));
        // Si no hay autor, poner anónimo
        $autor = !empty($juego['autor']) ? $juego['autor'] : 'Anónimo';
    ?>

    <div class='game-container'>
        <div class='glass-form'>
            
            <div class='game-card'>
                <div class='game-image'>
                    <img src='<?php echo htmlspecialchars($juego['imagen']); ?>' alt='Portada'>
                </div>
                
                <div class='game-content'>
                    <h3 class='game-title'><?php echo htmlspecialchars($juego['titulo']); ?></h3>
                    
                    <div class="meta-info">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($autor); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> <?php echo $fecha_formato; ?></span>
                    </div>

                    <div class='game-details'>
                        <span class='game-tag hours'>
                            <i class='fas fa-clock'></i> <?php echo htmlspecialchars($juego['horas_juego']); ?> h
                        </span>

                        <?php 
                        // --- LÓGICA PARA SEPARAR GÉNEROS ---
                        
                        // 1. Convertimos la string "Accion, RPG" en un array ["Accion", " RPG"]
                        $lista_generos = explode(',', $juego['genero']);

                        // 2. Recorremos cada género por separado
                        foreach ($lista_generos as $gen): 
                            // Limpiamos espacios en blanco al inicio/final (ej: " RPG" -> "RPG")
                            $gen_limpio = trim($gen);
                            
                            // Solo imprimimos si no está vacío
                            if (!empty($gen_limpio)):
                        ?>
                            <span class='game-tag genre'>
                                <i class='fas fa-tag'></i> <?php echo htmlspecialchars($gen_limpio); ?>
                            </span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    
                    <p class='game-description'><?php echo nl2br(htmlspecialchars($juego['descripcion'])); ?></p>
                </div>
            </div>

            <div class="comments-section">
                <h4><i class="fas fa-comments"></i> Comentarios</h4>
                
                <div class="comment-box">
                    <?php
                    // Consultar comentarios SOLO para este juego
                    $qid = $juego['id'];
                    $q_coments = "SELECT usuario, comentario, fecha FROM comentarios WHERE juego_id = $qid ORDER BY fecha DESC";
                    $res_coments = mysqli_query($conexion, $q_coments);

                    if (mysqli_num_rows($res_coments) > 0) {
                        while ($com = mysqli_fetch_assoc($res_coments)) {
                            $fecha_com = date("d/m H:i", strtotime($com['fecha']));
                            echo "<div class='single-comment'>
                                    <div class='comment-header'>
                                        <span>" . htmlspecialchars($com['usuario']) . "</span>
                                        <span class='comment-date'>$fecha_com</span>
                                    </div>
                                    <p class='comment-body'>" . htmlspecialchars($com['comentario']) . "</p>
                                  </div>";
                        }
                    } else {
                        echo "<p style='color:#777; font-size:0.9em;'>Sé el primero en comentar.</p>";
                    }
                    ?>
                </div>

                <form method="POST" class="comment-form">
                    <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
                    <input type="text" name="comentario" class="comment-input" placeholder="Escribe un comentario..." required autocomplete="off">
                    <button type="submit" name="btn_comentar" class="btn-comment"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
            </div>
    </div>
    <?php endwhile; ?>

    <div class="flex-container" >
        <div class="button-container">
            <a href="blog_agregarJuego.php" class="btn-primary" style="text-decoration:none; display:inline-block; margin-right:10px;">Agregar Juego</a>
        </div>
        <div class="button-container">
            <a href="blog_inicio.php" class="btn-secondary" style="text-decoration:none; display:inline-block; margin-right:10px;">Volver al inicio</a>
        </div>
    </div>

</div>

</body>
</html>
<?php mysqli_close($conexion); ?>