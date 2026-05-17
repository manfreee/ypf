<?php
/**
 * Configuración inicial para un nuevo módulo
 * 
 * @param string $numero_serie Número de serie del módulo
 * @param mysqli $conn Conexión a la base de datos
 * @return bool True si la configuración se realizó correctamente, false en caso contrario
 */
function configuracionInicialModulo($numero_serie, $conn) {
    try {
        // Inicializar configuraciones por defecto para el módulo
        $configuraciones = [
            // Configuración de sensores
            ['numero_serie' => $numero_serie, 'clave' => 'sensor_humedad', 'valor' => '1', 'tipo' => 'sensor'],
            ['numero_serie' => $numero_serie, 'clave' => 'sensor_temperatura', 'valor' => '1', 'tipo' => 'sensor'],
            ['numero_serie' => $numero_serie, 'clave' => 'sensor_luz', 'valor' => '1', 'tipo' => 'sensor'],
            
            // Configuración de riego
            ['numero_serie' => $numero_serie, 'clave' => 'riego_automatico', 'valor' => '1', 'tipo' => 'riego'],
            ['numero_serie' => $numero_serie, 'clave' => 'umbral_humedad', 'valor' => '30', 'tipo' => 'riego'],
            ['numero_serie' => $numero_serie, 'clave' => 'duracion_riego', 'valor' => '10', 'tipo' => 'riego'],
            
            // Configuración general
            ['numero_serie' => $numero_serie, 'clave' => 'frecuencia_lectura', 'valor' => '5', 'tipo' => 'general'],
            ['numero_serie' => $numero_serie, 'clave' => 'notificaciones', 'valor' => '1', 'tipo' => 'general']
        ];

        // Preparar la consulta para insertar configuraciones
        $stmt = $conn->prepare("INSERT INTO configuracion_modulos (numero_serie, clave, valor, tipo) VALUES (?, ?, ?, ?)");
        
        // Insertar cada configuración
        foreach ($configuraciones as $config) {
            $stmt->bind_param("ssss", 
                $config['numero_serie'],
                $config['clave'],
                $config['valor'],
                $config['tipo']
            );
            $stmt->execute();
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        // En caso de error, registrar el error y devolver false
        error_log("Error en configuracionInicialModulo: " . $e->getMessage());
        return false;
    }
}
?>
