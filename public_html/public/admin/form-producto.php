<?php
    // Se marca este archivo como parte de una API o lógica del backend.
    
    // Se incluye el archivo de conexión a la base de datos.
    include_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';

    // Se inicializan variables vacías para reutilizarlas en el formulario.
    // Estas variables guardarán los datos del producto que se va a editar o crear.
    $nombre = $descripcion = $precio = $id = "";

    // Si llega el parámetro ?eliminar=true en la URL, se elimina el producto.
    if (isset($_GET['eliminar']) && $_GET['eliminar'] === 'true') {
        // Se ejecuta una sentencia DELETE para borrar el producto según su id.
        $conexion->query("DELETE FROM productos WHERE id_producto = " . intval($_GET['id']));

        // Después de eliminar, se redirige a la tienda.
        header("Location: /public/usuarios/tienda.php");

        // Se corta la ejecución para evitar que el resto del código siga.
        exit();
    }
    // Si hay un parámetro id en la URL y no está vacío, significa que se va a editar.
    else if (isset($_GET['id']) && !empty($_GET['id'])) {
        // Convierte el id recibido a entero.
        $id = intVal($_GET['id']);

        // Busca el producto en la base de datos usando el id.
        $producto = $conexion->query("SELECT * FROM productos WHERE id_producto = " . intval($_GET['id']))->fetch_assoc();

        // Se cargan los datos del producto en las variables para mostrarlos en el formulario.
        $nombre = $producto['nombre'];
        $descripcion = $producto['descripcion'];
        $precio = $producto['precio'];
    }

    // Si se envió el formulario mediante POST, se procesa la información.
    if (isset($_POST['nombre'], $_POST['descripcion'], $_POST['precio'])) {
        // Se sanitizan los datos para evitar inyección SQL.
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $descripcion = $conexion->real_escape_string($_POST['descripcion']);

        // Convierte el precio a número decimal.
        $precio = floatval($_POST['precio']);

        // Si existe un id en la URL, se actualiza el producto.
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $conexion->query("UPDATE productos SET nombre='$nombre', descripcion='$descripcion', precio=$precio WHERE id_producto = " . intval($_GET['id']));
        } else {
            // Si no existe id, se crea un producto nuevo.
            $conexion->query("INSERT INTO productos (nombre, descripcion, precio) VALUES ('$nombre', '$descripcion', $precio)");
        }

        // Luego de guardar, redirige a la página de la tienda.
        header("Location: tienda.php");

        // Detiene la ejecución para no seguir mostrando contenido.
        exit();
    }
    
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Simple</title>
    <style>
        /* Estilos básicos para que el formulario se vea ordenado */
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        form {
            max-width: 300px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        label {
            font-weight: bold;
        }

        input, textarea, button {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            cursor: pointer;
            background: #f3f3f3;
        }

        button:hover {
            background: #e2e2e2;
        }
    </style>
</head>
<body>
    <!--
        El formulario se envía a la misma página.
        Si hay un id, se agrega como parámetro en la URL para saber si estamos editando.
        Ejemplo: form-producto.php?id=5
    -->
    <form action="form-producto.php?id=<?= $id ?>" method="POST">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Nombre" value="<?= $nombre ?>">

        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="4" required placeholder="Descripción"><?= $descripcion ?></textarea>

        <label for="precio">Precio</label>
        <input type="number" id="precio" name="precio" step="0.10" required placeholder="00.00" value="<?= $precio ?>">

        <button type="submit">Enviar</button>
    </form>
</body>
</html>