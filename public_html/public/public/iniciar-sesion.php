<?php
session_start();

// Los usuarios que ya tienen una sesión activa no necesitan iniciar sesión otra vez.
if (isset($_SESSION['id_usuario'])) {

    if ($_SESSION['id_rol'] == 1) {
        header("Location: admin/panel-admin.php"); 
    } else {
        header("Location: /index.php");
    }
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
$mensaje_error = "";
$mensaje_exito = "";

if (isset($_SESSION['mensaje_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
}

if (isset($_POST['iniciar-sesion'])) {

    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $contrasena = mysqli_real_escape_string($conexion, $_POST['contrasena']);

    if (empty($email) || empty($contrasena)) {
        $mensaje_error = "Error: Ambos campos son obligatorios.";
    } else {
        // Selecciona todos los campos necesarios
        $query = "SELECT id_usuario, nombre, apellido, email, contrasena, id_rol FROM usuarios WHERE email = '$email'";
        $resultado = mysqli_query($conexion, $query);
        if (mysqli_num_rows($resultado) == 1) {
            $usuario = mysqli_fetch_assoc($resultado);

            $contrasena_bd = $usuario['contrasena'];

            // doble verif hash y texto sin hash
            $password_correcta = false;

            // 1. Si la contraseña está hasheada (usa $2y$ al inicio)
            if (password_verify($contrasena, $contrasena_bd)) {
                $password_correcta = true;
            } 
            // 2. Si NO está hasheada lo compara directo ( se podria solucionar hashando todas las contraseñas viejardas)
            else if ($contrasena === $contrasena_bd) {
                $password_correcta = true;
            }
            if ($password_correcta) {
                // Conserva en la sesión la identidad y el rol del usuario autenticado.
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['apellido'] = $usuario['apellido'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['id_rol'] = $usuario['id_rol'];

                if ($usuario['id_rol'] == 1) {
                    header("Location: /index.php");
                } else {
                    header("Location: /index.php");
                }
                exit();
            } else {
                $mensaje_error = "Error: El correo electrónico o la contraseña son incorrectos.";
            }
        } else {
            $mensaje_error = "Error: El correo electrónico o la contraseña son incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="/css/general.css">
    <link rel="stylesheet" href="/css/autenticacion.css">
    <title>NecoTracking - Iniciar Sesión</title>
</head>

<body>
    <main>
        <!-- Fondo decorativo y formulario principal de inicio de sesión. -->
        <div class="contenedor-principal">
            <div class="circulo uno"></div>
            <div class="circulo dos"></div>

            <div class="contenedor-letras">
                <div class="fila">
                    <span class="texto grande color-blanco ethnocentric">Iniciar</span>
                    <span class="linea"></span>
                </div>
                <div class="fila">
                    <span class="linea"></span>
                    <span class="texto grande color-blanco ethnocentric">Sesión</span>
                </div>
            </div>


            <form class="borde-redondeado shadow formulario form-iniciar-sesion" method="POST" action="iniciar-sesion.php">

                <h4 class="titulo-form color-primario nulshock">Iniciar sesión</h4>

                <div class="form-cuerpo">
                    <!-- Contenedor para mostrar mensaje de éxito -->
                    <?php if (!empty($mensaje_exito)): ?>
                        <div class="contenedor-texto pequenio media-negrita texto-centrado color-exito">
                            <p class="muy-pequenio color-exito"><?php echo htmlspecialchars($mensaje_exito); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="input-grupo">
                        <div class="contenedor-input">
                            <iconify-icon icon="mdi:email" class="input-icon color-primario icon-h6"></iconify-icon>
                            <input required placeholder="Email" class="form-input" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                        </div>
                    </div>

                    <div class="input-grupo">
                        <div class="contenedor-input">
                            <iconify-icon icon="fluent:password-16-filled" class="input-icon color-primario icon-h6"></iconify-icon>
                            <input required placeholder="Contraseña" class="form-input" type="password" name="contrasena" />
                            <button class="password-visualizar" type="button">
                                <iconify-icon icon="iconoir:eye" class="input-icon icon-pequenio"></iconify-icon>
                            </button>
                        </div>
                        <a href="" class="muy-pequenio gris-claro light-swiss">¿Olvidaste tu contraseña?</a>
                    </div>
                </div>

                <!-- Contenedor para mostrar mensajes de error -->
                <div class="contenedor-texto">
                    <?php if (!empty($mensaje_error)): ?>
                        <p class="muy-pequenio negrita-swiss color-error"><?php echo htmlspecialchars($mensaje_error); ?></p>
                    <?php endif; ?>
                </div>

                <button class="btn-primario btn-enviar" type="submit" name="iniciar-sesion">
                    <span class="pequenio color-blanco negrita-swiss">Iniciar sesion</span>
                </button>

                <div class="form-footer">
                    <a class="pequenio iniciar-sesion-link" href="registrarse.php">
                        ¿No tienes cuenta? <span class="pequenio negrita-swiss">Registrarse</span>
                    </a>
                </div>
            </form>
        </div>
    </main>
    <!-- Permite alternar la visibilidad del campo de contraseña. -->
    <script src="/js/visualizarContrasenia.js"></script>
</body>
</html>