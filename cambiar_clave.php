<?php
session_start();

// 1. Validar sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave_actual    = trim($_POST['clave_actual'] ?? '');
    $clave_nueva     = trim($_POST['clave_nueva'] ?? '');
    $clave_confirmar = trim($_POST['clave_confirmar'] ?? '');
    $id_usuario      = $_SESSION['id'];

    if (empty($clave_actual) || empty($clave_nueva) || empty($clave_confirmar)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "bg-red-100 text-red-700 border-red-400";
    } elseif ($clave_nueva !== $clave_confirmar) {
        $mensaje = "La nueva contraseña y su confirmación no coinciden.";
        $tipo_mensaje = "bg-red-100 text-red-700 border-red-400";
    } elseif (strlen($clave_nueva) < 4) {
        $mensaje = "La nueva contraseña debe tener al menos 4 caracteres.";
        $tipo_mensaje = "bg-yellow-100 text-yellow-700 border-yellow-400";
    } else {
        // 2. Buscar al usuario en la base de datos
        $query = "SELECT * FROM usuarios WHERE id = '$id_usuario' LIMIT 1";
        $resultado = mysqli_query($conexion, $query);

        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            
            // Detectar cuál columna contiene la clave (password, clave o contrasena)
            $columna_pass = isset($usuario['password']) ? 'password' : (isset($usuario['clave']) ? 'clave' : 'contrasena');
            $pass_bd = $usuario[$columna_pass] ?? '';

            // 3. Validar únicamente la contraseña REAL que tiene guardada el usuario
            // (Funciona tanto para contraseñas encriptadas como para las antiguas en texto plano)
            $clave_valida = password_verify($clave_actual, $pass_bd) || ($clave_actual === $pass_bd);

            if ($clave_valida) {
                // 4. Generar el HASH seguro de la nueva clave
                $nueva_hash = password_hash($clave_nueva, PASSWORD_DEFAULT);
                $nueva_hash_escapada = mysqli_real_escape_string($conexion, $nueva_hash);

                // 5. Guardar la nueva clave encriptada
                $sql_update = "UPDATE usuarios SET $columna_pass = '$nueva_hash_escapada' WHERE id = '$id_usuario'";
                
                if (mysqli_query($conexion, $sql_update)) {
                    $mensaje = "¡Contraseña actualizada con éxito! A partir de ahora solo podrás ingresar con la nueva.";
                    $tipo_mensaje = "bg-green-100 text-green-700 border-green-400";
                } else {
                    $mensaje = "Error al actualizar en la base de datos: " . mysqli_error($conexion);
                    $tipo_mensaje = "bg-red-100 text-red-700 border-red-400";
                }
            } else {
                $mensaje = "La contraseña actual introducida no es correcta.";
                $tipo_mensaje = "bg-red-100 text-red-700 border-red-400";
            }
        } else {
            $mensaje = "Usuario no encontrado en la base de datos.";
            $tipo_mensaje = "bg-red-100 text-red-700 border-red-400";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-6 border border-slate-200">
        
        <div class="text-center mb-6">
            <div class="bg-blue-100 text-blue-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2 text-xl">
                <i class="fas fa-key"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800">Cambiar Contraseña</h2>
            <p class="text-slate-500 text-xs mt-1">
                Usuario: <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario'); ?></strong>
            </p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mb-4 p-3 border rounded-xl text-xs font-medium <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="cambiar_clave.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                    Contraseña Actual
                </label>
                <input type="password" name="clave_actual" required placeholder="••••••••"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                    Nueva Contraseña
                </label>
                <input type="password" name="clave_nueva" required placeholder="••••••••"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                    Confirmar Nueva Contraseña
                </label>
                <input type="password" name="clave_confirmar" required placeholder="••••••••"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm">
            </div>

            <div class="flex items-center justify-between pt-3">
                <a href="javascript:history.back()" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                    ← Volver
                </a>
                <button type="submit" 
                    class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-5 rounded-xl text-sm transition-all shadow-md">
                    Actualizar Clave
                </button>
            </div>
        </form>

    </div>

</body>
</html>