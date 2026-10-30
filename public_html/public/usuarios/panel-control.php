<?php
/*Este código construye el panel de control (dashboard) para el monitoreo de módulos solares o de energía. Valida los permisos del usuario, consulta sus módulos asociados y obtiene las últimas lecturas eléctricas (tensión, corriente, potencia y energía). Además, recupera los últimos 20 registros para graficar la fluctuación de energía en tiempo real con ApexCharts, calcula un estimado monetario en pesos argentinos (ARS) según los kWh generados y renderiza los selectores para filtrar la información.*/

// Incluye la cabecera de la interfaz de usuario autenticado
include($_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header-usuario.php');

// Inicia la sesión PHP en caso de que no haya sido iniciada previamente
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Carga de la conexión a la base de datos
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Obtención de parámetros mediante la sesión y solicitudes GET (con valores por defecto)
$id_usuario = $_SESSION['id_usuario'] ?? null;
$mostrar = $_GET['mostrar'] ?? '10s';
$id_modulo = filter_input(INPUT_GET, 'id_modulo', FILTER_VALIDATE_INT) ?? 0;

// DECLARACIÓN DE VARIABLES INICIALES
$nombre = $apellido = $email = $localidad = $provincia = $pais = '';
$modulo_nombre = $numero_serie = '';
$tension = $corriente = $potencia = $energia = $angulo = $intensidad = null;
$fecha_ultimo = null;
$datos_detallados = [];
$usuario_valido = false;
$rol = null;

// VALIDAR USUARIO: Comprueba en la BD si el usuario tiene rol autorizado (roles 1 o 2)
if ($id_usuario) {
    $stmt_rol = $conexion->prepare("SELECT id_rol FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $stmt_rol->bind_param("i", $id_usuario);
    $stmt_rol->execute();
    $res_rol = $stmt_rol->get_result();
    
    if ($res_rol && $res_rol->num_rows > 0) {
        $rol = intval($res_rol->fetch_assoc()['id_rol']);
        if (in_array($rol, [1, 2])) {
            $usuario_valido = true;
        }
    }
    $stmt_rol->close();
}

// OBTENER MÓDULOS DEL USUARIO Y VALIDAR MÓDULO SELECCIONADO
$modulos_usuario = [];
if ($usuario_valido) {
    $stmt_mods = $conexion->prepare("SELECT id_modulo, numero_serie, nombre FROM modulos WHERE id_usuario = ? ORDER BY id_modulo ASC");
    $stmt_mods->bind_param("i", $id_usuario);
    $stmt_mods->execute();
    $res_mods = $stmt_mods->get_result();
    
    while ($fila = $res_mods->fetch_assoc()) {
        $modulos_usuario[] = $fila;
    }
    $stmt_mods->close();

    // Si no especificó id_modulo o el modulo especificado no le pertenece, asigna el primero
    $modulos_ids = array_column($modulos_usuario, 'id_modulo');
    if ((!$id_modulo || !in_array($id_modulo, $modulos_ids)) && !empty($modulos_usuario)) {
        $id_modulo = intval($modulos_usuario[0]['id_modulo']);
        $numero_serie = $modulos_usuario[0]['numero_serie'];
        $modulo_nombre = $modulos_usuario[0]['nombre'];
    }
}

// ÚLTIMO REGISTRO: Obtiene la última medición registrada para el módulo activo
if ($id_modulo > 0) {
    $stmt_ultimo = $conexion->prepare("SELECT * FROM datos WHERE id_modulo = ? ORDER BY fecha_hora DESC LIMIT 1");
    $stmt_ultimo->bind_param("i", $id_modulo);
    $stmt_ultimo->execute();
    $res_ultimo = $stmt_ultimo->get_result();
    
    if ($res_ultimo && $res_ultimo->num_rows > 0) {
        $d = $res_ultimo->fetch_assoc();
        $tension = $d['tension']; 
        $corriente = $d['corriente']; 
        $potencia = $d['potencia'];
        $energia = $d['energia']; 
        $angulo = $d['angulo']; 
        $intensidad = $d['intensidad'];
        $fecha_ultimo = $d['fecha_hora'];
    }
    $stmt_ultimo->close();

    // DATOS DETALLADOS: Obtiene los últimos 20 registros para construir la serie temporal del gráfico
    $stmt_det = $conexion->prepare("SELECT fecha_hora, energia FROM datos WHERE id_modulo = ? ORDER BY fecha_hora DESC LIMIT 20");
    $stmt_det->bind_param("i", $id_modulo);
    $stmt_det->execute();
    $res_det = $stmt_det->get_result();
    
    if ($res_det && $res_det->num_rows > 0) {
        while ($fila = $res_det->fetch_assoc()) {
            $datos_detallados[] = [
                "fecha" => $fila['fecha_hora'],
                "hora"  => date("H:i:s", strtotime($fila['fecha_hora'])),
                "valor" => floatval($fila['energia']) * 1000 // Conversión de kWh a Watt-hora (Wh)
            ];
        }
    }
    $stmt_det->close();
}

// Invierte el orden de las mediciones para mostrarlas cronológicamente de izquierda a derecha
$datos_detallados = array_reverse($datos_detallados);

// VALOR ESTIMADO GENERADO: Calcula el monto monetario generado según la tarifa local de kWh
$precio_kwh = 64; // Precio en ARS por kWh
$estimado = ($energia !== null) ? ($energia * $precio_kwh) : 0;
?>

<!-- Hojas de estilo locales e internas del panel -->
<link rel="stylesheet" href="/css/panel-control.css">

<style>
.filtro-panel{background:#fff;padding:18px;border-radius:12px;margin-bottom:20px;box-shadow:0px 2px 6px #00000015;display:flex;gap:15px;align-items:center;flex-wrap:wrap;}
.filtro-panel label{font-weight:bold;}
.grid-control-panel{display:flex;gap:15px;flex-wrap:wrap;margin-top:15px;}
.grid-secundaria{flex:1;min-width:300px;padding:15px;background:#fff;border-radius:12px;box-shadow:0px 2px 6px #00000018;}
.grafico{height:300px;}
.tarjeta-ultimo{padding:15px;background:#fff;border-radius:12px;box-shadow:0px 2px 6px #00000018;margin-bottom:25px;}
</style>

<main>

<!-- Formulario de filtros para seleccionar el módulo a visualizar -->
<form class="filtro-panel" method="get">
    <label>Módulo:</label>
    <select name="id_modulo" onchange="this.form.submit()">
        <?php if (!empty($modulos_usuario)): ?>
            <?php foreach ($modulos_usuario as $mod): ?>
                <?php $sel = ($id_modulo == $mod['id_modulo']) ? 'selected' : ''; ?>
                <option value="<?= $mod['id_modulo'] ?>" <?= $sel ?>>
                    <?= htmlspecialchars($mod['nombre']) ?> (N° serie <?= htmlspecialchars($mod['numero_serie']) ?>)
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="0">Sin módulos registrados</option>
        <?php endif; ?>
    </select>

    <label>Mostrar:</label>
    <select name="mostrar" onchange="this.form.submit()">
        <option value="10s" <?= $mostrar == '10s' ? 'selected' : '' ?>>Últimos 20 registros</option>
    </select>
</form>

<!-- Tarjeta de información en tiempo real con la lectura más reciente del módulo -->
<div class="tarjeta-ultimo">
    <h3>Último registro</h3>
    <p><strong>Fecha:</strong> <?= $fecha_ultimo ?? "—" ?></p>
    <p><strong>Tensión:</strong> <?= $tension !== null ? $tension . " V" : "—" ?></p>
    <p><strong>Corriente:</strong> <?= $corriente !== null ? $corriente . " A" : "—" ?></p>
    <p><strong>Potencia:</strong> <?= $potencia !== null ? $potencia . " W" : "—" ?></p>
    <p><strong>Energía:</strong> <?= $energia !== null ? $energia . " kWh" : "—" ?></p>
</div>

<!-- Contenedor del gráfico de área/líneas de la fluctuación de energía -->
<div class="grid-control-panel">
    <div class="grid-secundaria">
        <div class="contenedor-informacion">
            <p class="gris-claro">Fluctuación de energía (Wh)</p>
        </div>
        <div class="grafico" id="chartDetallado"></div>
    </div>
</div>

<!-- Tarjeta con el importe monetario estimado de la energía producida -->
<div class="tarjeta-ultimo">
    <h3>Valor estimado generado</h3>
    <p><strong>$ ARS:</strong> $<?= number_format($estimado, 2, ",", ".") ?></p>
</div>

<!-- Transferencia de variables PHP hacia variables globales en JavaScript -->
<script>
window.id_modulo = <?= json_encode($id_modulo) ?>;
window.datosDetallado = <?= json_encode($datos_detallados) ?>;
</script>

<!-- Librería externa para gráficos ApexCharts y script de inicialización -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="/js/configuracionChart.js"></script>

</main>
</body>
</html>