<?php
/*Este script funciona como un módulo mixto de lógica y vista para la gestión de dispositivos/módulos. Se encarga de procesar las acciones enviadas por el usuario mediante peticiones POST para agregar un nuevo módulo o desvincular uno existente. Incluye una validación estricta del número de serie en el servidor y en el HTML a través de expresiones regulares (preg_match y pattern), garantizando el patrón 2 vocales + 3 números + 1 consonante. Finalmente, realiza una consulta para listar dinámicamente los módulos vinculados al usuario en sesión.*/

/* agregar nuevo modulo
y registrar los diferentes modulos de configuracion que tendra el usuario
2 vocales 3 numeros y una letra consonante
*/

include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /iniciar-sesion.php');
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];

/* --- AGREGAR MÓDULO --- */
// Comprueba si el usuario envió el formulario para agregar módulo
if (isset($_POST['agregar_modulo'])) {
    $modulo = trim($_POST['modulo'] ?? '');
    $numero_serie = trim($_POST['numero_serie'] ?? '');
    $estado_defecto = 'activo'; // Campo 'estado' es NOT NULL en la tabla modulos

    // Validar el formato del código usando expresión regular: 2 vocales, 3 números y 1 consonante
    if (preg_match('/^[AEIOUaeiou]{2}[0-9]{3}[BCDFGHJKLMNÑPQRSTVWXYZbcdfghjklmnñpqrstvwxyz]{1}$/', $numero_serie)) {
        
        $stmt = $conexion->prepare("INSERT INTO modulos (nombre, numero_serie, id_usuario, estado) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $modulo, $numero_serie, $id_usuario, $estado_defecto);

        // Ejecuta la inserción y retroalimenta al usuario
        if ($stmt->execute()) {
            echo "<p class='text-success'>Módulo agregado exitosamente.</p>";
        } else {
            echo "<p class='text-danger'>Error al agregar el módulo: " . htmlspecialchars($stmt->error) . "</p>";
        }

        // Cierra la sentencia preparada
        $stmt->close();
    } else {
        echo "<p class='text-danger'>El código del módulo no tiene el formato correcto.</p>";
    }
}

/* --- DESVINCULAR MÓDULO --- */
// Comprueba si el usuario solicitó eliminar un módulo
if (isset($_POST['desvincular'])) {
    $modulo_id = (int)$_POST['modulo_id'];

    try {
        // eliminar solo si pertenece al usuario logueado
        $delete = $conexion->prepare("DELETE FROM modulos WHERE id_modulo = ? AND id_usuario = ?");
        $delete->bind_param("ii", $modulo_id, $id_usuario);
        
        if ($delete->execute()) {
            if ($delete->affected_rows > 0) {
                echo "<p class='text-success'>Módulo desvinculado correctamente.</p>";
            } else {
                echo "<p class='text-danger'>No se pudo desvincular el módulo o no te pertenece.</p>";
            }
        }
        $delete->close();
    } catch (mysqli_sql_exception $e) {
        // Manejo por si existen registros vinculados en la tabla 'datos' o 'productos'
        echo "<p class='text-danger'>No se puede eliminar el módulo porque tiene datos e historial asociados.</p>";
    }
}
?>

<!-- FORMULARIO PARA AGREGAR MÓDULO -->
<div class="">
    <form action="" method="post">
        <div class="mb-3">
            <label for="modulo" class="form-label">Nombre del Módulo</label>
            <input type="text" class="form-control" name="modulo" required>
        </div>

        <div class="mb-3">
            <label for="numero_serie" class="form-label">Código del Módulo</label>
            <!-- Validación del lado del cliente con HTML5 usando la misma Regex -->
            <input type="text" 
                   class="form-control" 
                   name="numero_serie" 
                   pattern="[AEIOUaeiou]{2}[0-9]{3}[BCDFGHJKLMNÑPQRSTVWXYZbcdfghjklmnñpqrstvwxyz]{1}" 
                   title="El código debe tener 2 vocales, 3 números y 1 consonante." 
                   required>
        </div>

        <button type="submit" name="agregar_modulo" class="btn btn-primary">Agregar Módulo</button>
    </form>
</div>

<hr>

<?php
/* --- MOSTRAR MODULOS DEL USUARIO --- */

$stmt = $conexion->prepare("SELECT id_modulo, nombre, numero_serie, estado FROM modulos WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Muestra un mensaje si no hay registros o itera el resultado para dibujarlos en pantalla
if ($result->num_rows === 0) {
    echo "<p class='mt-3'>No tenés módulos registrados todavía.</p>";
} else {
    while ($row = $result->fetch_assoc()) {

        echo "<div class='modulo border rounded p-3 my-2'>";
        // Sanitize variables para prevenir ataques XSS
        echo "<h4>" . htmlspecialchars($row['nombre']) . "</h4>";
        echo "<p><b>Código:</b> " . htmlspecialchars($row['numero_serie']) . "</p>";

        // Formulario individual para la desvinculación del módulo mediante botón
        echo "<form action='' method='post'>
                <input type='hidden' name='modulo_id' value='" . (int)$row['id_modulo'] . "'>
                <button type='submit' name='desvincular' class='btn btn-danger'>
                    Desvincular Módulo
                </button>
              </form>";

        echo "</div>";
    }
}
$stmt->close();
?>