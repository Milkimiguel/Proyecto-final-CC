<?php
session_start();
if (!isset($_SESSION["log"])) {
    header("Location: form.php");
    exit();
}

// Asegurarnos de que tenemos el usuario en sesión
$autor_actual = isset($_SESSION['user']) ? $_SESSION['user'] : 'Anónimo';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Reporte de errores interno (no mostrar al usuario)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion = new mysqli("localhost", "root", "", "clouddb");
        $conexion->set_charset("utf8mb4");

        // 1. SANITIZACIÓN DE TEXTO (Anti-XSS en entrada)
        // strip_tags evita que metan HTML. Si quieres permitir negritas, usa strip_tags($var, '<b>')
        $titulo = trim(strip_tags($_POST['titulo']));
        $descripcion = trim(strip_tags($_POST['descripcion']));
        // filter_var asegura que sea un número entero
        $horas_juego = filter_var($_POST['horas_juego'], FILTER_SANITIZE_NUMBER_INT);
        $genero = trim(strip_tags($_POST['genero']));
        
        // Autor desde sesión (ya es seguro porque viene del servidor)
        $autor_actual = $_SESSION['user'];

        // 2. PROCESAMIENTO SEGURO DE IMAGEN
        $imagen_path = "";
        
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error en la subida o no se seleccionó imagen.");
        }

        $file = $_FILES['imagen'];

        // VALIDACIÓN A: Tamaño máximo (Ej: 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("La imagen es muy pesada (Máx 2MB).");
        }

        // VALIDACIÓN B: Verificar que sea realmente una imagen (Magic Bytes)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        
        // Lista blanca estricta de tipos permitidos
        $allowed_mimes = ['image/webp', 'image/jpeg', 'image/png'];
        
        if (!in_array($mime_type, $allowed_mimes)) {
            throw new Exception("Archivo inválido. Detectado: $mime_type");
        }

        // VALIDACIÓN C: Renombrado aleatorio (Destruye nombres maliciosos)
        // Forzamos la extensión .webp aunque suban otra cosa (si tu lógica lo requiere)
        // Ojo: Si permites jpg/png, usa la extensión real del mime type.
        $ext = '.webp'; 
        if($mime_type == 'image/jpeg') $ext = '.jpg';
        if($mime_type == 'image/png') $ext = '.png';

        $upload_dir = "images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        // Nombre único criptográfico
        $image_name = bin2hex(random_bytes(16)) . $ext;
        $target_path = $upload_dir . $image_name;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new Exception("Fallo al guardar el archivo.");
        }
        
        $imagen_path = "./" . $target_path;

        // 3. INSERCIÓN SEGURA (Prepared Statements - Anti-SQLi)
        $stmt = $conexion->prepare("INSERT INTO juegos (titulo, descripcion, horas_juego, genero, imagen, autor) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $titulo, $descripcion, $horas_juego, $genero, $imagen_path, $autor_actual);
        
        if ($stmt->execute()) {
            $success = "Juego agregado con éxito.";
        }
        
        $stmt->close();
        $conexion->close();

    } catch (Exception $e) {
        // En producción usa error_log y muestra mensaje genérico
        $error = "Error de seguridad o datos: " . $e->getMessage();
    }
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