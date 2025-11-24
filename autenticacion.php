<?php
    session_start();
    if (isset($_POST['usuario'])) {   
        $conexion = mysqli_connect("localhost","root","","clouddb");
        $query = "select usuario, contraseña from usuarios where usuario = ?;";
    
        date_default_timezone_set("America/Mexico City");
        $usuario = trim($_POST['usuario']);
        $password = trim($_POST['password']);
    
        if ($usuario === "" || $password === "") {
            header("Location: form.php?error=1");
            exit();
        }
    
        // evita caracteres raros en el usuario permitiendo únicamente a-zA-Z0-9_
        // solo se hace con usuario pq la es el que se manda a la base de datos en la query
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) { 
            header("Location: form.php?error=1");
            exit();
        }
    
    
        //este metodo es una forma segura de realizar las consultas, evitando concatenacion
        //de peligrosas variables, toma de entrada la consulta, y los parametros a colocar
        $resultadoquery = $conexion->execute_query($query, [$usuario]);
        $registro = mysqli_fetch_array($resultadoquery);

        if (!$registro) {
            header("Location: form.php?error=1");
            exit();
        }

        $usuarioreal = $registro["usuario"];
        $passwordreal = $registro["contraseña"];
    
        if (($usuario == $usuarioreal) && ($password == $passwordreal)) {
            $_SESSION["log"] = true;
            $_SESSION["user"] = $usuario;
    
            if (isset($_POST["recordar"])){
                $token = bin2hex(random_bytes(16));
                $query = "update usuarios set token = ? where usuario = ?";
    
                $conexion->execute_query($query, [$token, $usuario]);
    
                setcookie("token", $token, time()+60*3); //Funciona como nuestra contraseña sin exponer la real
                setcookie("usuario", $usuario, time()+60*3);
            }
                header("Location:blog_inicio.php");
        } 
        else {
            header("Location: form.php?error=1"); // Error en los datos
        }
    }
    else {
        header ("location: form.php?error=3"); //Entra sin autorización
    }
?>