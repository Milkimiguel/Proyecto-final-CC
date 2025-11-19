<?php
    //se inicia la sesion
    session_start();
    //se inicia la conexion
    $conexion = mysqli_connect("localhost","root","","clouddb");
    //el signo ? es un marcador de posicion que mas adelante se usara
    $query = "select usuario, contraseña from usuarios where usuario = ?;";

    //si ya existian cookies
    //verifica si existen las cookies
    if((isset($_COOKIE["usuario"])) && (isset($_COOKIE["password"]))){
        //este metodo es una forma segura de realizar las consultas, evitando concatenacion
        //de peligrosas variables, toma de entrada la consulta, y los parametros a colocar
        $resultadoquery = $conexion->execute_query($query, [$_COOKIE["usuario"]]);
        $registro = mysqli_fetch_array($resultadoquery);
        //comparar las cookies con la base de datos
        $usuarioreal = $registro["usuario"];
        $passwordreal = $registro["contraseña"];
        //si las cookies existen en la base de datos, redireciona a bienvenida
        if (($_COOKIE["usuario"] == $usuarioreal) && ($_COOKIE["password"] == $passwordreal)) {
            //ingreso exitoso
            //redirecciono
            header("Location: blog.php");
            exit();
        }
        // si las cookies no existen en la base de datos, manda un error y te vuelve a redireccionar a autentica
        else{
            header("Location: formautentica.php?error=2");
        exit();
        }
    }

    date_default_timezone_set("America/Mexico City");
    //Se atrapan los valores que vienen desde el formulario en LoginCSS.php
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    //este metodo es una forma segura de realizar las consultas, evitando concatenacion
    //de peligrosas variables, toma de entrada la consulta, y los parametros a colocar
    $resultadoquery = $conexion->execute_query($query, [$usuario]);
    $registro = mysqli_fetch_array($resultadoquery);
    //Consultar la información del usuario de la DB (Todavía no sé como se usan así que queda pendiende xD)
    $usuarioreal = $registro["usuario"];
    $passwordreal = $registro["contraseña"];

    //Comparar los valores del formulario con los de la autenticación
    if (($usuario == $usuarioreal) && ($password == $passwordreal)) {
        $_SESSION["log"] = true;
        $_SESSION["user"] = $usuario;
        //no se agrega la contrasenia a la session por seguridad, y evitar ataques de Hijacking
        //valida si el usuario marco la checkbox remember para crear las cookies
        if (isset($_POST["recordar"])){
            setcookie("usuario", $usuario, time()+60*60*24);
            setcookie("password", $password, time()+60*60*24);
        }
        //En caso de ser verdadero, se redirigirá a la página de bienvenida :)
            header("Location:blog.php");
        } else {
        //En caso de ser cualquier otro caso que no sea verdadero, se redirecciona a la página LoginCSS.php con un mensaje de error
        header("Location: form.php?error=1");
    }
?>