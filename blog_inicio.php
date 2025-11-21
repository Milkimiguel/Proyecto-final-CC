<?php
    session_start();
    if (!isset($_SESSION["log"])) {
        header("Location: form.php");
        exit();
    }
    echo "Bienvenido ".htmlspecialchars($_SESSION["user"]);
?>