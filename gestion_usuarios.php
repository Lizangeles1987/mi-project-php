<?php
include 'config.php';

// Iniciar sesión de forma segura si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PROTECCIÓN DE SEGURIDAD: Si no es admin, lo saca al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$tipo_alerta = "";

// PROCESAR FORMULARIOS (M MOTOR PDO)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASO 1: REGISTRAR NUEVO DOCENTE
    if (isset($_POST['crear_profesor'])) {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']); // Puedes usar password_hash en producción

        if (!empty($nombre) && !empty($email) && !empty($password)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'profesor')");
                $stmt->execute([$nombre, $email, $password]);
                
                header("Location: gestion_usuarios.php?success=1");
                exit();
            } catch (PDOException $e) {
                $mensaje = "Error al registrar profesor: " . $e->getMessage();
                $tipo_alerta = "error";
            }
        }
    }

    // CASO 2: CREAR ASIGNACIÓN ACADÉMICA MULTIPERIODO
    if (isset($_POST['asignar_materia'])) {
        $docente_id = intval($_POST['docente_id']);
        $materia_id = intval($_POST['materia_id']);
        $grado_id = intval($_POST['grado_id']);
        $anio_lectivo = intval($_POST['anio_lectivo']);

        if ($docente_id > 0 && $materia_id > 0 && $anio_lectivo > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO asignaciones_docente (docente_id, materia_id, grado_id, anio_lectivo) VALUES (?, ?, ?, ?)");
                $stmt->execute([$docente_id, $materia_id, $grado_id, $anio_lectivo]);
                
                $mensaje = "¡Carga académica asignada con éxito para el periodo " . $anio_lectivo . "!";
                $tipo_alerta = "success";
            } catch (PDOException $e) {
                $mensaje = "Error al guardar asignación: " . $e->getMessage();
                $tipo_alerta = "error";
            }
        }
    }
}

// CONSULTAS DATA PARA CARGAR TABLAS Y SELECTORES (PDO)
try {
    // 1. Profesores actuales
    $stmt_profesores = $pdo->query("SELECT * FROM usuarios WHERE rol = 'profesor'");
    $profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);

    // 2. Materias registradas
    $stmt_materias = $pdo->query("SELECT id, nombre_materia FROM materias");
    $listado_materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);

    // 3. Listado de asignaciones actuales con nombres legibles (JOIN)
    $query_carga = "SELECT ad.anio_lectivo, ad.grado_id, u.nombre AS profesor_nombre, m.nombre_materia 
                    FROM asignaciones_docente ad
                    JOIN usuarios u ON ad.docente_id = u.id
                    JOIN materias m ON ad.materia_id = m.id
                    ORDER BY ad.anio_lectivo DESC, ad.grado_id ASC";
    $listado_carga = $pdo->query($query_carga)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $profesores = [];
    $listado_materias = [];
    $listado_carga = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Profesores</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background-color: #0f172a; min-height: 100vh; width: 260px; }
        .main-content { background-color: #f8fafc; flex: 1; }
    </style>
</head>
<body class="bg-slate-50 flex">

    <?php include 'sidebar.php'; ?>

    <main class="main-content p-8 overflow-y-auto h-screen">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800">Control de Personal y Carga Académica</h2>
            <p class="text-slate-500 text-sm">Panel exclusivo del Administrador para gestionar docentes, materias y años lectivos.</p>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div class="p-4 mb-6 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm font-medium">
                ✅ ¡Profesor registrado correctamente en el sistema!
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje)): ?>
            <div class="p-4 mb-6 rounded-xl text-sm font-medium <?php echo ($tipo_alerta === 'success') ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-700 border border-red-100'; ?>">
                <?php echo ($tipo_alerta === 'success') ? '🎉' : '❌'; ?> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
                <h3 class="font-bold text-lg text-slate-800 mb-4"><i class="fas fa-user-plus mr-2 text-blue-600"></i>Registrar Nuevo Docente</h3>
                
                <form action="gestion_usuarios.php" method="POST" class="space-y-4">
                    <input type="hidden" name="crear_profesor" value="1">
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Nombre Completo</label>
                        <input type="text" name="nombre" required placeholder="Ej. Marta Gómez" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Correo Electrónico</label>
                        <input type="email" name="email" required placeholder="marta@aula.com" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Contraseña de Acceso</label>
                        <input type="text" name="password" required placeholder="Clave inicial" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-blue-700 transition-all">
                        Crear Cuenta de Profesor
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
                <h3 class="font-bold text-lg text-slate-800 mb-4"><i class="fas fa-calendar-alt mr-2 text-emerald-600"></i>Asignación Académica</h3>
                
                <form action="gestion_usuarios.php" method="POST" class="space-y-4">
                    <input type="hidden" name="assignar_materia" value="1">
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">1. Seleccionar Profesor</label>
                        <select name="docente_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                            <option value="">-- Seleccione un docente --</option>
                            <?php foreach ($profesores as $profe): ?>
                                <option value="<?php echo $profe['id']; ?>"><?php echo htmlspecialchars($profe['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">2. Asignar Materia</label>
                        <select name="materia_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                            <option value="">-- Seleccione materia --</option>
                            <?php foreach ($listado_materias as $mat): ?>
                                <option value="<?php echo $mat['id']; ?>"><?php echo htmlspecialchars($mat['nombre_materia']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">3. Grado</label>
                            <select name="grado_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                                <option value="1">1° Primaria</option>
                                <option value="2">2° Primaria</option>
                                <option value="3">3° Primaria</option>
                                <option value="4">4° Primaria</option>
                                <option value="5">5° Primaria</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">4. Año Lectivo</label>
                            <select name="anio_lectivo" required class="w-full p-2.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-sm font-bold outline-none">
                                <option value="2026" selected>2026</option>
                                <option value="2027">2027</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="asignar_materia" class="w-full bg-emerald-600 text-white font-bold py-2.5 rounded-xl text-sm hover:bg-emerald-700 transition-all">
                        Enlazar Año y Materia
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
                <h3 class="font-bold text-lg text-slate-800 mb-4"><i class="fas fa-users mr-2 text-slate-600"></i>Cuentas de Docentes</h3>
                <div class="overflow-x-auto max-h-64 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase font-semibold">
                                <th class="pb-2">Nombre</th>
                                <th class="pb-2">Correo</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                            <?php if (count($profesores) > 0): ?>
                                <?php foreach($profesores as $p): ?>
                                    <tr>
                                        <td class="py-2 font-medium text-slate-800"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                        <td class="py-2 text-slate-500"><?php echo htmlspecialchars($p['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="py-4 text-center text-slate-400">Sin docentes.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 w-full">
            <h3 class="font-bold text-lg text-slate-800 mb-4"><i class="fas fa-network-wired mr-2 text-indigo-600"></i>Historial de Distribución Académica (Multiperiodo)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 text-xs uppercase font-semibold">
                            <th class="pb-3">Año Lectivo</th>
                            <th class="pb-3">Docente</th>
                            <th class="pb-3">Asignatura Asignada</th>
                            <th class="pb-3">Grado Destino</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                        <?php if (count($listado_carga) > 0): ?>
                            <?php foreach($listado_carga as $carga): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 font-bold text-emerald-600"><?php echo $carga['anio_lectivo']; ?></td>
                                    <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($carga['profesor_nombre']); ?></td>
                                    <td class="py-3 text-slate-600"><?php echo htmlspecialchars($carga['nombre_materia']); ?></td>
                                    <td class="py-3"><span class="bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded-md font-medium"><?php echo $carga['grado_id']; ?>° Primaria</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">No se han realizado asignaciones académicas para ningún periodo aún.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>