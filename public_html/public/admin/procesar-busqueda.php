<?php
    include_once $_SERVER["DOCUMENT_ROOT"]. "/PHP/conexion.php";
    if (isset($_GET['tabla'])) {
        $tabla = $_GET['tabla'];
        switch ($tabla) {
            case 'usuarios':
                $usuario_seleccionado = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;
                if (isset($_GET['q'])) {
                    //Verificar si exsite un o más elemento en la bdd segun lo que tenga la barra de busqueda.
                    //usando consulta preparada para evitar inyeccion SQL
                    $q = $_GET['q'];
                    if ($q === "") {
                        $resultado_usuario = $conexion->query("SELECT *, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM usuarios ORDER BY id_usuario");
                    } else {
                        $stmt = $conexion->prepare("SELECT *, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM usuarios
                        WHERE CONCAT(nombre, ' ', apellido) LIKE ? ORDER BY id_usuario");
                        $like = "%" . $q . "%";
                        $stmt->bind_param("s", $like);
                        $stmt->execute();
                        $resultado_usuario = $stmt->get_result();
                    }
                }
                while ($usuario = $resultado_usuario->fetch_assoc()) {
                    $rol_id = $usuario['id_rol'];
                    $rol = "Sin rol";

                    $stmt_rol = $conexion->prepare("SELECT nombre_rol FROM roles WHERE id_rol = ?");
                    if ($stmt_rol) {
                        $stmt_rol->bind_param("i", $rol_id);
                        $stmt_rol->execute();
                        $stmt_rol->bind_result($nombre_rol);

                        if ($stmt_rol->fetch()) {
                            $rol = $nombre_rol;
                        }

                        $stmt_rol->close();
                    }?>

                    <article class='usuario-card item usuario' data-id='<?= $usuario['id_usuario'] ?>'style='border:1px solid #000; padding:20px;'>
                        <h3><?php echo ucfirst($usuario['nombre_completo']) ?></h3>
                        <p><strong>Correo: </strong><?= $usuario['email'] ?></p>
                        <p><strong>Rol: </strong><?= $rol ?></p>
                        <div class='acciones'>
                            <a href='#' class='btn-editar'>Editar</a>
                            <a href='#' class='btn-eliminar'>Eliminar</a>
                        </div>
                    </article><?php
                }
                break;

            default:
                break;
        }
    }
?>