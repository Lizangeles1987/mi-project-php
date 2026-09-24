<?php
session_start();
include 'conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_ingresado    = mysqli_real_escape_string($conexion, trim($_POST['email'] ?? ''));
    $password_ingresado = trim($_POST['password'] ?? ''); 

    if (!empty($email_ingresado) && !empty($password_ingresado)) {
        
        // Detectar si la columna se llama email o correo
        $columnas_query = mysqli_query($conexion, "SHOW COLUMNS FROM usuarios LIKE 'email'");
        $columna_email  = (mysqli_num_rows($columnas_query) > 0) ? 'email' : 'correo';

        $query = "SELECT * FROM usuarios WHERE $columna_email = '$email_ingresado' LIMIT 1";
        $resultado = mysqli_query($conexion, $query);

        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            
            // Detectar automáticamente el nombre de la columna donde se guarda la contraseña
            $pass_bd = $usuario['password'] ?? $usuario['clave'] ?? $usuario['contrasena'] ?? '';

            // Validar de forma limpia: Hash seguro o Texto plano original de la BD
            $es_valida = password_verify($password_ingresado, $pass_bd) || ($password_ingresado === $pass_bd);

            if ($es_valida) {
                
                $_SESSION['id']     = $usuario['id'];
                $_SESSION['nombre'] = isset($usuario['nombre']) ? $usuario['nombre'] : 'Usuario Prueba';
                $_SESSION['email']  = $email_ingresado; 

                // Forzado manual para tu cuenta admin
                if ($email_ingresado === 'admin@aula.com' || $usuario['nombre'] === 'Usuario Prueba') {
                    $_SESSION['rol'] = 'admin';
                } else {
                    $_SESSION['rol'] = isset($usuario['rol']) ? trim($usuario['rol']) : 'estudiante';
                }

                // Redirección inteligente según el rol del usuario
                if ($_SESSION['rol'] === 'estudiante') {
                    header("Location: estudiante_dashboard.php"); 
                } else {
                    header("Location: cursos.php"); 
                }
                exit();

            } else {
                $error = "La contraseña ingresada es incorrecta.";
            }
        } else {
            $error = "El usuario no está registrado.";
        }
    } else {
        $error = "Por favor, llena todos los campos.";
    }
    
    header("Location: login.php?error=" . urlencode($error));
    exit();
}
?>