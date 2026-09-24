<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validar inicio de sesión
if (!isset($_SESSION['id']) && !isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

// 2. Encabezados para evitar la caché del navegador
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$id_usuario_sesion = intval($_SESSION['id']);
$email_actual      = isset($_SESSION['email']) ? trim($_SESSION['email']) : '';
$nombre_usuario    = isset($_SESSION['nombre']) ? trim($_SESSION['nombre']) : '';
$rol_actual        = isset($_SESSION['rol']) ? trim($_SESSION['rol']) : 'estudiante';

// Condición absoluta para saber si eres el administrador
$es_admin = ($email_actual === 'admin@aula.com' || $nombre_usuario === 'Usuario Prueba' || $rol_actual === 'admin');

try {
    // LOGICA INTELIGENTE DE GRADOS SEGÚN EL ROL
    if ($es_admin) {
        // Si es administrador, le mostramos TODOS los grados existentes en el colegio
        $stmtGrados = $pdo->query("SELECT id, numero, nombre FROM grados_admin ORDER BY numero ASC");
        $grados_asignados = $stmtGrados->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Si es profesor, solo le traemos los grados que tiene en su carga académica
        $stmtGrados = $pdo->prepare("SELECT DISTINCT g.id, g.numero, g.nombre 
                                     FROM carga_academica c
                                     JOIN grados_admin g ON c.id_grado = g.id
                                     WHERE c.id_profesor = ?
                                     ORDER BY g.numero ASC");
        $stmtGrados->execute([$id_usuario_sesion]);
        $grados_asignados = $stmtGrados->fetchAll(PDO::FETCH_ASSOC);
    }

    $grado_activo = 0;
    $nombre_grado_activo = "";

    if (isset($_POST['grado_id_post'])) {
        $grado_activo = intval($_POST['grado_id_post']);
    } elseif (!empty($grados_asignados)) {
        $grado_activo = intval($grados_asignados[0]['id']);
    }

    foreach ($grados_asignados as $ga) {
        if ($ga['id'] == $grado_activo) {
            $nombre_grado_activo = $ga['numero'] . "° " . $ga['nombre'];
            break;
        }
    }

    $estudiantes = [];
    if ($grado_activo > 0) {
        // Buscar estudiantes asignados a este ID de grado
        $stmtAlumnos = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE rol = 'estudiante' AND grado = ? ORDER BY nombre ASC");
        $stmtAlumnos->execute([$grado_activo]);
        $estudiantes = $stmtAlumnos->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Grados - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans min-h-screen flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Control de Grados</h1>
                <p class="text-slate-500 text-sm mt-1">Cursos asignados de primaria para la gestión de alumnos.</p>
            </div>
            
            <?php if ($grado_activo > 0): ?>
                <a href="calificar_planilla.php?grado=<?php echo $grado_activo; ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-md">
                    <i class="fas fa-bolt mr-1"></i> Calificación Masiva - <?php echo htmlspecialchars($nombre_grado_activo); ?>
                </a>
            <?php endif; ?>
        </header>

        <div class="flex flex-wrap gap-2 mb-8">
            <?php if (!empty($grados_asignados)): ?>
                <?php foreach ($grados_asignados as $g): 
                    $es_activo = ($grado_activo == $g['id']);
                    $clase_boton = $es_activo 
                        ? "bg-blue-600 text-white border-blue-600 shadow-md scale-105 font-bold" 
                        : "bg-white text-slate-600 border-slate-200 hover:bg-slate-50 font-semibold";
                ?>
                    <form method="POST" action="" class="inline-block">
                        <input type="hidden" name="grado_id_post" value="<?php echo $g['id']; ?>">
                        <button type="submit" class="<?php echo $clase_boton; ?> px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition-all">
                            <i class="fas fa-user-graduate text-xs <?php echo $es_activo ? 'text-white' : 'text-slate-400'; ?>"></i>
                            <?php echo $g['numero']; ?>° <?php echo htmlspecialchars($g['nombre']); ?>
                        </button>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold">
                    ⚠️ No tienes asignaciones académicas registradas todavía.
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-700 text-sm">
                    Estudiantes en <span class="text-blue-600"><?php echo htmlspecialchars($nombre_grado_activo ?: 'Ningún grado seleccionado'); ?></span>
                </h3>
                <span class="bg-blue-50 text-blue-700 text-xs font-black px-2.5 py-1 rounded-lg">
                    Total: <?php echo count($estudiantes); ?> Alumnos
                </span>
            </div>

            <div class="overflow-x-auto text-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="p-4 w-16 text-center">ID</th>
                            <th class="p-4">Nombre del Alumno</th>
                            <th class="p-4">Correo Electrónico</th>
                            <th class="p-4 pr-6 text-center w-72">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                        <?php if (empty($estudiantes)): ?>
                            <tr>
                                <td colspan="4" class="p-12 text-center text-slate-400 italic font-medium">
                                    <div class="text-slate-300 text-3xl mb-2"><i class="fas fa-folder-open"></i></div>
                                    No hay estudiantes registrados en este grado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($estudiantes as $est): ?>
                                <tr class="hover:bg-slate-50/70 transition-all">
                                    <td class="p-4 text-center font-mono text-slate-400"><?php echo $est['id']; ?></td>
                                    <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($est['nombre']); ?></td>
                                    <td class="p-4 font-mono text-slate-500"><?php echo htmlspecialchars($est['email']); ?></td>
                                    <td class="p-4 pr-6 flex items-center justify-center gap-2">
                                        <a href="cargar_nota.php?id=<?php echo $est['id']; ?>" class="inline-flex items-center justify-center bg-blue-600 text-white font-bold px-3 py-2 rounded-lg hover:bg-blue-700 shadow-sm transition-all gap-1 text-[11px] h-8">
                                            <i class="fas fa-edit text-[10px]"></i> Notas
                                        </a>
                                        
                                        <!-- SECCIÓN DE BOLETINES COMPACTA POR PERIODO -->
                                        <div class="flex items-center gap-0.5 bg-slate-100 p-1 rounded-lg border border-slate-200">
                                            <span class="text-[9px] font-bold text-slate-400 px-1 uppercase">Boletín:</span>
                                            <?php for ($p = 1; $p <= 4; $p++): ?>
                                                <a href="boletin.php?id=<?php echo $est['id']; ?>&periodo=<?php echo $p; ?>" 
                                                   target="_blank" 
                                                   class="px-2 py-1 rounded font-bold text-[10px] bg-white hover:bg-slate-900 text-slate-700 hover:text-white transition-all shadow-sm border border-slate-200" 
                                                   title="Ver Periodo <?php echo $p; ?>">
                                                    P<?php echo $p; ?>
                                                </a>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>