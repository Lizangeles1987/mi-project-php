<?php
session_start();

// Si el usuario ya tiene una sesión activa, lo mandamos directo a sus cursos
if (isset($_SESSION['nombre'])) {
    header("Location: cursos.php");
    exit();
} else {
    // Si no ha iniciado sesión, lo mandamos a que se identifique
    header("Location: login.php");
    exit();
}
?>