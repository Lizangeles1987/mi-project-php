<?php
session_start();
if (!isset($_SESSION['nombre'])) { 
    header("Location: login.php"); 
    exit(); 
}

include 'conexion.php'; 

$mensaje = "";
$tipo_alerta = "";

// ACCIÓN: ACCIÓN DE LIMPIEZA TOTAL DE ESTUDIANTES
if (isset($_POST['vaciar_base'])) {
    // Borramos solo los usuarios que sean estudiantes para no borrar a los profesores/administradores
    $sql_vaciar = "DELETE FROM usuarios WHERE rol = 'estudiante'";
    if (mysqli_query($conexion, $sql_vaciar)) {
        $mensaje = "🗑️ Se han eliminado todos los estudiantes del sistema. ¡Puedes subir el archivo correcto ahora!";
        $tipo_alerta = "success";
    } else {
        $mensaje = "❌ Error al vaciar la base de datos: " . mysqli_error($conexion);
        $tipo_alerta = "error";
    }
}

// PROCESAR EL ARCHIVO CUANDO SE HACE SUBMIT
if (isset($_POST['importar'])) {
    if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] == 0) {
        
        $filename = $_FILES['archivo_excel']['tmp_name'];
        $ext = pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION);
        
        if (strtolower($ext) == 'csv') {
            if (($handle = fopen($filename, "r")) !== FALSE) {
                
                fgetcsv($handle, 1000, ","); // Omitir cabecera
                
                $insertados = 0;
                $errores = 0;
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 4) {
                        $nombre = mysqli_real_escape_string($conexion, trim($data[0]));
                        $email = mysqli_real_escape_string($conexion, trim($data[1]));
                        $password = mysqli_real_escape_string($conexion, trim($data[2])); 
                        $grado = (int)trim($data[3]); 
                        $rol = 'estudiante'; 
                        
                        if (!empty($nombre) && !empty($email) && $grado > 0) {
                            $check = mysqli_query($conexion, "SELECT id FROM usuarios WHERE email = '$email'");
                            
                            if ($check && mysqli_num_rows($check) == 0) {
                                $sql = "INSERT INTO usuarios (nombre, email, password, rol, grado) 
                                        VALUES ('$nombre', '$email', '$password', '$rol', '$grado')";
                                
                                if (mysqli_query($conexion, $sql)) {
                                    $insertados++;
                                } else {
                                    $errores++;
                                }
                            } else {
                                $errores++; 
                            }
                        }
                    }
                }
                fclose($handle);
                
                $mensaje = "🎉 ¡Proceso terminado! Se matricularon <strong>$insertados</strong> estudiantes organizados por salones.";
                $tipo_alerta = "success";
                
            } else {
                $mensaje = "❌ No se pudo leer el archivo.";
                $tipo_alerta = "error";
            }
        } else {
            $mensaje = "❌ Formato inválido. Debe ser un archivo .CSV";
            $tipo_alerta = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matrícula Masiva - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background-color: #0f172a; min-height: 100vh; width: 260px; }
        .main-content { background-color: #f8fafc; flex: 1; }
    </style>
</head>
<body class="bg-slate-50 flex">

    <aside class="sidebar text-slate-400 p-6">
        <h2 class="text-white text-2xl font-bold italic mb-10 text-blue-400">Aula Primaria</h2>
        <nav class="space-y-2">
            <a href="cursos.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-th-large w-5"></i> Mis Cursos
            </a>
            <a href="gestion_notas.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-graduation-cap w-5"></i> Notas y Logros
            </a>
            <a href="subir_alumnos.php" class="flex items-center gap-3 p-3 rounded-xl bg-blue-600/10 text-blue-400 border border-blue-600/20">
                <i class="fas fa-file-upload w-5"></i> Matrícula Masiva Excel
            </a>
            <div class="pt-8 mt-8 border-t border-slate-800">
                <a href="logout.php" class="flex items-center gap-3 p-3 rounded-xl text-red-400 hover:bg-red-900/10">
                    <i class="fas fa-sign-out-alt w-5"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content p-8">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Carga Masiva de Estudiantes</h2>
                <p class="text-slate-500 text-sm">Sube listados completos asignando sus respectivos salones de clase</p>
            </div>
            
            <form action="subir_alumnos.php" method="POST" onsubmit="return confirm('⚠️ ¿Estás seguro de que quieres eliminar a TODOS los estudiantes actuales del sistema? Esto no borrará las notas ni los profesores.');">
                <button type="submit" name="vaciar_base" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-red-100 border border-red-200 transition-all flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Vaciar Todos los Estudiantes
                </button>
            </form>
        </header>

        <div class="max-w-3xl bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
            
            <?php if (!empty($mensaje)): ?>
                <div class="p-4 mb-6 rounded-xl text-sm <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="mb-8 bg-slate-50 p-5 rounded-xl border border-slate-100">
                <h4 class="font-bold text-slate-700 mb-2"><i class="fas fa-info-circle text-blue-500 mr-2"></i>Nueva Estructura de Archivo</h4>
                <p class="text-sm text-slate-600 mb-2">Tu Excel ahora debe contener 4 columnas obligatorias en la primera fila:</p>
                <span class="bg-slate-200 text-slate-800 px-2 py-1 rounded font-mono text-xs font-bold block w-fit">nombre, correo, contrasena, grado</span>
                <p class="text-xs text-slate-400 mt-2">En la columna 'grado' escribe solo el número (2, 3, 4 o 5) según el salón del alumno.</p>
            </div>

            <form action="subir_alumnos.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center hover:border-blue-400 transition-all bg-slate-50/50">
                    <i class="fas fa-file-excel text-4xl text-emerald-600 mb-3"></i>
                    <p class="text-slate-700 font-medium mb-1">Selecciona tu archivo .CSV</p>
                    <input type="file" name="archivo_excel" accept=".csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 max-w-xs mx-auto">
                </div>

                <button type="submit" name="importar" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-cloud-upload-alt"></i> Procesar e Inscribir por Salones
                </button>
            </form>
        </div>
    </main>

</body>
</html>