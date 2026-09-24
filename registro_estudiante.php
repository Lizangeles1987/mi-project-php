<?php
// Conexión a la base de datos
include 'conexion.php';

$mensaje = "";
$tipo_alerta = "";

// Lógica para procesar el formulario cuando se presiona "Registrar"
if (isset($_POST['registrar_estudiante'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    
    // CORREGIDO: Validamos primero que la clave 'email' realmente exista en la petición POST
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conexion, $_POST['email']) : ''; 
    
    if (empty($email)) {
        $mensaje = "❌ Por favor, ingresa un correo electrónico válido.";
        $tipo_alerta = "bg-red-100 text-red-700 border-red-200";
    } else {
        // Verificamos si el email ya existe en la base de datos
        $buscar_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email = '$email'");
        
        if (mysqli_num_rows($buscar_usuario) > 0) {
            $mensaje = "❌ Ese correo electrónico ya está registrado con otro usuario.";
            $tipo_alerta = "bg-red-100 text-red-700 border-red-200";
        } else {
            // Insertamos el nombre, email y rol estrictamente requeridos
            $sql = "INSERT INTO usuarios (nombre, email, rol) VALUES ('$nombre', '$email', 'estudiante')";
            
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "✅ Estudiante '$nombre' registrado con éxito. ¡Ya puedes asignarle notas!";
                $tipo_alerta = "bg-green-100 text-green-700 border-green-200";
            } else {
                $mensaje = "❌ Error al registrar: " . mysqli_error($conexion);
                $tipo_alerta = "bg-red-100 text-red-700 border-red-200";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Estudiante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { background-color: #0f172a; min-height: 100vh; width: 260px; }
        .main-content { background-color: #f8fafc; flex: 1; }
        .card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="flex">

    <aside class="sidebar text-slate-400 p-6">
        <h2 class="text-white text-2xl font-bold italic mb-10 text-blue-400">Aula Virtual</h2>
        <nav class="space-y-2">
            <a href="escritorio.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-th-large w-5"></i> Inicio
            </a>
            <a href="gestion_notas.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-graduation-cap w-5"></i> Notas y Logros
            </a>
            <a href="registro_estudiante.php" class="flex items-center gap-3 p-3 rounded-xl bg-blue-600/10 text-blue-400 border border-blue-600/20">
                <i class="fas fa-user-plus w-5"></i> Registrar Alumno
            </a>
            <div class="pt-8 mt-8 border-t border-slate-800">
                <a href="logout.php" class="flex items-center gap-3 p-3 rounded-xl text-red-400 hover:bg-red-900/10">
                    <i class="fas fa-sign-out-alt w-5"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content p-8 flex items-center justify-center">
        <div class="max-w-md w-full">
            
            <?php if(!empty($mensaje)): ?>
                <div class="p-4 mb-4 border rounded-xl text-sm font-medium <?php echo $tipo_alerta; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="card p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-800">Matricular Estudiante</h1>
                    <p class="text-slate-500 text-sm">Agrega un nuevo alumno al aula virtual</p>
                </div>

                <form action="registro_estudiante.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nombre Completo del Estudiante</label>
                        <input type="text" name="nombre" required placeholder="Ej: Juan Pérez" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Correo Electrónico (Único)</label>
                        <input type="email" name="email" required placeholder="juan@correo.com" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <button type="submit" name="registrar_estudiante" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 mt-2">
                        <i class="fas fa-plus mr-2"></i> Guardar Estudiante
                    </button>
                </form>
            </div>
        </div>
    </main>

</body>
</html>