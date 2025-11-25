<?php
session_start();

// 1. Verificar si está logueado
if (!isset($_SESSION["log"]) || $_SESSION["log"] !== true) {
    header("Location: form.php");
    exit();
}

// 2. Control de Inactividad (TIMEOUT)
// Definir tiempo máximo de inactividad (ej: 10 minutos = 600 segundos)
$inactividad = 600; 

if (isset($_SESSION["ultimo_acceso"])) {
    $vida_session = time() - $_SESSION["ultimo_acceso"];
    
    if ($vida_session > $inactividad) {
        // Si pasó el tiempo, destruimos todo y redirigimos
        session_unset();
        session_destroy();
        
        // También borramos cookies si existían, para obligar a reloguearse
        if (isset($_COOKIE['usuario'])) {
            setcookie("usuario", "", time()-1000, "/");
            setcookie("token", "", time()-1000, "/");
        }

        header("Location: form.php?error=2"); // Error 2: Sesión expirada
        exit();
    }
}

// 3. Renovar el tiempo
// Si el usuario sigue activo (recarga página), actualizamos su reloj
$_SESSION["ultimo_acceso"] = time();
?>