<?php
    session_start();
    // Configuración para errores
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if (isset($_POST['usuario'])) {   
        try {
            $conexion = new mysqli("localhost","root","CacluloConNumeros08!","clouddb");
            $conexion->set_charset("utf8mb4");

            $query = "SELECT usuario, contraseña, rol FROM usuarios WHERE usuario = ?";
            
            $usuario = trim($_POST['usuario']);
            $password = trim($_POST['password']);
        
            if ($usuario === "" || $password === "") {
                header("Location: index.php?error=1");
                exit();
            }
        
            // Validación regex usuario...
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) { 
                header("Location: index.php?error=1");
                exit();
            }
        
            // Consulta segura (usadndo password_verify)
            $resultado = $conexion->execute_query($query, [$usuario]);
            $registro = $resultado->fetch_assoc();

            if ($registro && $registro['contraseña'] === $password) { 
                
                session_regenerate_id(true); // Seguridad anti-fixation
                $_SESSION["log"] = true;
                $_SESSION["user"] = $usuario;
                $_SESSION["rol"] = $registro['rol'];
                $_SESSION["ultimo_acceso"] = time(); 

                if (isset($_POST["recordar"])){
                    $token = bin2hex(random_bytes(16));
                    $queryUpdate = "UPDATE usuarios SET token = ? WHERE usuario = ?";
                    $conexion->execute_query($queryUpdate, [$token, $usuario]);
        
                    setcookie("token", $token, time() + (60*60*24*30), "/", "", false, true); 
                    setcookie("usuario", $usuario, time() + (60*60*24*30), "/", "", false, true);
                }
                
                header("Location: blog_inicio.php");
                exit();
            } else {
                header("Location: index.php?error=1");
                exit();
            }

        } catch (Exception $e) {
            header("Location: index.php?error=4");
            exit();
        }
    } else {
        header ("location: index.php?error=3");
    }
?>