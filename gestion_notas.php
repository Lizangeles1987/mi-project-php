<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capturamos el grado que el usuario elija (Por defecto 5)
$grado_seleccionado = isset($_REQUEST['grado']) ? $_REQUEST['grado'] : '5';
$materia_seleccionada = isset($_REQUEST['materia']) ? $_REQUEST['materia'] : '';

$mensaje = "";
$tipo_alerta = "";

// GUARDAR NOTAS
if (isset($_POST['guardar_planilla'])) {
    $materia_form = $_POST['materia_oculta'];
    $notas = isset($_POST['notas']) ? $_POST['notas'] : [];
    $logros = isset($_POST['logros']) ? $_POST['logros'] : [];

    if (!empty($materia_form) && !empty($notas)) {
        try {
            $sqlIns = "INSERT INTO calificaciones (id_usuario, materia, logro, nota) VALUES (?, ?, ?, ?)";
            $stmtIns = $pdo->prepare($sqlIns);

            foreach ($notas as $id_estudiante => $nota_valor) {
                $logro_valor = isset($logros[$id_estudiante]) ? trim($logros[$id_estudiante]) : '';
                $nota_float = floatval($nota_valor);

                if ($nota_float > 0) {
                    $stmtIns->execute([$id_estudiante, $materia_form, $logro_valor, $nota_float]);
                }
            }
            $mensaje = "💾 Calificaciones guardadas exitosamente.";
            $tipo_alerta = "success";
        } catch (PDOException $e) {
            $mensaje = "❌ Error: " . $e->getMessage();
            $tipo_alerta = "error";
        }
    }
}

// LEER ESTUDIANTES DE LA BASE DE DATOS
try {
    $stmtEstud = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE rol = 'estudiante' AND grado = ? ORDER BY nombre ASC");
    $stmtEstud->execute([$grado_seleccionado]);
    $alumnos_filtrados = $stmtEstud->fetchAll(PDO::FETCH_ASSOC);

    $stmtMat = $pdo->query("SELECT nombre FROM materias ORDER BY nombre ASC");
    $materias_sistema = $stmtMat->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($materia_seleccionada) && !empty($materias_sistema)) {
        $materia_seleccionada = $materias_sistema[0]['nombre'];
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla Escolar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex">

    <!-- BARRA LATERAL -->
    <aside class="bg-slate-900 text-slate-400 p-6 min-h-screen w-[260px] shrink-0">
        <h2 class="text-white text-2xl font-bold italic mb-10 text-blue-400">Aula Primaria</h2>
        <nav class="space-y-2">
            <a href="cursos.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all"><i class="fas fa-th-large w-5"></i> Mis Cursos</a>
            <a href="gestion_notes.php" class="flex items-center gap-3 p-3 rounded-xl bg-blue-600 text-white font-semibold shadow-md"><i class="fas fa-graduation-cap w-5"></i> Notas y Logros</a>
            <a href="gestion_materias.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all"><i class="fas fa-book w-5"></i> Administrar Materias</a>
            <div class="pt-8 mt-8 border-t border-slate-800">
                <a href="logout.php" class="flex items-center gap-3 p-3 rounded-xl text-red-400 hover:bg-red-900/10"><i class="fas fa-sign-out-alt w-5"></i> Cerrar Sesión</a>
            </div>
        </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 p-8">
        <header class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Planilla de Notas y Logros</h2>
                <p class="text-slate-500 text-sm">Digita las calificaciones y las observaciones por asignatura</p>
            </div>
            <span class="bg-slate-900 text-white text-xs font-bold px-3 py-1.5 rounded-full">Periodo 1</span>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div class="p-4 mb-6 rounded-xl text-sm font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- FORMULARIO DE FILTRADO -->
        <form method="GET" action="gestion_notas.php" id="form_filtros" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Seleccionar Grado</label>
                <select name="grado" onchange="document.getElementById('form_filtros').submit();" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none text-slate-700 font-medium">
                    <?php for($g=1; $g<=5; $g++): ?>
                        <option value="<?php echo $g; ?>" <?php echo ($grado_seleccionado == $g) ? 'selected' : ''; ?>><?php echo $g; ?>° de Primaria</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Asignatura / Área</label>
                <select name="materia" onchange="document.getElementById('form_filtros').submit();" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none text-slate-700 font-medium">
                    <?php foreach($materias_sistema as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['nombre']); ?>" <?php echo ($materia_seleccionada == $m['nombre']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <!-- FORMULARIO DE TABLA MASIVA -->
        <form action="gestion_notas.php" method="POST">
            <input type="hidden" name="materia_oculta" value="<?php echo htmlspecialchars($materia_seleccionada); ?>">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white text-xs uppercase font-semibold">
                            <th class="p-4 w-16 text-center">ID</th>
                            <th class="p-4">Estudiante</th>
                            <th class="p-4 w-32 text-center">Nota (1.0 - 10.0)</th>
                            <th class="p-4">Descripción del Logro Alcanzado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                        <?php if (empty($alumnos_filtrados)): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400 italic">No hay alumnos registrados en el grado <?php echo htmlspecialchars($grado_seleccionado); ?>°.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alumnos_filtrados as $index => $al): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-4 text-center font-mono text-slate-400"><?php echo $index + 1; ?></td>
                                    <td class="p-4 font-bold text-slate-700"><?php echo htmlspecialchars($al['nombre']); ?></td>
                                    <td class="p-4">
                                        <input type="number" name="notes[<?php echo $al['id']; ?>]" step="0.1" min="1.0" max="10.0" placeholder="0.0" class="w-full text-center p-2 border border-slate-200 rounded-lg font-bold bg-slate-50">
                                    </td>
                                    <td class="p-4">
                                        <input type="text" name="logros[<?php echo $al['id']; ?>]" placeholder="Escribe el logro..." class="w-full p-2 border border-slate-200 rounded-lg bg-slate-50 text-xs">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($alumnos_filtrados)): ?>
                <div class="flex justify-end">
                    <button type="submit" name="guardar_planilla" class="bg-blue-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-blue-700 shadow-md">
                        <i class="fas fa-save"></i> Guardar Planilla Actual
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </main>

</body>
</html>