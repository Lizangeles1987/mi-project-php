<?php
// Evitamos errores si la sesión ya estaba abierta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// INCLUIMOS LA CONEXIÓN AUTOMÁTICAMENTE
include 'conexion.php';

// Si el usuario no ha iniciado sesión, lo dejamos pasar SOLÓ si está en el login
// Esto rompe el bucle de la pantalla congelada
if (!isset($_SESSION['nombre']) && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'controlador_login.php') { 
    header("Location: login.php"); 
    exit(); 
}
?>