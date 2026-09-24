<?php
include 'config.php'; 

// 1. CONTROL DE SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) && !isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

// Variables para tu sidebar.php
$nombre_usuario = isset($_SESSION['nombre']) ? trim($_SESSION['nombre']) : 'Docente';
$rol_actual = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : 'profesor';

// Capturar filtro de búsqueda si el profesor escribe algo
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$notas_logros = [];
try {
    // Si el profesor busca un alumno, filtramos; si no, trae todo
    if (!empty($busqueda)) {
        $stmt = $pdo->prepare("SELECT id, nombre_materia, periodo, nombre, nota, descripcion FROM logros_academicos WHERE nombre LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$busqueda%"]);
    } else {
        $stmt = $pdo->query("SELECT id, nombre_materia, periodo, nombre, nota, descripcion FROM logros_academicos ORDER BY id DESC");
    }
    $notas_logros = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar las notas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Notas y Logros</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="w-full bg-white p-8 rounded-[30px] shadow-sm border border-slate-200">
            
            <div class="mb-6">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Historial de Notas y Logros</h2>
                <p class="text-xs text-slate-400 mt-1">Aquí puedes supervisar todas las calificaciones que has guardado en el sistema.</p>
            </div>

            <div class="mb-6 bg-slate-50 border border-slate-200 p-4 rounded-2xl max-w-md">
                <form method="GET" action="mis_notas.php" class="flex gap-2">
                    <input type="text" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar alumno por nombre..." class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 rounded-xl transition-all">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($busqueda)): ?>
                        <a href="mis_notas.php" class="bg-slate-200 text-slate-600 text-xs font-bold px-3 rounded-xl flex items-center justify-center hover:bg-slate-300">
                            Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm text-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                            <th class="p-4">Estudiante</th>
                            <th class="p-4 w-32">Asignatura</th>
                            <th class="p-4 w-24 text-center">Periodo</th>
                            <th class="p-4 w-24 text-center">Nota Final</th>
                            <th class="p-4">Logro Descriptivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                        <?php if (empty($notas_logros)): ?>
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-400 italic font-medium">
                                    <div class="text-slate-300 text-3xl mb-2"><i class="fas fa-folder-open"></i></div>
                                    No se encontraron registros de calificaciones en este momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notas_logros as $reg): 
                                // Color dinámico para la nota (Rojo si pierde, Verde si aprueba)
                                $color_nota = ($reg['nota'] >= 6.0) ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-all">
                                    <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($reg['nombre']); ?></td>
                                    <td class="p-4 font-semibold text-slate-500"><?php echo htmlspecialchars($reg['nombre_materia']); ?></td>
                                    <td class="p-4 text-center font-mono font-bold text-slate-400">P<?php echo $reg['periodo']; ?></td>
                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg font-black font-mono <?php echo $color_nota; ?>">
                                            <?php echo number_format($reg['nota'], 1); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-slate-500 max-w-xs truncate" title="<?php echo htmlspecialchars($reg['descripcion']); ?>">
                                        <?php echo htmlspecialchars($reg['descripcion']); ?>
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