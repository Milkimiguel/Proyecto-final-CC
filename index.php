<?php
session_start();

// 1. SI YA ESTOY DENTRO (Por sesión activa), NO ME MUESTRES EL LOGIN
if (isset($_SESSION["log"]) && $_SESSION["log"] === true) {
    header("Location: blog_inicio.php");
    exit();
}

// 2. AUTO-LOGIN (Si no hay sesión, pero hay cookies "Recordarme")
// Verificamos que no sea un Logout para no re-loguear inmediatamente al salir
$esLogout = isset($_GET['logout']) && $_GET['logout'] == 'true';

if (!$esLogout && isset($_COOKIE["usuario"]) && isset($_COOKIE["token"])) {
    
    // Usamos Try-Catch para no revelar errores de conexión
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conexion = new mysqli("localhost", "root", "CacluloConNumeros08!", "clouddb");
        $conexion->set_charset("utf8mb4");

        $query = "SELECT usuario, token FROM usuarios WHERE usuario = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("s", $_COOKIE["usuario"]);
        $stmt->execute();
        $res = $stmt->get_result();
        $registro = $res->fetch_assoc();

        // VALIDACIÓN SEGURA (hash_equals previene ataques de tiempo)
        if ($registro && !empty($registro['token']) && hash_equals($registro["token"], $_COOKIE["token"])) {
            
            // ¡Éxito! Restauramos la sesión
            session_regenerate_id(true);
            $_SESSION["log"] = true;
            $_SESSION["user"] = $registro["usuario"];
            $_SESSION["ultimo_acceso"] = time();
            
            header("Location: blog_inicio.php");
            exit();
        } 
        
        // Si el token no coincide (ej: cookie vieja o hackeada), borramos cookies
        if ($registro) { // Solo borrar si falló la validación
             setcookie("usuario", "", time() - 3600, "/");
             setcookie("token", "", time() - 3600, "/");
        }

    } catch (Exception $e) {
        // Fallo silencioso, mostramos el formulario
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio de Sesión</title>
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  
  <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles\style.css">
</head>
<body>

  <?php 
    $mensaje = "";
    $tipo = "";

    if (isset($_GET['error'])) {
        $tipo = "error";
        switch ($_GET['error']) {
            case 1: $mensaje = "Usuario o contraseña incorrectos"; break;
            case 2: $mensaje = "Tu sesión ha expirado por inactividad"; break;
            case 3: $mensaje = "Acceso denegado. Debes iniciar sesión."; break;
            case 4: $mensaje = "Error del sistema. Intenta más tarde."; break;
        }
    }
    
    if (isset($_GET['logout'])) {
        $tipo = "success";
        $mensaje = "Has cerrado sesión correctamente.";
    }

    if ($mensaje): 
  ?>
    <div class="message <?php echo $tipo; ?>" id="msgBox"> 
        <?php echo $mensaje; ?> 
    </div>
  <?php endif; ?>

  <div class="background">
    <div class="lines"></div>
  </div>

  <div class="container">
    <form action="autenticacion.php" method="POST" class="glass-form">
      <h2>Iniciar Sesión</h2>

      <div class="input-group">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required autocomplete="username">
      </div>

      <div class="input-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>

      <div class="options">
        <label class="remember">
          <input type="checkbox" name="recordar">
          Mantener sesión iniciada
        </label>
      </div>
    
      <div style="display: flex; justify-content: flex-end;">
        <button type="submit" class="btn-primary" style="width: 100%;">Entrar</button>
      </div>
    </form>
  </div>

  <script>
    // Animación para desaparecer el mensaje
    const msgBox = document.getElementById('msgBox');
    if (msgBox) {
        setTimeout(() => {
            msgBox.style.transition = "opacity 0.5s ease";
            msgBox.style.opacity = "0";
            setTimeout(() => msgBox.remove(), 500);
        }, 4000);
    }
  </script>

</body>
</html>