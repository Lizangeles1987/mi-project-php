<?php
// 1. Mantenemos tu config.php original que evita los bucles y maneja la sesión
include 'config.php';

// 2. Validación de seguridad unificada para tu correo o rol
$rol_verificar = isset($_SESSION['rol']) ? trim($_SESSION['rol']) : '';
$email_verificar = isset($_SESSION['email']) ? trim($_SESSION['email']) : '';

if ($rol_verificar !== 'admin' && $rol_verificar !== 'administrador' && $email_verificar !== 'admin@aula.com') {
    header("Location: login.php");
    exit();
}

$mensaje_exito = "";
$mensaje_error = "";

// 3. PROCESAR FORMULARIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASO A: REGISTRO MANUAL DE UN ALUMNO
    if (isset($_POST['registro_manual'])) {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $grado = intval($_POST['grado_destino']);
        $anio = intval($_POST['anio_lectivo']);

        if (!empty($nombre)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, grado, anio_lectivo) VALUES (?, ?, ?, 'alumno', ?, ?)");
                $stmt->execute([$nombre, $email, $password, $grado, $anio]);
                $mensaje_exito = "¡Estudiante <strong>$nombre</strong> matriculado manualmente con éxito para el año $anio!";
            } catch (PDOException $e) {
                $mensaje_error = "Error en registro manual: " . $e->getMessage();
            }
        }
    }
    
    // CASO B: SUBIR ARCHIVO CSV (EXCEL)
    if (isset($_POST['registro_csv']) && isset($_FILES['archivo_excel'])) {
        $file_name = $_FILES['archivo_excel']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if ($file_ext === 'csv') {
            $file_tmp = $_FILES['archivo_excel']['tmp_name'];
            
            if (($handle = fopen($file_tmp, "r")) !== FALSE) {
                $fila = 0;
                $alumnos_importados = 0;
                $grado_alumno = intval($_POST['grado_destino']);
                $anio_lectivo = intval($_POST['anio_lectivo']);

                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, grado, anio_lectivo) VALUES (?, ?, '12345', 'alumno', ?, ?)");

                    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                        $fila++;
                        if ($fila == 1) continue; // Saltar cabecera

                        $nombre_alumno = isset($data[0]) ? trim($data[0]) : '';
                        $email_alumno = isset($data[1]) ? trim($data[1]) : '';

                        if (!empty($nombre_alumno)) {
                            $stmt->execute([$nombre_alumno, $email_alumno, $grado_alumno, $anio_lectivo]);
                            $alumnos_importados++;
                        }
                    }
                    fclose($handle);
                    $pdo->commit();
                    $mensaje_exito = "¡Éxito! Se han matriculado masivamente <strong>$alumnos_importados</strong> estudiantes para el ciclo $anio_lectivo.";
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $mensaje_error = "Error al procesar archivo: " . $e->getMessage();
                }
            } else {
                $mensaje_error = "No se pudo abrir el archivo temporal.";
            }
        } else {
            $mensaje_error = "Por favor, sube un archivo con extensión .csv válido.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matrícula de Alumnos - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto h-screen">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800">Módulo de Matrículas Escalable</h2>
            <p class="text-slate-500 text-sm">Registra alumnos uno a uno o de forma masiva asignándoles su periodo escolar.</p>
        </header>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="p-4 mb-6 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm font-medium">
                🎉 <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_error)): ?>
            <div class="p-4 mb-6 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm font-medium">
                ❌ <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
                <h3 class="font-bold text-lg text-slate-800 mb-4"><i class="fas fa-keyboard text-blue-600 mr-2"></i>Matrícula Manual</h3>
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="registro_manual" value="1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Nombre del Estudiante</label>
                        <input type="text" name="nombre" required placeholder="Ej. Juan Pérez" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Correo de Acceso (Opcional)</label>
                        <input type="email" name="email" placeholder="juan@aula.com" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Contraseña</label>
                        <input type="text" name="password" value="12345" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Grado Destino</label>
                        <select name="grado_destino" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none">
                            <option value="1">Primero (1°)</option>
                            <option value="2" selected>Segundo (2°)</option>
                            <option value="3">Tercero (3°)</option>
                            <option value="4">Cuarto (4°)</option>
                            <option value="5">Quinto (5°)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Año Lectivo</label>
                        <select name="anio_lectivo" class="w-full p-2.5 bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 rounded-xl text-sm">
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-blue-700 transition-all">Registrar Alumno</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 lg:col-span-2 h-fit">
                <h3 class="font-bold text-lg text-slate-800 mb-4"><i class="fas fa-file-excel text-emerald-600 mr-2"></i>Carga Masiva desde Excel (.csv)</h3>
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="registro_csv" value="1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Grado Colectivo</label>
                            <select name="grado_destino" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none">
                                <option value="2" selected>Segundo de Primaria (2°)</option>
                                <option value="3">Tercero de Primaria (3°)</option>
                                <option value="4">Cuarto de Primaria (4°)</option>
                                <option value="5">Quinto de Primaria (5°)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Año de esta lista</label>
                            <select name="anio_lectivo" class="w-full p-2.5 bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 rounded-xl text-sm">
                                <option value="2026">Año 2026</option>
                                <option value="2027">Año 2027</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center bg-slate-50 hover:bg-slate-100/50 transition-all cursor-pointer relative">
                        <input type="file" name="archivo_excel" required accept=".csv" class="absolute inset-0 opacity-0 cursor-pointer">
                        <div class="text-slate-400 space-y-2">
                            <i class="fas fa-file-csv text-4xl text-emerald-600"></i>
                            <p class="text-sm font-semibold text-slate-700">Selecciona tu archivo CSV</p>
                            <p class="text-xs">Columnas: Nombre; Correo</p>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-slate-800 transition-all">Importar Lista Completa</button>
                </form>
            </div>

        </div>
    </main>

</body>
</html>