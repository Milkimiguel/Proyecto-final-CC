<?php
    //Se atrapan los valores que vienen desde el formulario en LoginCSS.php
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];

    //Consultar la información del usuario de la DB (Todavía no sé como se usan así que queda pendiende xD)
    $usuarioreal = "abel";
    $passwordreal = "123";

    //Comparar los valores del formulario con los de la autenticación
    if (($usuario == $usuarioreal) && ($password == $passwordreal)) {
        //En caso de ser verdadero, se redirigirá a la página de bienvenida :)
            header("Location:blog.php");
        } else {
        //En caso de ser cualquier otro caso que no sea verdadero, se redirecciona a la página LoginCSS.php con un mensaje de error
        header("Location: form.php?error=1");
    }
?>