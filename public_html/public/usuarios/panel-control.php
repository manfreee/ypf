<?php
/*Este código construye el panel de control (dashboard) para el monitoreo de módulos solares o de energía. Valida los permisos del usuario, consulta sus módulos asociados y obtiene las últimas lecturas eléctricas (tensión, corriente, potencia y energía). Además, recupera los últimos 20 registros para graficar la fluctuación de energía en tiempo real con ApexCharts, calcula un estimado monetario en pesos argentinos (ARS) según los kWh generados y renderiza los selectores para filtrar la información.*/

// Incluye la cabecera de la interfaz de usuario autenticado
include($_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header-usuario.php');

// Inicia la sesión PHP en caso de que no haya sido iniciada previamente
if(session_status() === PHP_SESSION_NONE){ session_start(); }

// Carga de la conexión a la base de datos
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Obtención de parámetros mediante la sesión y solicitudes GET (con valores por defecto)
$id_usuario = $_SESSION['id_usuario'] ?? null;
$mostrar = $_GET['mostrar'] ?? 'ambos';
$filtro = $_GET['filtro'] ?? 'todo';
$id_modulo = intval($_GET['id_modulo'] ?? 0);

// DECLARACIÓN DE VARIABLES INICIALES
$nombre=$apellido=$email=$localidad=$provincia=$pais='';
$modulo_nombre=$numero_serie='';
$tension=$corriente=$potencia=$energia=$angulo=$intensidad=null;
$datos_detallados = [];
$usuario_valido = false;
$rol = null;

// VALIDAR USUARIO: Comprueba en la BD si el usuario tiene rol autorizado (roles 1 o 2)
if($id_usuario){
    $res_rol = mysqli_query($conexion,"SELECT id_rol FROM usuarios WHERE id_usuario=$id_usuario LIMIT 1");
    if($res_rol && mysqli_num_rows($res_rol)>0){
        $rol = intval(mysqli_fetch_assoc($res_rol)['id_rol']);
        if(in_array($rol,[1,2])) $usuario_valido = true;
    }
}

// PRIMER MÓDULO POR DEFAULT: Si el usuario es válido y no hay un módulo seleccionado, asigna el primero que posea
if($usuario_valido){
    $res_mods = mysqli_query($conexion,"SELECT id_modulo,numero_serie,nombre FROM modulos WHERE id_usuario=$id_usuario ORDER BY id_modulo ASC LIMIT 1");
    if($res_mods && mysqli_num_rows($res_mods)>0){
        $prim_mod = mysqli_fetch_assoc($res_mods);
        if(!$id_modulo) $id_modulo = intval($prim_mod['id_modulo']);
        $numero_serie = $prim_mod['numero_serie'];
        $modulo_nombre = $prim_mod['nombre'];
    }
}

// ÚLTIMO REGISTRO: Obtiene la última medición registrada para el módulo activo (tensión, corriente, potencia, etc.)
$ultimo_reg = mysqli_query($conexion,"SELECT * FROM datos WHERE id_modulo=$id_modulo ORDER BY fecha_hora DESC LIMIT 1");
if($ultimo_reg && mysqli_num_rows($ultimo_reg)>0){
    $d = mysqli_fetch_assoc($ultimo_reg);
    $tension=$d['tension']; $corriente=$d['corriente']; $potencia=$d['potencia'];
    $energia=$d['energia']; $angulo=$d['angulo']; $intensidad=$d['intensidad'];
    $fecha_ultimo=$d['fecha_hora'];
}

// DATOS DETALLADOS: Obtiene los últimos 20 registros para construir la serie temporal del gráfico
$res_det = mysqli_query($conexion,"SELECT fecha_hora,energia FROM datos WHERE id_modulo=$id_modulo ORDER BY fecha_hora DESC LIMIT 20");
if($res_det && mysqli_num_rows($res_det)>0){
    while($fila=mysqli_fetch_assoc($res_det)){
        $datos_detallados[]= [
            "fecha"=>$fila['fecha_hora'],
            "hora"=>date("H:i:s",strtotime($fila['fecha_hora'])),
            "valor"=>floatval($fila['energia'])*1000 // Conversión a Watt-hora (Wh)
        ];
    }
}
// Invierte el orden de las mediciones para mostrarlas cronológicamente de izquierda a derecha
$datos_detallados = array_reverse($datos_detallados);

// VALOR ESTIMADO GENERADO: Calcula el monto monetario generado según la tarifa local de kWh
$valor_wh = $energia*1000; // Wh
$precio_kwh = 64; // Precio en ARS por kWh (Necochea aprox)
$estimado = $valor_wh/1000*$precio_kwh;
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

<!-- Formulario de filtros para seleccionar el módulo a visualizar y la vista de registros -->
<form class="filtro-panel" method="get">
    <input type="hidden" name="id_modulo" value="<?= $id_modulo ?>">
    <label>Módulo:</label>
    <select name="id_modulo" onchange="this.form.submit()">
        <?php
        // Renderiza en el desplegable todos los módulos asociados al usuario actual
        $res_mods_all = mysqli_query($conexion,"SELECT * FROM modulos WHERE id_usuario=$id_usuario");
        while($fila=mysqli_fetch_assoc($res_mods_all)){
            $sel = ($id_modulo==$fila['id_modulo'])?'selected':'';
            echo "<option value='{$fila['id_modulo']}' $sel>N° serie {$fila['numero_serie']}</option>";
        }
        ?>
    </select>

    <label>Mostrar:</label>
    <select name="mostrar" onchange="this.form.submit()">
        <option value="10s" <?= $mostrar=='10s'?'selected':'' ?>>Últimos 20 registros</option>
    </select>
</form>

<!-- Tarjeta de información en tiempo real con la lectura más reciente del módulo -->
<div class="tarjeta-ultimo">
    <h3>Último registro</h3>
    <p><strong>Fecha:</strong> <?= $fecha_ultimo ?? "—" ?></p>
    <p><strong>Tensión:</strong> <?= $tension ?> V</p>
    <p><strong>Corriente:</strong> <?= $corriente ?> A</p>
    <p><strong>Potencia:</strong> <?= $potencia ?> W</p>
     <p><strong>Energía:</strong> <?= $energia ?> Wats </p>
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
    <p><strong>$ ARS:</strong> <?= number_format($estimado,2,",",".") ?></p>
</div>

<!-- Transferencia de variables PHP hacia variables globales en JavaScript -->
<script>
window.id_modulo = <?= $id_modulo ?>;
window.datosDetallado = <?= json_encode($datos_detallados) ?>;
</script>

<!-- Librería externa de librerías para gráficos ApexCharts y script de inicialización -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="/js/configuracionChart.js"></script>

</main>
</body>
</html>