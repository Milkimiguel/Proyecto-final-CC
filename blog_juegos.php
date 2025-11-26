<?php
require 'seguridad_sesion.php';

// --- FUNCIONES DE PERMISOS ---

function esAdministrador() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function puedeEditarJuego($autor_juego) {
    // REGLA DE ORO: Solo edita el dueño del post.
    // Ni siquiera el admin puede editar posts de otros.
    return isset($_SESSION['user']) && $_SESSION['user'] === $autor_juego;
}

function puedeEliminarJuego($autor_juego) {
    // Admin borra todo, usuario solo lo suyo.
    if (esAdministrador()) return true;
    return isset($_SESSION['user']) && $_SESSION['user'] === $autor_juego;
}

function puedeEliminarComentario($autor_comentario) {
    if (esAdministrador()) return true;
    return isset($_SESSION['user']) && $_SESSION['user'] === $autor_comentario;
}

function obtenerBadgeRol($autor) {
    if (isset($_SESSION['user']) && $_SESSION['user'] === $autor) {
        return esAdministrador() ? '<span class="admin-badge">👑 Admin</span>' : '<span class="user-badge">👤 Usuario</span>';
    }
    return '';
}

$conexion = mysqli_connect("localhost", "root", "", "clouddb");
$conexion->set_charset("utf8mb4"); 


// 1. GUARDAR EDICIÓN DEL JUEGO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_guardar_edicion'])) {
    $juego_id = $_POST['juego_id'];
    

    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $horas_juego = trim($_POST['horas_juego']); // Aseguramos que sea string limpio
    $genero = trim($_POST['genero']);

    $patron_titulo = "/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.,\:!?]+$/u";
    
    // 2. Género: Solo letras, tildes y comas (Ej: Acción, RPG, Aventura)
    $patron_genero = "/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s,]+$/u";
    
    // 3. Horas: Solo números enteros
    $patron_horas = "/^[0-9]+$/";

    // 4. Descripción: Amplio rango de texto, puntuación, paréntesis y saltos de línea (\n\r)
    $patron_descripcion = "/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.,\:!?\(\)\[\]\"'\n\r]+$/u";

    $stmt = $conexion->prepare("SELECT autor FROM juegos WHERE id = ?");
    $stmt->bind_param("i", $juego_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $juego_db = $result->fetch_assoc();
    $stmt->close();

    // --- VALIDACIÓN DE DATOS ---
    $errores = [];

    // Validamos cada campo contra su Regex
    if (!preg_match($patron_titulo, $titulo)) {
        $errores[] = "El título contiene caracteres no permitidos.";
    }
    if (!preg_match($patron_genero, $genero)) {
        $errores[] = "El género solo debe contener letras y comas.";
    }
    if (!preg_match($patron_horas, $horas_juego)) {
        $errores[] = "Las horas deben ser un número entero.";
    }
    if (!preg_match($patron_descripcion, $descripcion)) {
        $errores[] = "La descripción contiene caracteres inválidos (evita usar < >).";
    }

    // SI TODO ESTÁ CORRECTO Y TIENE PERMISO
    if (empty($errores) && $juego_db && puedeEditarJuego($juego_db['autor'])) {
        
        $stmt_update = $conexion->prepare("UPDATE juegos SET titulo = ?, descripcion = ?, horas_juego = ?, genero = ? WHERE id = ?");
        $stmt_update->bind_param("ssisi", $titulo, $descripcion, $horas_juego, $genero, $juego_id);
        
        if ($stmt_update->execute()) {
            $stmt_update->close();
            // Éxito: recargamos limpio
            header("Location: " . $_SERVER['PHP_SELF']); 
            exit();
        } else {
            // Error SQL
            echo "<script>alert('Error en la base de datos al actualizar.');</script>";
        }

    } else {
        if (!empty($errores)) {
            $msg = implode("\\n", $errores); // Unimos errores para el alert
            echo "<script>alert('Error de validación:\\n$msg'); window.history.back();</script>";
            exit();
        } else {
             // Fallo de permisos
            echo "<script>alert('No tienes permiso para editar esto.'); window.location.href='blog_juegos.php';</script>";
            exit();
        }
    }
}

// 2. ELIMINAR COMENTARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_eliminar_comentario'])) {
    $comentario_id = $_POST['comentario_id'];
    $stmt = $conexion->prepare("SELECT usuario FROM comentarios WHERE id = ?");
    $stmt->bind_param("i", $comentario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $com = $res->fetch_assoc();
    
    if ($com && puedeEliminarComentario($com['usuario'])) {
        $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ?");
        $stmt->bind_param("i", $comentario_id);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 3. ELIMINAR JUEGO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_eliminar_juego'])) {
    $juego_id = $_POST['juego_id'];
    $stmt = $conexion->prepare("SELECT autor FROM juegos WHERE id = ?");
    $stmt->bind_param("i", $juego_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $juego_db = $res->fetch_assoc();
    
    if ($juego_db && puedeEliminarJuego($juego_db['autor'])) {
        $conexion->query("DELETE FROM comentarios WHERE juego_id = $juego_id");
        $stmt = $conexion->prepare("DELETE FROM juegos WHERE id = ?");
        $stmt->bind_param("i", $juego_id);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 4. GUARDAR NUEVO COMENTARIO
// --- 1. LÓGICA PARA GUARDAR COMENTARIO (BLINDADA) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_comentar'])) {
    $juego_id = $_POST['juego_id'];
    $usuario = $_SESSION['user'];
    $comentario = trim($_POST['comentario']);

    // BLOQUEA < y > para evitar inyecciones HTML/JS.
    $patron_comentario = "/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.,\:!?\(\)\[\]\"'\n\r]+$/u";

    // Validaciones
    if (empty($comentario)) {
        echo "<script>alert('El comentario no puede estar vacío.'); window.history.back();</script>";
        exit();
    }

    if (!preg_match($patron_comentario, $comentario)) {
        echo "<script>alert('El comentario contiene caracteres inválidos (< >).'); window.history.back();</script>";
        exit();
    }

    // Si pasa la validación, guardamos
    $stmt = $conexion->prepare("INSERT INTO comentarios (juego_id, usuario, comentario) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $juego_id, $usuario, $comentario);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit();
    } else {
        echo "<script>alert('Error al guardar el comentario.');</script>";
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

<header class="main-header">
    <h2 class="logo">Blog Gamer</h2>
    <nav class="header-nav">
        <a href="blog_juegos.php">Juegos</a>
        <a href="blog_agregarJuego.php">Agregar</a>
        <a href="blog_descargas.php">Descargas</a>
    </nav>
    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        <?php echo esAdministrador() ? '<span class="admin-badge">👑 Admin</span>' : '<span class="user-badge">👤 Usuario</span>'; ?>
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
        
        // Permisos para botones
        $puede_eliminar = puedeEliminarJuego($juego['autor']);
        $puede_editar = puedeEditarJuego($juego['autor']);

        // Variable para saber si estamos editando ESTE juego específico
        $editando = isset($_GET['editar']) && $_GET['editar'] == $juego['id'];
    ?>

    <div class='game-container'> 
        <div class='glass-form'>

            <?php 
            // ==========================================
            // VISTA 1: MODO EDICIÓN (Solo si toca editar)
            // ==========================================
            if ($editando && $puede_editar): 
            ?>
                
                <form method="POST" class='form-editar'>
    <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
    
    <h3><i class="fas fa-pen-fancy"></i> Editando Publicación</h3>
    
    <label>Título del Juego</label>
    <input type="text" name="titulo" 
        value="<?php echo htmlspecialchars($juego['titulo']); ?>" 
        required
        oninput="this.value = this.value.replace(/[^a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.,\:!?]/g, '')">

    <label>Descripción</label>
    <textarea name="descripcion" required
            oninput="this.value = this.value.replace(/[^a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.,\:!?\(\)\[\]&quot;'\n\r]/g, '')"><?php echo htmlspecialchars($juego['descripcion']); ?></textarea>
    
    <label>Horas de Juego</label>
    <input type="number" name="horas_juego" 
        value="<?php echo htmlspecialchars($juego['horas_juego']); ?>" 
        required
        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    
    <label>Género (separado por comas)</label>
    <input type="text" name="genero" 
        value="<?php echo htmlspecialchars($juego['genero']); ?>" 
        required
        oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s,]/g, '')">

    <div class="actions-editar">
        <button type="submit" name="btn_guardar_edicion" class="btn-save">
            <i class="fas fa-save"></i> Guardar
        </button>
        <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="btn-cancel">
            <i class="fas fa-times"></i> Cancelar
        </a>
    </div>
</form>

            <?php 
            // ==========================================
            // VISTA 2: MODO VISUALIZACIÓN NORMAL
            // ==========================================
            else: 
            ?>
                
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
                                if (!empty(trim($gen))): ?>
                                <span class='game-tag genre'>
                                    <i class='fas fa-tag'></i> <?php echo htmlspecialchars(trim($gen)); ?>
                                </span>
                            <?php endif; endforeach; ?>
                        </div>
                        
                        <p class='game-description'><?php echo nl2br(htmlspecialchars($juego['descripcion'])); ?></p>

                        <?php if ($puede_eliminar || $puede_editar): ?>
                        <div class="game-actions">
                            <?php if ($puede_editar): ?>
                                <a href="?editar=<?php echo $juego['id']; ?>" class="btn-editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            <?php endif; ?>

                            <?php if ($puede_eliminar): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
                                    <button type="submit" name="btn_eliminar_juego" class="btn-eliminar" 
                                            onclick="return confirm('¿Seguro que quieres borrar este juego?')">
                                        <i class="fas fa-trash"></i> Borrar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="comments-section">
                    <h4><i class="fas fa-comments"></i> Comentarios</h4>
                    <div class="comment-box">
                        <?php
                        $qid = $juego['id'];
                        $res_coments = mysqli_query($conexion, "SELECT * FROM comentarios WHERE juego_id = $qid ORDER BY fecha DESC");
                        
                        if (mysqli_num_rows($res_coments) > 0) {
                            while ($com = mysqli_fetch_assoc($res_coments)) {
                                $fecha_com = date("d/m H:i", strtotime($com['fecha']));
                                echo "<div class='single-comment'>
                                        <div class='comment-header'>
                                            <span>" . htmlspecialchars($com['usuario']) . obtenerBadgeRol($com['usuario']) . "</span>
                                            <span class='comment-date'>$fecha_com</span>
                                        </div>
                                        <p class='comment-body'>" . htmlspecialchars($com['comentario']) . "</p>";
                                
                                if (puedeEliminarComentario($com['usuario'])) {
                                    echo "<form method='POST' style='text-align:right; margin-top:5px;'>
                                            <input type='hidden' name='comentario_id' value='{$com['id']}'>
                                            <button type='submit' name='btn_eliminar_comentario' class='btn-eliminar' style='padding:2px 8px; font-size:0.8em;' onclick='return confirm(\"¿Borrar comentario?\")'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                        </form>";
                                }
                                echo "</div>";
                            }
                        } else {
                            echo "<p style='color:#ccc; font-style:italic;'>Sé el primero en comentar.</p>";
                        }
                        ?>
                    </div>
                    <form method="POST" class="comment-form">
                        <input type="hidden" name="juego_id" value="<?php echo $juego['id']; ?>">
    
                        <input type="text" 
                            name="comentario" 
                            class="comment-input" 
                            placeholder="Escribe un comentario..." 
                            required 
                            autocomplete="off"
                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.,\:!?\(\)\[\]&quot;']/g, '')">
                            
                        <button type="submit" name="btn_comentar" class="btn-comment">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

            <?php endif; ?>
        
        </div>
    </div>
    <?php endwhile; ?>

</div>
</body>
</html>
<?php mysqli_close($conexion); ?>