<?php
// 1. Iniciar o recuperar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Vaciar todas las variables de sesión
$_SESSION = array();

// 3. Destruir la cookie de sesión en el navegador si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();

// 5. Encabezados para evitar la caché del navegador al presionar "Atrás"
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 6. Redirigir al inicio de sesión
header("Location: login.php");
exit();
?>