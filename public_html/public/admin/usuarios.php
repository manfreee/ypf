<?php
    // Se incluye la conexión a la base de datos.
    // $_SERVER["DOCUMENT_ROOT"] apunta a la raíz del servidor, por eso se conecta a /PHP/conexion.php.
    include_once $_SERVER["DOCUMENT_ROOT"]. "/PHP/conexion.php";

    // Se inicia la sesión del usuario para verificar si está autenticado.
    session_start();

    // Si no existe la sesión del usuario, se ejecuta este bloque.
    if(!isset($_SESSION['usuario'])){
        // Sirve para mandar al usuario a iniciar sesión si no está logueado.
        // header("location: ../iniciar-sesion.php");
    }
    else{
        // Si la sesión existe, se verifica que el usuario tenga rol 1.
        // En este caso, rol 1 probablemente significa administrador.
        if($_SESSION['rol'] != 1){
            // Sirve para bloquear acceso a usuarios no autorizados.
            // header("location: ../../../index.html");
        }
    }

    // Se consulta a la base de datos todos los usuarios ordenados por su id.
    // La parte "WHERE id_rol != 1" estaba comentada, así que por ahora trae todos.
    $usuarios = $conexion->query("SELECT * FROM usuarios ORDER BY id_usuario");// WHERE id_rol != 1

    // Barra de búsqueda para buscar usuarios por nombre o correo.
    ?>
    <input type='text' name='buscar' placeholder='Buscar usuario por nombre o correo' oninput='showUser(this.value)'>
    <section style='border:1px solid #ff0000ff; padding:20px;' id='txtHint'>
        <?php
        // Se recorren todos los usuarios obtenidos de la base de datos.
        while ($usuario = $usuarios->fetch_assoc()) {
            // Se consulta el nombre del rol según el id del rol del usuario.
            $rol = $conexion->query("SELECT nombre_rol FROM roles WHERE id_rol = ".$usuario['id_rol']);

            // Se obtiene la fila resultante y luego el nombre del rol.
            $rol = $rol->fetch_assoc()["nombre_rol"];
            ?>
            <article class='usuario-card item usuario' data-id='<?= $usuario['id_usuario'] ?>'
            style='border:1px solid #000; padding:20px;'>
                    <h3><?php echo ucfirst($usuario['nombre']) . " " . ucfirst($usuario['apellido'])?></h3>
                    <p><strong>Correo: </strong><?= $usuario['email'] ?></p>
                    <p><strong>Rol: </strong><?= $rol ?></p>
                    <div class='acciones'>
                        <a href='#' class='btn-editar'>Editar</a>
                        <a href='#' class='btn-eliminar'>Eliminar</a>
                    </div>
                </article><?php
        }?>
    </section>
<!-- JS -->
<script>
    // Función que se ejecuta cada vez que el usuario escribe en la barra de búsqueda.
    function showUser(str) {
        var tabla = 'usuarios'; //nombre de la tabla en la que se ejecutará la consulta
        var xmlhttp = new XMLHttpRequest();

        // Cuando la petición termina correctamente, reemplaza el contenido del section con la respuesta.
        xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };

        // Se hace una petición GET a procesar-busqueda.php con la búsqueda y la tabla.
        xmlhttp.open("GET", "procesar-busqueda.php?q=" + str + "&tabla=" + tabla, true);
        xmlhttp.send();
    }

    // ===============================================
    // REDIRIGIR A USUARIO ESPECIFICO AL HACER CLICK
    // ===============================================
    // Este bloque está comentado, pero sirve para redirigir a una vista detallada de un usuario.
    //     document.querySelectorAll('.item.usuario').forEach(usuario => {
    //     usuario.addEventListener('click', () => {
    //         const id = usuario.getAttribute('data-id');
    //         usuarioEspecifico(id);
    //     });
    // });

    //funcion para redirigir a noticia especifica
    function usuarioEspecifico(id) {
        window.location.href = "ver-usuario.php?id=" + id;
    }

</script>