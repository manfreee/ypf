<?php
/* agregar nuevo modulo
y registrar los diferentes modulos de configuracion que tendra el usuario
2 vocales 3 numeros y una letra consonante
*/

include "../../PHP/conexion.php";
session_start();

$id_usuario = $_SESSION['id_usuario'];

/* --- AGREGAR MÓDULO --- */
if (isset($_POST['agregar_modulo'])) {
    $modulo = $_POST['modulo'];
    $numero_serie = $_POST['numero_serie'];

    // Validar el formato
    if (preg_match('/^[AEIOUaeiou]{2}[0-9]{3}[BCDFGHJKLMNÑPQRSTVWXYZbcdfghjklmnñpqrstvwxyz]{1}$/', $numero_serie)) {
        
        $stmt = $conexion->prepare("INSERT INTO modulos (nombre, numero_serie, id_usuario) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $modulo, $numero_serie, $id_usuario);

        if ($stmt->execute()) {
            echo "<p class='text-success'>Módulo agregado exitosamente.</p>";
        } else {
            echo "<p class='text-danger'>Error al agregar el módulo: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p class='text-danger'>El código del módulo no tiene el formato correcto.</p>";
    }
}

/* --- DESVINCULAR MÓDULO --- */
if (isset($_POST['desvincular'])) {
    $modulo_id = $_POST['modulo_id'];

    // eliminar solo si pertenece al usuario logueado
    $delete = $conexion->prepare("DELETE FROM modulos WHERE id_modulo = ? AND id_usuario = ?");
    $delete->bind_param("ii", $modulo_id, $id_usuario);
    $delete->execute();

    echo "<p class='text-success'>Módulo desvinculado correctamente.</p>";
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

$stmt = $conexion->prepare("SELECT id_modulo, nombre, numero_serie FROM modulos WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='mt-3'>No tenés módulos registrados todavía.</p>";
} else {
    while ($row = $result->fetch_assoc()) {

        echo "<div class='modulo border rounded p-3 my-2'>";
        echo "<h4>" . htmlspecialchars($row['nombre']) . "</h4>";
        echo "<p><b>Código:</b> " . htmlspecialchars($row['numero_serie']) . "</p>";

        echo "<form action='' method='post'>
                <input type='hidden' name='modulo_id' value='" . $row['id_modulo'] . "'>
                <button type='submit' name='desvincular' class='btn btn-danger'>
                    Desvincular Módulo
                </button>
              </form>";

        echo "</div>";
    }
}
?>
