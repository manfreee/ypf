<?php
/*Define la función configuracionInicialModulo(), que se encarga de dar de alta los parámetros predeterminados para un módulo nuevo registrado en el sistema. Construye un arreglo asociativo con valores por defecto para sensores (humedad, temperatura, luz), riego (modo automático, umbral y duración) y opciones generales (frecuencia de lectura y notificaciones). Posteriormente, inserta cada una de estas filas en la tabla configuracion_modulos utilizando sentencias preparadas (prepare y bind_param) dentro de un bloque try...catch para garantizar la seguridad y el manejo silencioso de excepciones en los logs.*/
/*
 * Configuración inicial para un nuevo módulo
 * 
 * @param string $numero_serie Número de serie del módulo
 * @param mysqli $conn Conexión a la base de datos
 * @return bool True si la configuración se realizó correctamente, false en caso contrario
 */
function configuracionInicialModulo($numero_serie, $conn) {
    try {
        // Inicializar la lista de configuraciones predeterminadas ordenadas por clave, valor y tipo
        $configuraciones = [
            // Configuración de disponibilidad de sensores (1 = activado)
            ['numero_serie' => $numero_serie, 'clave' => 'sensor_humedad', 'valor' => '1', 'tipo' => 'sensor'],
            ['numero_serie' => $numero_serie, 'clave' => 'sensor_temperatura', 'valor' => '1', 'tipo' => 'sensor'],
            ['numero_serie' => $numero_serie, 'clave' => 'sensor_luz', 'valor' => '1', 'tipo' => 'sensor'],
            
            // Configuración del sistema de riego (modo automático, umbral mínimo % y tiempo de riego en mins/segs)
            ['numero_serie' => $numero_serie, 'clave' => 'riego_automatico', 'valor' => '1', 'tipo' => 'riego'],
            ['numero_serie' => $numero_serie, 'clave' => 'umbral_humedad', 'valor' => '30', 'tipo' => 'riego'],
            ['numero_serie' => $numero_serie, 'clave' => 'duracion_riego', 'valor' => '10', 'tipo' => 'riego'],
            
            // Configuración general del dispositivo (frecuencia de toma de lecturas y alertas)
            ['numero_serie' => $numero_serie, 'clave' => 'frecuencia_lectura', 'valor' => '5', 'tipo' => 'general'],
            ['numero_serie' => $numero_serie, 'clave' => 'notificaciones', 'valor' => '1', 'tipo' => 'general']
        ];

        // Preparar la consulta SQL parametrizada para la inserción masiva en la tabla 'configuracion_modulos'
        $stmt = $conn->prepare("INSERT INTO configuracion_modulos (numero_serie, clave, valor, tipo) VALUES (?, ?, ?, ?)");
        
        // Iterar sobre el arreglo e insertar individualmente cada clave de configuración
        foreach ($configuraciones as $config) {
            // Vincula los tipos de datos a la consulta (s = string para los 4 parámetros)
            $stmt->bind_param("ssss", 
                $config['numero_serie'],
                $config['clave'],
                $config['valor'],
                $config['tipo']
            );
            // Ejecuta la inserción en la base de datos
            $stmt->execute();
        }
        
        // Cierra la sentencia preparada tras finalizar las inserciones
        $stmt->close();
        
        // Retorna verdadero indicando éxito en la inicialización
        return true;
        
    } catch (Exception $e) {
        // En caso de fallo o excepción, registra el detalle técnico en los logs del servidor
        error_log("Error en configuracionInicialModulo: " . $e->getMessage());
        
        // Retorna falso para notificar que ocurrió un problema
        return false;
    }
}
?>