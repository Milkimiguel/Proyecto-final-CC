        <?php
        $conexion = mysqli_connect("localhost","root","","clouddb");
        $query = "select count(*) from juegos;";
        $resultado_consulta = mysqli_query($conexion, $query);
        $row = mysqli_fetch_array($resultado_consulta);
        $total_juegos = $row[0];

        $juegos = [];

        for ($i = 1; $i <= $total_juegos; $i++) {

            $query = "select titulo, descripcion, horas_juego, genero, imagen from juegos where id = ?;";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("i", $i);
            $stmt->execute();
            $resultadoquery = $stmt->get_result();

            if ($registro = $resultadoquery->fetch_assoc()) {
                $juego = [
                    'titulo' => $registro["titulo"],
                    'descripcion' => $registro["descripcion"],
                    'horas_juego' => $registro["horas_juego"],
                    'genero' => $registro["genero"],
                    'imagen' => $registro["imagen"]
                ];
                array_push($juegos, $juego);
            }
        }

        foreach ($juegos as $juego) {
            echo $juego;
        }
        ?> 