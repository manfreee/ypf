<?php
// Inicia la sesión
session_start();

// Carga la conexión a la base de datos
require_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Mensaje que se mostrará en el formulario cuando ocurra un error
$mensaje = "";

// Procesa el formulario únicamente cuando se pulsa el botón de registro
if (isset($_POST['registrarse'])) {
    // Escapa los datos antes de incorporarlos a las consultas SQL
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
    $pais = mysqli_real_escape_string($conexion, $_POST['pais']);
    $provincia = mysqli_real_escape_string($conexion, $_POST['provincia']);
    $localidad = mysqli_real_escape_string($conexion, $_POST['localidad']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $contrasena = mysqli_real_escape_string($conexion, $_POST['contrasena']);

    // Comprueba los campos obligatorios y el formato del correo electrónico
    if (empty($nombre) || empty($apellido) || empty($email) || empty($contrasena)) {
        $mensaje = "Error: Todos los campos marcados son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Error: El formato del correo electrónico no es válido.";
    } else {
        // Evita registrar más de una cuenta con el mismo correo
        $query_check_email = "SELECT id_usuario FROM usuarios WHERE email = '$email'";
        $result_email = mysqli_query($conexion, $query_check_email);

        if (mysqli_num_rows($result_email) > 0) {
            $mensaje = "Error: El correo electrónico ya está en uso. Por favor, elige otro.";
        } else {
            // guarda la contraseña en un hash seguro
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

            // La transacción permite confirmar o deshacer el registro como una sola operación.
            mysqli_begin_transaction($conexion);
            try {
                // Crea el usuario con el rol 2, correspondiente a un usuario normal
                $query_insert_user = "INSERT INTO usuarios (nombre, apellido, email, contrasena, localidad, provincia, pais, id_rol) VALUES ('$nombre', '$apellido', '$email', '$contrasena_hash', '$localidad', '$provincia', '$pais', 2)";
                $result_insert_user = mysqli_query($conexion, $query_insert_user);

                if (!$result_insert_user) {
                    throw new Exception("Error al crear el usuario.");
                }
                mysqli_commit($conexion);

                // guarda el mensaje en la sesión para mostrarlo en la página de inicio de sesión
                $_SESSION['mensaje_exito'] = "¡Registro completado con éxito! Ahora puedes iniciar sesión.";
                header("Location: iniciar-sesion.php");
                exit();
            }   catch (Exception $e) {
                // Si falla la inserción, revierte cualquier cambio de la transacción
                mysqli_rollback($conexion);
                var_dump($query_insert_user);
                $mensaje = "Error en el registro: No se pudieron completar todas las operaciones. Inténtelo de nuevo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--  estilo utilizado por el formulario -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="/css/general.css">
    <link rel="stylesheet" href="/css/autenticacion.css">
    <title>NecoTracking - Registro</title>
</head>
<body>
    <main>
        <div class="contenedor-principal">
            <div class="circulo uno"></div>
            <div class="circulo dos"></div>
            <div class="contenedor-letras">
                <div class="fila">
                    <span class="texto grande color-blanco ethnocentric subrayado">Registro</span>
                </div>
            </div>
            <!-- Formulario de creación de cuenta que se envia a este mismo archivo mediante POST -->
            <form class="borde-redondeado shadow formulario form-iniciar-sesion" method="POST" action="registrarse.php">
                <h4 class="titulo-form color-primario nulshock">Registrarse</h4>
                <div class="form-cuerpo">
                    <div class="contenedor-dividido">
                        <div class="input-grupo">
                            <div class="contenedor-input">
                                <iconify-icon icon="solar:user-bold" class="input-icon color-primario icon-h6"></iconify-icon>
                                <!-- Mantiene el valor escrito si la validación devuelve un error -->
                                <input required placeholder="Nombre" name="nombre" class="form-input" type="text" autocomplete="off" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" />
                            </div>
                        </div>
                        <div class="input-grupo">
                            <div class="contenedor-input">
                                <iconify-icon icon="solar:user-linear" class="input-icon color-primario icon-h6"></iconify-icon>
                                <input required placeholder="Apellido" name="apellido" class="form-input" type="text" autocomplete="off" value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="input-grupo">
                        <div class="contenedor-input">
                            <iconify-icon icon="material-symbols:location-away-rounded" class="input-icon color-primario icon-h6"></iconify-icon>
                            <input placeholder="País" name="pais" class="form-input" type="text" autocomplete="off" value="<?= htmlspecialchars($_POST['pais'] ?? '') ?>" />
                        </div>
                    </div>
                    <div class="contenedor-dividido">
                        <div class="input-grupo">
                            <div class="contenedor-input">
                                <iconify-icon icon="material-symbols:location-away-rounded" class="input-icon color-primario icon-h6"></iconify-icon>
                                <input placeholder="Provincia" name="provincia" class="form-input" type="text" autocomplete="off" value="<?= htmlspecialchars($_POST['provincia'] ?? '') ?>" />
                            </div>
                        </div>
                        <div class="input-grupo">
                            <div class="contenedor-input">
                                <iconify-icon icon="material-symbols:location-away-rounded" class="input-icon color-primario icon-h6"></iconify-icon>
                                <input placeholder="Localidad" name="localidad" class="form-input" type="text" autocomplete="off" value="<?= htmlspecialchars($_POST['localidad'] ?? '') ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="input-grupo">
                        <div class="contenedor-input">
                            <iconify-icon icon="mdi:email" class="input-icon color-primario icon-h6"></iconify-icon>
                            <input required placeholder="Email" name="email" class="form-input" type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                        </div>
                    </div>
                    <div class="input-grupo">
                        <div class="contenedor-input">
                            <iconify-icon icon="fluent:password-16-filled" class="input-icon color-primario icon-h6"></iconify-icon>
                            <input required placeholder="Contraseña" name="contrasena" class="form-input" type="password" />
                            <button class="password-visualizar" type="button">
                                <iconify-icon icon="iconoir:eye" class="input-icon icon-pequenio"></iconify-icon>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Muestra los errores generados durante el procesamiento del formulario -->
                <div class="contenedor-texto">
                    <?php if (!empty($mensaje)): ?>
                        <p class="muy-pequenio negrita-swiss color-error"><?php echo $mensaje; ?></p>
                    <?php endif; ?>
                </div>
                <button class="btn-primario btn-enviar" type="submit" name="registrarse">
                    <span class="pequenio color-blanco negrita-swiss">Crear cuenta</span>
                </button>
                <div class="form-footer">
                    <a class="pequenio iniciar-sesion-link" href="iniciar-sesion.php">
                        ¿Ya tienes cuenta? <span class="pequenio negrita-swiss">Iniciar sesión</span>
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>