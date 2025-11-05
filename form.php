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

  <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
    <div class="error-message" id="errorBox">
      Usuario o contraseña incorrectos
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
  <script>
    // Mostrar animación del mensaje de error
    const errorBox = document.getElementById('errorBox');
    if (errorBox) {
      setTimeout(() => {
        errorBox.classList.add('show');
      }, 100); // pequeña demora para animación

      // Ocultar automáticamente después de 3.5 segundos
      setTimeout(() => {
        errorBox.classList.remove('show');
      }, 3500);
    }
  </script>

</body>
</html>
