<?php
require 'seguridad_sesion.php';

function esAdministrador() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function puedeEliminarJuego($autor_juego) {
    // Admin puede eliminar cualquier juego, usuarios solo los suyos
    if (esAdministrador()) {
        return true;
    }
    return isset($_SESSION['user']) && $_SESSION['user'] === $autor_juego;
}

function puedeEliminarComentario($autor_comentario) {
    // Admin puede eliminar cualquier comentario, usuarios solo los suyos
    if (esAdministrador()) {
        return true;
    }
    return isset($_SESSION['user']) && $_SESSION['user'] === $autor_comentario;
}

function obtenerBadgeRol($autor) {
    // Solo mostrar badge si tenemos información del autor en sesión
    if (isset($_SESSION['user']) && $_SESSION['user'] === $autor) {
        if (esAdministrador()) {
            return '<span class="admin-badge">👑 Admin</span>';
        } else {
            return '<span class="user-badge">👤 Usuario</span>';
        }
    }
    return '';
}

$conexion = mysqli_connect("localhost", "root", "", "clouddb");
$conexion->set_charset("utf8mb4"); // Importante para tildes y ñ


// --- LÓGICA PARA ELIMINAR COMENTARIOS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_eliminar_comentario'])) {
    $comentario_id = $_POST['comentario_id'];
    
    // Verificar permisos antes de eliminar
    $stmt = $conexion->prepare("SELECT usuario FROM comentarios WHERE id = ?");
    $stmt->bind_param("i", $comentario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comentario = $result->fetch_assoc();
    
    if ($comentario && puedeEliminarComentario($comentario['usuario'])) {
        $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ?");
        $stmt->bind_param("i", $comentario_id);
        $stmt->execute();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- LÓGICA PARA ELIMINAR JUEGOS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_eliminar_juego'])) {
    $juego_id = $_POST['juego_id'];
    
    // Verificar permisos antes de eliminar
    $stmt = $conexion->prepare("SELECT autor FROM juegos WHERE id = ?");
    $stmt->bind_param("i", $juego_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $juego = $result->fetch_assoc();
    
    if ($juego && puedeEliminarJuego($juego['autor'])) {
        // Primero eliminar comentarios asociados (por integridad referencial)
        $stmt = $conexion->prepare("DELETE FROM comentarios WHERE juego_id = ?");
        $stmt->bind_param("i", $juego_id);
        $stmt->execute();
        
        // Luego eliminar el juego
        $stmt = $conexion->prepare("DELETE FROM juegos WHERE id = ?");
        $stmt->bind_param("i", $juego_id);
        $stmt->execute();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


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
    <style>
        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            margin-left: 5px;
            font-weight: bold;
        }
        
        .user-badge {
            background: linear-gradient(45deg, #74b9ff, #0984e3);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            margin-left: 5px;
        }
        
        .btn-eliminar {
            background: linear-gradient(45deg, #ff7675, #d63031);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .btn-eliminar:hover {
            background: linear-gradient(45deg, #d63031, #c23616);
        }
        
        .comment-actions {
            margin-top: 5px;
            text-align: right;
        }
        
        .game-actions {
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
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

    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        <?php if (esAdministrador()): ?>
            <span class="admin-badge">👑 Admin</span>
        <?php else: ?>
            <span class="user-badge">👤 Usuario</span>
        <?php endif; ?>
        <a href="cerrarsesion.php" class="logout-btn-header">Cerrar Sesión</a>
    </div>
</header>

<div class="container">
    <h2>Lista de Juegos</h2>

    <?php
    $query = "SELECT id, titulo, descripcion, horas_juego, genero, imagen, autor, fecha_creacion FROM juegos ORDER BY fecha_creacion DESC";
    $resultado = mysqli_query($conexion, $query);

    while ($juego = mysqli_fetch_assoc($resultado)): 
        $fecha_formato = date("d/m/Y", strtotime($juego['fecha_creacion']));
        $autor = !empty($juego['autor']) ? $juego['autor'] : 'Anónimo';
        $puede_eliminar_juego = puedeEliminarJuego($juego['autor']);
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
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($autor); ?>
                        <?php echo obtenerBadgeRol($juego['autor']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> <?php echo $fecha_formato; ?></span>
                    </div>

                    <div class='game-details'>
                        <span class='game-tag hours'>
                            <i class='fas fa-clock'></i> <?php echo htmlspecialchars($juego['horas_juego']); ?> h
                        </span>

                        <?php 
                        $lista_generos = explode(',', $juego['genero']);
                        foreach ($lista_generos as $gen): 
                            $gen_limpio = trim($gen);
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
                    
                    <!-- BOTÓN ELIMINAR JUEGO -->
                    <?php if ($puede_eliminar_juego): ?>
                    <div class="game-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
                            <button type="submit" name="btn_eliminar_juego" class="btn-eliminar" 
                                    onclick="return confirm('¿Estás seguro de eliminar este juego?')">
                                <i class="fas fa-trash"></i> Eliminar Juego
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="comments-section">
                <h4><i class="fas fa-comments"></i> Comentarios</h4>
                
                <div class="comment-box">
                    <?php
                    $qid = $juego['id'];
                    $q_coments = "SELECT id, usuario, comentario, fecha FROM comentarios WHERE juego_id = $qid ORDER BY fecha DESC";
                    $res_coments = mysqli_query($conexion, $q_coments);

                    if (mysqli_num_rows($res_coments) > 0) {
                        while ($com = mysqli_fetch_assoc($res_coments)) {
                            $fecha_com = date("d/m H:i", strtotime($com['fecha']));
                            $puede_eliminar_comentario = puedeEliminarComentario($com['usuario']);
                            
                            echo "<div class='single-comment'>
                                    <div class='comment-header'>
                                        <span>" . htmlspecialchars($com['usuario']) . 
                                        obtenerBadgeRol($com['usuario']) . "</span>
                                        <span class='comment-date'>$fecha_com</span>
                                    </div>
                                    <p class='comment-body'>" . htmlspecialchars($com['comentario']) . "</p>";
                            
                            // BOTÓN ELIMINAR COMENTARIO
                            if ($puede_eliminar_comentario) {
                                echo "<div class='comment-actions'>
                                        <form method='POST' style='display: inline;'>
                                            <input type='hidden' name='comentario_id' value='{$com['id']}'>
                                            <button type='submit' name='btn_eliminar_comentario' class='btn-eliminar'
                                                    onclick='return confirm(\"¿Estás seguro de eliminar este comentario?\")'>
                                                <i class='fas fa-trash'></i> Eliminar
                                            </button>
                                        </form>
                                      </div>";
                            }
                            
                            echo "</div>";
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

    <div class="flex-container">
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