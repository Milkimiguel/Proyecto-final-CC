<?php
  $conexion = mysqli_connect("localhost","root","","clouddb");
  $query = "select usuario, token from usuarios where usuario = ?;";
  
  if((isset($_COOKIE["usuario"])) && (isset($_COOKIE["token"]))){
    $resultadoquery = $conexion->execute_query($query, [$_COOKIE["usuario"]]);
    $registro = mysqli_fetch_array($resultadoquery);

    $usuarioreal = $registro["usuario"];
    $tokenreal = $registro["token"];
    if (($_COOKIE["usuario"] == $usuarioreal) && ($_COOKIE["token"] == $tokenreal)) {
        header("Location: blog_inicio.php");
        exit();
    }
    else{
      // Eliminar cookies corruptas o inválidas
      setcookie("usuario", "", time() - 3600, "/");
      setcookie("token", "", time() - 3600, "/");
      
      header("Location: form.php?error=2");
      exit();
    }
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

      <button type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
