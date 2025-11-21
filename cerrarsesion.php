<?php
    session_start();
    //procedemos a destruir la sesion
    session_destroy();
    $_SESSION = [];
    //destrui las cookies
    setcookie("usuario", "", time()-100000);
    setcookie("token", "", time()-100000);

    //Redirigir a formulario inicial
    header("Location: form.php");
    exit();
?>