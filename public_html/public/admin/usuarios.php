<?php
    // Se incluye la conexión a la base de datos.
    // $_SERVER["DOCUMENT_ROOT"] apunta a la raíz del servidor, por eso se conecta a /PHP/conexion.php.
    include_once $_SERVER["DOCUMENT_ROOT"]. "/PHP/conexion.php";

    include_once $_SERVER["DOCUMENT_ROOT"]. "/PHP/conexion.php";
 
    // Se inicia la sesión del usuario para verificar si está autenticado.
    // CAMBIO: se verifica que no haya una sesión ya iniciada, para evitar el aviso "session already active".
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
 
    // CAMBIO: control de acceso activado. Antes los header() estaban comentados, así que
    // cualquiera veía todos los usuarios y sus correos, y sin sesión el código seguía ejecutándose.
    // Ahora, si no hay sesión, se manda a iniciar sesión y se corta la ejecución con exit().
    if (!isset($_SESSION['usuario'])) {
        header("Location: /public/public/iniciar-sesion.php");
        exit();
    }
 
    // Se verifica que el usuario tenga rol 1 (administrador).
    // AJUSTAR: este archivo lee $_SESSION['rol']. A confirmar que el login guarde el rol con esa clave
    // (usar la misma en buscar-usuarios.php, donde se leía $_SESSION['usuario']['id_rol']).
    if (($_SESSION['rol'] ?? null) != 1) {
        // Bloquea el acceso a usuarios no autorizados.
        header("Location: /index.php");
        exit();
    }
 
    // Se consultan todos los usuarios ordenados por su id.
    // CAMBIOS en la consulta:
    // - Se piden solo las columnas necesarias (antes SELECT * traía también el hash de la contraseña).
    // - Se usa LEFT JOIN con roles para traer el nombre del rol en la misma consulta
    //   (antes se hacía una consulta extra por cada usuario, y si el rol no existía la página se rompía).
    // - COALESCE deja "Sin rol" si el usuario no tiene un rol que coincida.
    // No recibe datos externos, por eso query() es válido y no hace falta preparar la consulta.
    // La parte "WHERE id_rol != 1" estaba comentada, así que por ahora trae todos.
    $usuarios = $conexion->query("
        SELECT u.id_usuario, u.nombre, u.apellido, u.email,
               COALESCE(r.nombre_rol, 'Sin rol') AS rol
        FROM usuarios u
        LEFT JOIN roles r ON r.id_rol = u.id_rol
        ORDER BY u.id_usuario
    "); // WHERE u.id_rol != 1
 
    // Barra de búsqueda para buscar usuarios por nombre o correo.
    ?>
    <input type='text' name='buscar' placeholder='Buscar usuario por nombre o correo' oninput='showUser(this.value)'>
    <section style='border:1px solid #ff0000ff; padding:20px;' id='txtHint'>
        <?php
        // Se recorren todos los usuarios obtenidos de la base de datos.
        // CAMBIO: se eliminó la consulta interna a `roles`, porque el rol ya viene en $usuario['rol'] gracias al JOIN.
        while ($usuario = $usuarios->fetch_assoc()) {
            ?>
            <article class='usuario-card item usuario' data-id='<?= (int)$usuario['id_usuario'] ?>'
            style='border:1px solid #000; padding:20px;'>
                    <!-- CAMBIO: htmlspecialchars en nombre, apellido, correo y rol, para evitar que se inyecte HTML en la página. -->
                    <h3><?= htmlspecialchars(ucfirst($usuario['nombre']) . " " . ucfirst($usuario['apellido'])) ?></h3>
                    <p><strong>Correo: </strong><?= htmlspecialchars($usuario['email']) ?></p>
                    <p><strong>Rol: </strong><?= htmlspecialchars($usuario['rol']) ?></p>
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
        };.

        xmlhttp.open("GET", "procesar-busqueda.php?q=" + encodeURIComponent(str) + "&tabla=" + tabla, true); 
        // AJUSTAR: procesar-busqueda.php es el archivo de búsqueda
        // Se hace una petición GET a procesar-busqueda.php con la búsqueda y la tabla.
        // CAMBIO: encodeURIComponent evita que caracteres como & o # rompan la URL
        xmlhttp.send();
    }
 
    // ===============================================
    // REDIRIGIR A USUARIO ESPECIFICO AL HACER CLICK
    // ===============================================
    // Este bloque sirve para redirigir a una vista detallada de un usuario.
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