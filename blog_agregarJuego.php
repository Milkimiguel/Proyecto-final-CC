<?php
session_start();
if (!isset($_SESSION["log"])) {
    header("Location: form.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conexion = mysqli_connect("localhost", "root", "", "clouddb");

    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $horas_juego = mysqli_real_escape_string($conexion, $_POST['horas_juego']);
    $genero = mysqli_real_escape_string($conexion, $_POST['genero']);
    
    // Procesar la imagen
    $imagen_path = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        
        // Verificar que sea archivo .webp
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_extension !== 'webp') {
            $error = "Solo se permiten archivos .webp";
        } else {
            // Crear directorio images si no existe
            $upload_dir = "images/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generar nombre único para la imagen
            $image_name = uniqid() . '_' . basename($file['name']);
            $target_path = $upload_dir . $image_name;
            
            // Mover el archivo
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $imagen_path = "./" . $target_path;
            } else {
                $error = "Error al subir la imagen";
            }
        }
    }
    
    // Si no hay error, insertar en la base de datos
    if (!isset($error) && !empty($imagen_path)) {
        $stmt = $conexion->prepare("INSERT INTO juegos (titulo, descripcion, horas_juego, genero, imagen) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $titulo, $descripcion, $horas_juego, $genero, $imagen_path);
        
        if ($stmt->execute()) {
            $success = "¡Juego agregado correctamente!";
            // Limpiar los campos del formulario
            $titulo = $descripcion = $horas_juego = $genero = "";
        } else {
            $error = "Error al guardar en la base de datos: " . $stmt->error;
        }
        $stmt->close();
    }
    
    mysqli_close($conexion);
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
    <form class="glass-form form-container" action="" method="POST" enctype="multipart/form-data">
        <h2 class="form-title">Agregar Nuevo Juego</h2>

        <?php if (isset($success)): ?>
            <div class="message success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="input-group">
            <label for="titulo">Título del Juego</label>
            <input type="text" id="titulo" name="titulo" value="<?php echo isset($titulo) ? htmlspecialchars($titulo) : ''; ?>" required placeholder="Ingresa el título del juego">
        </div>

        <div class="input-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" required placeholder="Describe el juego..."><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
        </div>

        <div class="input-group">
            <label for="horas_juego">Horas de juego</label>
            <input type="number" id="horas_juego" name="horas_juego" min="1" step="1" value="<?php echo isset($horas_juego) ? htmlspecialchars($horas_juego) : ''; ?>" required placeholder="Ej: 50">
        </div>

        <div class="input-group">
            <label for="genero">Género</label>
            <input type="text" id="genero" name="genero" value="<?php echo isset($genero) ? htmlspecialchars($genero) : ''; ?>" required placeholder="Ej: Aventura, RPG, FPS...">
        </div>

        <div class="input-group">
            <label for="imagen">Imagen del Juego</label>
            <div class="file-input-container">
                <input type="file" id="imagen" name="imagen" accept=".webp" required>
                <div class="file-input-label" id="fileLabel">
                    <span>Haz clic para seleccionar una imagen .webp</span>
                </div>
            </div>
            <small>Solo se permiten archivos en formato .webp</small>
        </div>

        <button type="submit" class="btn-primary">Guardar Juego</button>

        <a href="blog_inicio.php">
            <button type="button" class="btn-secondary">Volver al Inicio</button>
        </a>
        
        <p class="form-note">Todos los campos son obligatorios</p>
    </form>
</div>

<script>
// JavaScript para mejorar la experiencia del input de archivo
document.getElementById('imagen').addEventListener('change', function(e) {
    const fileLabel = document.getElementById('fileLabel');
    if (this.files.length > 0) {
        const fileName = this.files[0].name;
        fileLabel.innerHTML = `<span>Archivo seleccionado: ${fileName}</span>`;
        fileLabel.classList.add('has-file');
    } else {
        fileLabel.innerHTML = '<span>Haz clic para seleccionar una imagen .webp</span>';
        fileLabel.classList.remove('has-file');
    }
});
</script>

</body>
</html>