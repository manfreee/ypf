<?php
require_once __DIR__ . '/../conexion.php';

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /public/public/iniciar-sesion.php');
    exit();
}

// Obtener el ID del módulo del usuario
$id_usuario = $_SESSION['usuario']['id_usuario'];
$query = "SELECT id_modulo FROM modulos WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$modulo = $result->fetch_assoc();
$id_modulo = $modulo ? $modulo['id_modulo'] : null;

// Configuración de paginación
$registros_por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Filtros
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Construir la consulta con filtros
$where_conditions = [];
$params = [];
$types = "";

if ($id_modulo) {
    $where_conditions[] = "id_modulo = ?";
    $params[] = $id_modulo;
    $types .= "i";
}

if ($filtro_fecha_desde) {
    $where_conditions[] = "DATE(fecha_hora) >= ?";
    $params[] = $filtro_fecha_desde;
    $types .= "s";
}

if ($filtro_fecha_hasta) {
    $where_conditions[] = "DATE(fecha_hora) <= ?";
    $params[] = $filtro_fecha_hasta;
    $types .= "s";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Obtener el total de registros para la paginación
$count_query = "SELECT COUNT(*) as total FROM datos $where_clause";
$count_stmt = $conn->prepare($count_query);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$total_registros = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta para obtener los datos con paginación
$query = "SELECT * FROM datos $where_clause ORDER BY fecha_hora DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

// Agregar los parámetros de paginación
$types .= "ii";
$params[] = $registros_por_pagina;
$params[] = $offset;

$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultados = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Histórico - NecoTracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/css/estilos.css">
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #2c3e50;
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .pagination .page-link {
            color: #2c3e50;
        }
        .pagination .page-item.active .page-link {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }
        .filtros {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../componentes/header.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Resumen Histórico de Datos</h2>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="fecha_desde" class="form-label">Fecha desde:</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_hasta" class="form-label">Fecha hasta:</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="?" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen de métricas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Registros</h5>
                        <h2 class="card-text"><?php echo number_format($total_registros); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Módulo</h5>
                        <h4 class="card-text"><?php echo htmlspecialchars($modulo ? 'Módulo ' . $modulo['id_modulo'] : 'No asignado'); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Rango de fechas</h5>
                        <p class="card-text">
                            <?php 
                            if ($filtro_fecha_desde && $filtro_fecha_hasta) {
                                echo "Desde: " . date('d/m/Y', strtotime($filtro_fecha_desde)) . 
                                     " - Hasta: " . date('d/m/Y', strtotime($filtro_fecha_hasta));
                            } else {
                                echo "Mostrando todos los registros disponibles";
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-table"></i> Registros Históricos</h5>
                <a href="exportar_historico.php" class="btn btn-success btn-sm">
                    <i class="bi bi-download"></i> Exportar a Excel
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tablaDatos">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Tensión (V)</th>
                                <th>Corriente (A)</th>
                                <th>Potencia (W)</th>
                                <th>Energía (kWh)</th>
                                <th>Ángulo (°)</th>
                                <th>Intensidad (Lux)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($fila['fecha_hora'])); ?></td>
                                <td><?php echo number_format($fila['tension'], 1); ?></td>
                                <td><?php echo number_format($fila['corriente'], 2); ?></td>
                                <td><?php echo number_format($fila['potencia'], 1); ?></td>
                                <td><?php echo number_format($fila['energia'], 2); ?></td>
                                <td><?php echo number_format($fila['angulo'], 1); ?></td>
                                <td><?php echo number_format($fila['intensidad'], 0); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($resultados->num_rows === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center">No se encontraron registros con los filtros actuales</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegación de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina_actual > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>">Primera</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])); ?>">Anterior</a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $inicio = max(1, $pagina_actual - 2);
                        $fin = min($total_paginas, $pagina_actual + 2);
                        
                        for ($i = $inicio; $i <= $fin; $i++):
                            $clase_activa = ($i == $pagina_actual) ? 'active' : '';
                        ?>
                            <li class="page-item <?php echo $clase_activa; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagina_actual < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])); ?>">Siguiente</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>">Última</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#tablaDatos').DataTable({
                "paging": false, // Desactivar la paginación de DataTables (usamos la personalizada)
                "searching": true,
                "ordering": true,
                "info": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "order": [[0, 'desc']] // Ordenar por fecha descendente por defecto
            });

            // Establecer la fecha de hoy como valor predeterminado para el campo "hasta"
            document.getElementById('fecha_hasta').max = new Date().toISOString().split('T')[0];
            
            // Validar que la fecha desde no sea mayor que la fecha hasta
            document.querySelector('form').addEventListener('submit', function(e) {
                const fechaDesde = document.getElementById('fecha_desde').value;
                const fechaHasta = document.getElementById('fecha_hasta').value;
                
                if (fechaDesde && fechaHasta && new Date(fechaDesde) > new Date(fechaHasta)) {
                    e.preventDefault();
                    alert('La fecha desde no puede ser mayor que la fecha hasta');
                }
            });
        });
    </script>
</body>
</html>