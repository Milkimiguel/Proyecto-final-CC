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
                header("Location: form.php?error=1");
                exit();
            }
        
            // Validación regex usuario...
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) { 
                header("Location: form.php?error=1");
                exit();
            }
        
            // Consulta segura (aquí deberías usar password_verify como vimos antes)
            // Asumiré que sigues comparando texto plano por ahora para no romper tu lógica actual
            $resultado = $conexion->execute_query($query, [$usuario]);
            $registro = $resultado->fetch_assoc();

            if ($registro && $registro['contraseña'] === $password) { // OJO: Idealmente usar password_verify
                
                // --- INICIO DE SESIÓN EXITOSO ---
                session_regenerate_id(true); // Seguridad anti-fixation
                $_SESSION["log"] = true;
                $_SESSION["user"] = $usuario;
                $_SESSION["rol"] = $registro['rol'];
                // NUEVO: Guardamos la hora exacta del login
                $_SESSION["ultimo_acceso"] = time(); 

                // --- Lógica de "Recordarme" ---
                if (isset($_POST["recordar"])){
                    $token = bin2hex(random_bytes(16));
                    $queryUpdate = "UPDATE usuarios SET token = ? WHERE usuario = ?";
                    $conexion->execute_query($queryUpdate, [$token, $usuario]);
        
                    // CORRECCIÓN: 3 minutos (60*3) es muy poco para "Recordarme". 
                    // Lo cambié a 30 días (60*60*24*30).
                    setcookie("token", $token, time() + (60*60*24*30), "/", "", false, true); 
                    setcookie("usuario", $usuario, time() + (60*60*24*30), "/", "", false, true);
                }
                
                header("Location: blog_inicio.php");
                exit();
            } else {
                header("Location: form.php?error=1");
                exit();
            }

        } catch (Exception $e) {
            header("Location: form.php?error=4");
            exit();
        }
    } else {
        header ("location: form.php?error=3");
    }
?>