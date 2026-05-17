<?php
    include_once $_SERVER["DOCUMENT_ROOT"]. "/PHP/conexion.php";
    session_start();
    if(!isset($_SESSION['usuario'])){
        // header("location: ../iniciar-sesion.php");
    }
    else{
        if($_SESSION['rol'] != 1){
            // header("location: ../../../index.html");
        }
    }
    $usuarios = $conexion->query("SELECT * FROM usuarios ORDER BY id_usuario");// WHERE id_rol != 1
    //barra de busqueda
    ?>
    <input type='text' name='buscar' placeholder='Buscar usuario por nombre o correo' oninput='showUser(this.value)'>
    <section style='border:1px solid #ff0000ff; padding:20px;' id='txtHint'>
    <?php
    while ($usuario = $usuarios->fetch_assoc()) {
        $rol = $conexion->query("SELECT nombre_rol FROM roles WHERE id_rol = ".$usuario['id_rol']);
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
<script>
    function showUser(str) {
        var tabla = 'usuarios'; //nombre de la tabla en la que se ejecutará la consulta
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "procesar-busqueda.php?q=" + str + "&tabla=" + tabla, true);
        xmlhttp.send();
}

// ===============================================
// REDIRIGIR A USUARIO ESPECIFICO AL HACER CLICK
// ===============================================
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