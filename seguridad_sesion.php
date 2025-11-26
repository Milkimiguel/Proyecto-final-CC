<?php
session_start();

// 1. Verificar si hay sesión activa básica
if (!isset($_SESSION["log"]) || $_SESSION["log"] !== true) {
    // Si no hay sesión, verificamos si hay COOKIES para restaurarla (Auto-Login)
    if (isset($_COOKIE["usuario"]) && isset($_COOKIE["token"])) {
        // Intentamos revivir la sesión con la cookie
        restaurarSesionConCookie();
    } else {
        // Si no hay sesión ni cookies, adiós
        header("Location: index.php");
        exit();
    }
}

// 2. Control de Inactividad (TIMEOUT)
$inactividad = 600; // 10 minutos

if (isset($_SESSION["ultimo_acceso"])) {
    $vida_session = time() - $_SESSION["ultimo_acceso"];
    
    if ($vida_session > $inactividad) {
        // ¡OJO AQUÍ! La sesión caducó, PERO antes de matar todo...
        // ¿Tiene el usuario cookies válidas de "Recordarme"?
        
        if (isset($_COOKIE["usuario"]) && isset($_COOKIE["token"])) {
            // Si tiene cookies, NO lo matamos. Renovamos su tiempo.
            // (Opcional: podrías volver a validar contra BD aquí para ser ultra seguro)
            $_SESSION["ultimo_acceso"] = time(); 
        } else {
            // Si NO tiene cookies (entró modo incógnito o sin marcar la casilla)
            // Entonces sí, lo expulsamos por seguridad.
            session_unset();
            session_destroy();
            header("Location: index.php?error=2"); 
            exit();
        }
    }
}

// 3. Renovar el reloj
$_SESSION["ultimo_acceso"] = time();


// --- FUNCIÓN AUXILIAR PARA RESTAURAR SESIÓN ---
function restaurarSesionConCookie() {
    // Necesitamos conectar a la BD para verificar que la cookie no sea falsa
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conexion = new mysqli("localhost", "root", "CacluloConNumeros08!", "clouddb");
        $conexion->set_charset("utf8mb4");

        $query = "SELECT usuario, token FROM usuarios WHERE usuario = ?";
        $stmt = $conexion->prepare($query);
        $usuario_cookie = $_COOKIE["usuario"];
        $stmt->bind_param("s", $usuario_cookie);
        $stmt->execute();
        $res = $stmt->get_result();
        $registro = $res->fetch_assoc();

        // Verificamos que el token de la BD coincida con la cookie
        if ($registro && hash_equals($registro["token"], $_COOKIE["token"])) {
            // ¡Cookie válida! Restauramos sesión
            session_regenerate_id(true);
            $_SESSION["log"] = true;
            $_SESSION["user"] = $registro["usuario"];
            $_SESSION["ultimo_acceso"] = time();
            return true; // Éxito
        }
    } catch (Exception $e) {
        // Si falla algo, no hacemos nada, dejaremos que el script redirija al login
    }
    return false;
}
?>