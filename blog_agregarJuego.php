<?php
require 'seguridad_sesion.php';

$autor_actual = isset($_SESSION['user']) ? $_SESSION['user'] : 'Anónimo';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion = new mysqli("localhost", "root", "", "clouddb");
        $conexion->set_charset("utf8mb4");

        // 1. LIMPIEZA Y VALIDACIÓN (BACKEND)
        
        // --- A. TÍTULO ---
        // strip_tags quita HTML. trim quita espacios.
        $titulo = trim(strip_tags($_POST['titulo']));
        // Regex: Letras, números, tildes, espacios y signos típicos de títulos (: - ! . ,)
        if (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\-\:\!\.\,]+$/', $titulo)) {
            throw new Exception("El título contiene caracteres inválidos.");
        }
        if (strlen($titulo) > 100) { // Límite de largo para BD
            throw new Exception("El título es demasiado largo (máx 100 caracteres).");
        }

        // --- B. DESCRIPCIÓN ---
        // Aquí NO usamos regex estricto porque una descripción lleva de todo.
        // Pero usamos strip_tags para matar cualquier intento de código <script> o HTML.
        $descripcion = trim(strip_tags($_POST['descripcion']));
        if (strlen($descripcion) > 1000) { // Evita ataques de desbordamiento
            throw new Exception("La descripción es demasiado larga (máx 1000 caracteres).");
        }

        // --- C. HORAS ---
        $horas_juego = filter_var($_POST['horas_juego'], FILTER_SANITIZE_NUMBER_INT);
        if ($horas_juego <= 0 || $horas_juego > 9999) {
            throw new Exception("Horas de juego inválidas.");
        }

        // --- D. GÉNERO (Tu validación estricta) ---
        $genero = trim(strip_tags($_POST['genero']));
        if (!preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s,]+$/', $genero)) {
            throw new Exception("El campo género contiene caracteres no permitidos.");
        }

        // --- 2. IMAGEN (Tu validación segura) ---
        // 2. PROCESAMIENTO SEGURO DE IMAGEN
        $imagen_path = "";
        
        // Verificamos si hubo error al subir (incluyendo si excede el peso del servidor)
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            
            // Detectar específicamente si se pasó del límite de php.ini o .htaccess
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_INI_SIZE) {
                throw new Exception("El archivo excede el límite del servidor (2MB).");
            }
            
            throw new Exception("Error al subir la imagen. Código: " . $_FILES['imagen']['error']);
        }

        $file = $_FILES['imagen'];

        // VALIDACIÓN DE PESO (Doble seguridad por lógica)
        // 2MB = 2097152 bytes
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("La imagen es muy pesada. Máximo permitido: 2MB.");
        }

        $imagen_path = "";
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error en la subida o no se seleccionó imagen.");
        }
        $file = $_FILES['imagen'];
        if ($file['size'] > 2 * 1024 * 1024) throw new Exception("La imagen es muy pesada (Máx 2MB).");
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        $allowed_mimes = ['image/webp', 'image/jpeg', 'image/png'];
        if (!in_array($mime_type, $allowed_mimes)) throw new Exception("Archivo inválido.");

        $ext = '.webp'; 
        if($mime_type == 'image/jpeg') $ext = '.jpg';
        if($mime_type == 'image/png') $ext = '.png';

        $upload_dir = "images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $image_name = bin2hex(random_bytes(16)) . $ext;
        $target_path = $upload_dir . $image_name;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) throw new Exception("Fallo al guardar archivo.");
        $imagen_path = "./" . $target_path;

        // --- 3. INSERCIÓN ---
        $stmt = $conexion->prepare("INSERT INTO juegos (titulo, descripcion, horas_juego, genero, imagen, autor) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $titulo, $descripcion, $horas_juego, $genero, $imagen_path, $autor_actual);
        
        if ($stmt->execute()) {
            $success = "Juego agregado con éxito.";
            $titulo = $descripcion = $horas_juego = $genero = "";
        }
        $stmt->close();
        $conexion->close();

    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Juego Seguro</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles\style.css">
</head>
<body>

<div class="background"><div class="lines"></div></div>

<div class="container">
    <form class="glass-form form-container" action="" method="POST" enctype="multipart/form-data">
        <h2 class="form-title">Agregar Nuevo Juego</h2>

        <?php if (isset($success)) echo "<div class='message success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='message error'>$error</div>"; ?>

        <div class="input-group">
            <label for="titulo">Título del Juego</label>
            <input type="text" id="titulo" name="titulo" 
                   value="<?php echo isset($titulo) ? htmlspecialchars($titulo) : ''; ?>" 
                   required 
                   placeholder="Ej: God of War: Ragnarok"
                   oninput="validarTitulo(this)"
                   pattern="[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\-\:\!\.\,]+"
                   title="Solo letras, números y signos básicos (: - ! . ,)">
        </div>

        <div class="input-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" 
                      required 
                      placeholder="Sin spoilers..." 
                      maxlength="1000"><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
            <small>Máximo 1000 caracteres.</small>
        </div>

        <div class="input-group">
            <label for="horas_juego">Horas de juego</label>
            <input type="number" id="horas_juego" name="horas_juego" 
                   min="1" max="9999" step="1" 
                   value="<?php echo isset($horas_juego) ? htmlspecialchars($horas_juego) : ''; ?>" 
                   required placeholder="Ej: 50">
        </div>

        <div class="input-group">
            <label for="genero">Género</label>
            <input type="text" id="genero" name="genero" 
                   value="<?php echo isset($genero) ? htmlspecialchars($genero) : ''; ?>" 
                   required placeholder="Ej: RPG, Aventura"
                   oninput="validarGenero(this)" 
                   pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚ\s,]+"
                   title="Solo letras y comas">
            <small>Separa con comas. Solo letras.</small>
        </div>

        <div class="input-group">
            <label for="imagen">Imagen</label>
            <div class="file-input-container">
                <input type="file" id="imagen" name="imagen" accept=".webp,.jpg,.png" required>
                <div class="file-input-label" id="fileLabel"><span>Seleccionar imagen</span></div>
            </div>
        </div>

        <button type="submit" class="btn-primary">Guardar Juego</button>
        <a href="blog_inicio.php"><button type="button" class="btn-secondary">Volver</button></a>
    </form>
</div>

<script>
    // Validar TÍTULO: Permite letras, números y signos comunes de juegos
    function validarTitulo(input) {
        var regex = /[^a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\-\:\!\.\,]/g;
        input.value = input.value.replace(regex, '');
    }

    // Validar GÉNERO: Solo letras y comas
    function validarGenero(input) {
        var regex = /[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s,]/g;
        input.value = input.value.replace(regex, '');
    }

    // Validación de IMAGEN (Peso y Tipo)
    document.getElementById('imagen').addEventListener('change', function(e) {
        const fileLabel = document.getElementById('fileLabel');
        const file = this.files[0];

        if (file) {
            const maxSize = 2 * 1024 * 1024; 

            if (file.size > maxSize) {
                alert("¡Archivo muy pesado! El límite es 2MB para no saturar el servidor.");
                this.value = ""; // Borra el archivo del input
                fileLabel.innerHTML = '<span>Haz clic para seleccionar una imagen</span>';
                fileLabel.classList.remove('has-file');
                return; // Detiene la función
            }

            // 2. VALIDAR TIPO (Doble chequeo visual)
            const validTypes = ['image/webp', 'image/jpeg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                alert("Formato no válido. Solo .webp, .jpg o .png");
                this.value = "";
                return;
            }

            // Si todo está bien:
            fileLabel.innerHTML = `<span>✅ ${file.name} (${(file.size/1024/1024).toFixed(2)} MB)</span>`;
            fileLabel.classList.add('has-file');
        }
    });
</script>

</body>
</html>