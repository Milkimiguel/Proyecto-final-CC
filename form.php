<?php
// Verificar si viene de un logout para evitar redirección
$esLogout = isset($_GET['logout']) && $_GET['logout'] == 'true';

if(!$esLogout && (isset($_COOKIE["usuario"]) && isset($_COOKIE["token"]))){
  $conexion = mysqli_connect("localhost","root","CacluloConNumeros08!","clouddb");
  $query = "select usuario, token from usuarios where usuario = ?;";
  
  $resultadoquery = $conexion->execute_query($query, [$_COOKIE["usuario"]]);
  $registro = mysqli_fetch_array($resultadoquery);

  if($registro){
    $usuarioreal = $registro["usuario"];
    $tokenreal = $registro["token"];
    if (($_COOKIE["usuario"] == $usuarioreal) && ($_COOKIE["token"] == $tokenreal)) {
        header("Location: blog_inicio.php");
        exit();
    }
  }
  
  // Si llegamos aquí, las cookies son inválidas
  setcookie("usuario", "", time() - 3600, "/");
  setcookie("token", "", time() - 3600, "/");
}
?>

<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio de Sesión</title>

  <!-- Fuente Comfortaa -->
  <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;600&display=swap" rel="stylesheet">

  <!-- Enlace al CSS -->
  <link rel="stylesheet" href="styles\style.css">
</head>
<body>

  <?php // Errores atrapados de redirecciones
    if (isset($_GET['error']) && $_GET['error'] == 1): 
    echo '<div class="message error" id="errorBox"> Usuario o contraseña incorrectos </div>';
    endif; 
    
    if (isset($_GET['error']) && $_GET['error'] == 2): 
    echo '<div class="message error" id="errorBox"> Error sesión expirada u obsoleta </div>';
    endif;

    if (isset($_GET['error']) && $_GET['error'] == 3): 
    echo '<div class="message error" id="errorBox"> Qué haces intentando entrar ahí sin permiso? </div>';
    endif;

    // Mensaje de logout exitoso
    if (isset($_GET['logout']) && $_GET['logout'] == 'true'): 
    echo '<div class="message success" id="successBox"> Sesión cerrada correctamente </div>';
    endif;
  ?>

  <div class="background">
    <div class="lines"></div>
  </div>

  <div class="container">
    <form action="autenticacion.php" method="POST" class="glass-form">
      <h2>Iniciar Sesión</h2>

      <div class="input-group">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>
      </div>

      <div class="input-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="options">
        <label class="remember">
          <input type="checkbox" name="recordar">
          Mantener sesión iniciada
        </label>
      </div>
    
      <div style="display: flex; justify-content: flex-end;">
        <button type="submit">Entrar</button>
      </div>
    </form>
  </div>
</body>
</html>