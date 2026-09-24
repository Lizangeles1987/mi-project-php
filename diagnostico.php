<?php
// Configuración actualizada
$host = "localhost";
$user = "root";
$pass = "";
$db   = "aula_virtual"; // Nombre corregido según tu indicación

// Intentar conexión inicial sin seleccionar DB para evitar el Fatal Error de PHP
$conexion_inicial = mysqli_connect($host, $user, $pass);

echo "<h1>🔍 Diagnóstico de Base de Datos</h1>";

if (!$conexion_inicial) {
    echo "<p style='color:red'>❌ Error crítico de conexión al servidor: " . mysqli_connect_error() . "</p>";
    exit;
}

// Verificar si la base de datos existe
$db_check = mysqli_select_db($conexion_inicial, $db);

if (!$db_check) {
    echo "<p style='color:red; font-weight:bold;'>❌ ERROR: La base de datos '$db' no existe.</p>";
    
    // Listar las bases de datos disponibles para ayudar al usuario
    echo "<h3>Bases de datos encontradas en tu sistema:</h3>";
    $res = mysqli_query($conexion_inicial, "SHOW DATABASES");
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($res)) {
        $name = $row['Database'];
        // Omitir bases de datos internas del sistema
        if (!in_array($name, ['information_schema', 'performance_schema', 'mysql', 'phpmyadmin'])) {
            echo "<li><strong>$name</strong></li>";
        }
    }
    echo "</ul>";
    echo "<p>💡 <em>Asegúrate de que el nombre '$db' esté escrito exactamente igual en phpMyAdmin.</em></p>";
    exit;
}

echo "<p style='color:green'>✅ Conexión exitosa a la base de datos <strong>'$db'</strong></p>";

// Listado de tablas que tu sistema debería tener
$tablas_a_revisar = ['usuarios', 'entregas', 'tareas'];

echo "<h2>Estado de las Tablas:</h2>";

foreach ($tablas_a_revisar as $tabla) {
    echo "<h3>Tabla: $tabla</h3>";
    $result = mysqli_query($conexion_inicial, "SHOW TABLES LIKE '$tabla'");
    
    if (mysqli_num_rows($result) == 0) {
        echo "<p style='color:red'>❌ La tabla '$tabla' NO existe en '$db'.</p>";
    } else {
        echo "<p style='color:green'>✅ La tabla existe. Estructura encontrada:</p>";
        $columns = mysqli_query($conexion_inicial, "SHOW COLUMNS FROM $tabla");
        echo "<ul style='font-family: monospace; background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
        while ($col = mysqli_fetch_assoc($columns)) {
            echo "<li>" . $col['Field'] . " - <span style='color: #666;'>" . $col['Type'] . "</span></li>";
        }
        echo "</ul>";
        
        $conteo_query = mysqli_query($conexion_inicial, "SELECT COUNT(*) as total FROM $tabla");
        if ($conteo_query) {
            $conteo = mysqli_fetch_assoc($conteo_query);
            echo "<p>📊 Filas actuales: <strong>" . $conteo['total'] . "</strong></p>";
        }
    }
    echo "<hr>";
}
?>