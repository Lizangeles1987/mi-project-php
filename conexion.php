<?php
// Datos de configuración
$host = "localhost";
$user = "root"; // Usuario por defecto de XAMPP
$pass = "";     // Por defecto XAMPP no tiene contraseña
$db   = "aula_virtual";
$charset = "utf8mb4";

// 1. NUEVA CONEXIÓN EN FORMATO PDO (Para que funcione el nuevo cursos.php multiperiodo)
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("La conexión con PDO falló: " . $e->getMessage());
}

// 2. TU CONEXIÓN ACTUAL EN FORMATO MYSQLI (Para mantener vivos tus archivos anteriores)
$conexion = mysqli_connect($host, $user, $pass, $db);

// Verificar si funciona mysqli
if (!$conexion) {
    die("La conexión falló: " . mysqli_connect_error());
}

// Esto evita problemas con las tildes y la Ñ en tus consultas
mysqli_set_charset($conexion, "utf8mb4");
?>