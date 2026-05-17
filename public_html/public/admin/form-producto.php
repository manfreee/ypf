<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
    $nombre = $descripcion = $precio = $id = "";
    if (isset($_GET['eliminar']) && $_GET['eliminar'] === 'true') {
        $conexion->query("DELETE FROM productos WHERE id_producto = " . intval($_GET['id']));
        header("Location: /public/usuarios/tienda.php");
        exit();
    }
    else if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = intVal($_GET['id']);
        $producto = $conexion->query("SELECT * FROM productos WHERE id_producto = " . intval($_GET['id']))->fetch_assoc();
        $nombre = $producto['nombre'];
        $descripcion = $producto['descripcion'];
        $precio = $producto['precio'];
    }
    if (isset($_POST['nombre'], $_POST['descripcion'], $_POST['precio'])) {
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $descripcion = $conexion->real_escape_string($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $conexion->query("UPDATE productos SET nombre='$nombre', descripcion='$descripcion', precio=$precio WHERE id_producto = " . intval($_GET['id']));
        } else {
            $conexion->query("INSERT INTO productos (nombre, descripcion, precio) VALUES ('$nombre', '$descripcion', $precio)");
        }
        header("Location: tienda.php");
        exit();
    }
    
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Simple</title>
    <style>
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
