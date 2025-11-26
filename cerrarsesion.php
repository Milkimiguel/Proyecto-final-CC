<?php
session_start();

// 1. Limpiar el token de la base de datos para invalidar la sesión "recordada"
if (isset($_SESSION['user'])) {
    try {
        $conexion = mysqli_connect("localhost","root","CacluloConNumeros08!","clouddb");
        $query = "UPDATE usuarios SET token = NULL WHERE usuario = ?";
        $conexion->execute_query($query, [$_SESSION['user']]);
        mysqli_close($conexion);
    } catch (Exception $e) {
        // Silenciar errores de base de datos durante logout
    }
}

// 2. Destruir la sesión PHP
session_unset();
session_destroy();
$_SESSION = [];

// 3. ELIMINAR COOKIES CORRECTAMENTE - USANDO LOS MISMOS PARÁMETROS
// Los mismos parámetros que en autenticacion.php:
// time() + (60*60*24*30), "/", "", false, true

// Opción 1: Eliminar con los mismos parámetros exactos
setcookie("usuario", "", time() - 3600, "/", "", false, true);
setcookie("token", "", time() - 3600, "/", "", false, true);

// Opción 2: Eliminar con múltiples combinaciones para asegurar (defensa en profundidad)
setcookie("usuario", "", time() - 3600, "/");
setcookie("token", "", time() - 3600, "/");
setcookie("usuario", "", time() - 3600);
setcookie("token", "", time() - 3600);

// 4. Forzar la escritura de las cabeceras
if (headers_sent()) {
    echo "<script>document.cookie = 'usuario=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';</script>";
    echo "<script>document.cookie = 'token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';</script>";
}

// 5. Redirigir con parámetro para evitar bucles
header("Location: index.php?logout=success");
exit();
?>