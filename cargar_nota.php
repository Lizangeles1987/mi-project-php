<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Obtener el ID del estudiante seleccionado
$id_estudiante = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Capturamos el periodo seleccionado por el profesor de forma persistente (POST o GET)
$periodo_actual = isset($_POST['periodo_registro']) ? intval($_POST['periodo_registro']) : (isset($_GET['periodo']) ? intval($_GET['periodo']) : 1);

if ($id_estudiante === 0) {
    die("Error: No se seleccionó ningún estudiante válido.");
}

$mensaje = "";
$tipo_alerta = "";

// 🌟 NUEVO: LÓGICA PARA ELIMINAR CALIFICACIÓN DIRECTAMENTE
if (isset($_GET['eliminar_id'])) {
    $id_eliminar = intval($_GET['eliminar_id']);
    try {
        $stmtDel = $pdo->prepare("DELETE FROM calificaciones WHERE id = ? AND id_usuario = ?");
        $stmtDel->execute([$id_eliminar, $id_estudiante]);
        $mensaje = "🗑️ Calificación eliminada correctamente del sistema.";
        $tipo_alerta = "success";
    } catch (PDOException $e) {
        $mensaje = "❌ Error al intentar eliminar: " . $e->getMessage();
        $tipo_alerta = "error";
    }
}

// Variables para auto-rellenar el formulario en modo edición rápida
$materia_edit = "";
$nota_edit = "";
$logro_edit = "";

// 🌟 NUEVO: LÓGICA PARA CARGAR DATOS EN EL FORMULARIO PARA EDITAR
if (isset($_GET['editar_id'])) {
    $id_editar = intval($_GET['editar_id']);
    try {
        $stmtEdit = $pdo->prepare("SELECT materia, nota, logro FROM calificaciones WHERE id = ? AND id_usuario = ?");
        $stmtEdit->execute([$id_editar, $id_estudiante]);
        $nota_a_editar = $stmtEdit->fetch(PDO::FETCH_ASSOC);
        if ($nota_a_editar) {
            $materia_edit = $nota_a_editar['materia'];
            $nota_edit    = $nota_a_editar['nota'];
            $logro_edit   = $nota_a_editar['logro'];
            $mensaje = "✏️ Datos cargados en el formulario de arriba. Modifica los campos y dale a Guardar.";
            $tipo_alerta = "success";
        }
    } catch (PDOException $e) {
        // Silencioso
    }
}

try {
    // 2. Traer los datos del estudiante
    $stmtEstud = $pdo->prepare("SELECT nombre, grado FROM usuarios WHERE id = ? AND rol = 'estudiante'");
    $stmtEstud->execute([$id_estudiante]);
    $estudiante = $stmtEstud->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        die("Error: El estudiante no existe.");
    }

    // 3. Traer las materias registradas en el sistema
    $stmtMat = $pdo->query("SELECT nombre FROM materias ORDER BY nombre ASC");
    $materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

// 4. LÓGICA DE GUARDADO O CORRECCIÓN MODULAR (INSERT O UPDATE ASOCIADO AL PERIODO)
if (isset($_POST['guardar_nota'])) {
    $materia = $_POST['materia'];
    $nota    = floatval($_POST['nota']);
    $logro   = trim($_POST['logro']);
    $periodo_registro = intval($_POST['periodo_registro']); // Captura el periodo del formulario

    if (!empty($materia) && !empty($logro) && $nota >= 1.0 && $nota <= 10.0 && $periodo_registro >= 1 && $periodo_registro <= 4) {
        // Sincronizamos para mantener la vista en el periodo donde se guardó
        $periodo_actual = $periodo_registro;
        
        try {
            // 🌟 VALIDACIÓN: Verificamos si ya tiene nota EN ESA MATERIA Y EN ESE PERIODO ESPECÍFICO
            $stmtCheck = $pdo->prepare("SELECT id FROM calificaciones WHERE id_usuario = ? AND materia = ? AND periodo = ?");
            $stmtCheck->execute([$id_estudiante, $materia, $periodo_registro]);
            $existe = $stmtCheck->fetch();

            if ($existe) {
                // SI YA EXISTE: Corregimos la nota y logro específicamente de ese periodo
                $sqlUpd = "UPDATE calificaciones SET nota = ?, logro = ? WHERE id_usuario = ? AND materia = ? AND periodo = ?";
                $stmtAction = $pdo->prepare($sqlUpd);
                $stmtAction->execute([$nota, $logro, $id_estudiante, $materia, $periodo_registro]);
                $mensaje = "🔄 ¡Calificación corregida con éxito! Se actualizó " . htmlspecialchars($materia) . " en el Periodo " . $periodo_registro;
            } else {
                // SI NO EXISTE: Insertamos la nueva calificación amarrada a su respectivo periodo
                $sqlIns = "INSERT INTO calificaciones (id_usuario, materia, logro, nota, periodo) VALUES (?, ?, ?, ?, ?)";
                $stmtAction = $pdo->prepare($sqlIns);
                $stmtAction->execute([$id_estudiante, $materia, $logro, $nota, $periodo_registro]);
                $mensaje = "✅ Calificación y logro guardados con éxito en el Periodo " . $periodo_registro;
            }
            $tipo_alerta = "success";
        } catch (PDOException $e) {
            try {
                $sqlInsGeneral = "INSERT INTO calificaciones (id_usuario, materia, logro, nota) VALUES (?, ?, ?, ?)";
                $stmtAction = $pdo->prepare($sqlInsGeneral);
                $stmtAction->execute([$id_estudiante, $materia, $logro, $nota]);
                $mensaje = "✅ Calificación registrada (Modo general sin periodos).";
                $tipo_alerta = "success";
            } catch (PDOException $err) {
                $mensaje = "❌ Error al procesar en la base de datos: " . $e->getMessage();
                $tipo_alerta = "error";
            }
        }
    } else {
        $mensaje = "⚠️ Por favor, llena todos los campos, usa una nota válida entre 1.0 y 10.0 y un periodo correcto.";
        $tipo_alerta = "error";
    }
}

// 5. CARGAR HISTORIAL FILTRADO (Traemos también el campo 'id' para poder Editar/Eliminar)
try {
    $stmtHistorial = $pdo->prepare("SELECT id, materia, nota, logro FROM calificaciones WHERE id_usuario = ? AND periodo = ? ORDER BY materia ASC");
    $stmtHistorial->execute([$id_estudiante, $periodo_actual]);
    $notas_guardadas = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $stmtHistorial = $pdo->prepare("SELECT id, materia, nota, logro FROM calificaciones WHERE id_usuario = ? ORDER BY materia ASC");
        $stmtHistorial->execute([$id_estudiante]);
        $notas_guardadas = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $err) {
        $notas_guardadas = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Calificación - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen p-6 flex flex-col items-center">

    <div class="w-full max-w-3xl bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        
        <div class="flex justify-between items-center mb-6">
            <a href="cursos.php" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-blue-600 transition-all">
                <i class="fas fa-arrow-left"></i> Volver a Cursos
            </a>
            
            <a href="boletin.php?id=<?php echo $id_estudiante; ?>&periodo=<?php echo $periodo_actual; ?>" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-800 transition-all bg-blue-50 px-3 py-1.5 rounded-lg">
                <i class="fas fa-file-invoice"></i> Ver Boletín P<?php echo $periodo_actual; ?>
            </a>
        </div>

        <header class="mb-6">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Asignar Calificación y Logro</h2>
            <p class="text-sm text-slate-500 mt-1">Estudiante: <strong class="text-slate-700"><?php echo htmlspecialchars($estudiante['nombre']); ?></strong></p>
            <span class="inline-block bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-0.5 rounded mt-1"><?php echo $estudiante['grado']; ?>° de Primaria</span>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div class="p-4 mb-6 rounded-xl text-sm font-semibold <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="cargar_nota.php?id=<?php echo $id_estudiante; ?>" method="POST" class="space-y-5">
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">1. SELECCIONAR PERIODO ACADÉMICO</label>
                <select name="periodo_registro" required onchange="window.location.href='cargar_nota.php?id=<?php echo $id_estudiante; ?>&periodo='+this.value;" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm font-bold text-blue-600 cursor-pointer">
                    <?php for($p=1; $p<=4; $p++): ?>
                        <option value="<?php echo $p; ?>" <?php echo ($periodo_actual == $p) ? 'selected' : ''; ?>>Periodo <?php echo $p; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">2. SELECCIONAR MATERIA</label>
                <select name="materia" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm font-medium text-slate-700">
                    <option value="">-- Selecciona una materia --</option>
                    <?php foreach ($materias as $mat): ?>
                        <option value="<?php echo htmlspecialchars($mat['nombre']); ?>" <?php echo ($materia_edit == $mat['nombre']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($mat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">3. NOTA NUMÉRICA (1.0 A 10.0)</label>
                <input type="number" name="nota" step="0.1" min="1.0" max="10.0" placeholder="Ej: 8.5" value="<?php echo htmlspecialchars($nota_edit); ?>" required 
                       class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 font-mono font-bold text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">4. REDACTAR EL LOGRO O DESEMPEÑO</label>
                <textarea name="logro" rows="3" placeholder="Escribe aquí el logro alcanzado por el estudiante en esta materia..." required 
                          class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm text-slate-600"><?php echo htmlspecialchars($logro_edit); ?></textarea>
            </div>

            <button type="submit" name="guardar_nota" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl hover:bg-blue-700 transition-all shadow-md flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                <i class="fas fa-save"></i> Guardar en el Sistema
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-slate-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-700"><i class="fas fa-folder-open text-blue-500 mr-2"></i>Calificaciones registradas</h3>
                <div class="flex items-center gap-1.5 bg-slate-100 rounded-lg p-1 text-[11px] font-semibold text-slate-500">
                    <span>Cambiar visualización:</span>
                    <?php for($i=1;$i<=4;$i++): ?>
                        <a href="cargar_nota.php?id=<?php echo $id_estudiante; ?>&periodo=<?php echo $i; ?>" class="px-2 py-0.5 rounded transition-all <?php echo ($periodo_actual == $i) ? 'bg-white text-blue-600 shadow-sm font-bold' : 'hover:bg-slate-200'; ?>">P<?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            
            <?php if (empty($notas_guardadas)): ?>
                <p class="text-xs text-slate-400 italic">Este estudiante no tiene calificaciones registradas aún en el Periodo <?php echo $periodo_actual; ?>.</p>
            <?php else: ?>
                <div class="overflow-hidden rounded-xl border border-slate-200 text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200">
                                <th class="p-3">Materia</th>
                                <th class="p-3 text-center">Nota</th>
                                <th class="p-3">Logro Registrado (Periodo <?php echo $periodo_actual; ?>)</th>
                                <th class="p-3 text-center w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <?php foreach ($notas_guardadas as $ng): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3 font-semibold text-slate-800"><?php echo htmlspecialchars($ng['materia']); ?></td>
                                    <td class="p-3 text-center font-mono font-bold text-blue-600 bg-blue-50/30"><?php echo number_format($ng['nota'], 1); ?></td>
                                    <td class="p-3 text-slate-500"><?php echo htmlspecialchars($ng['logro']); ?></td>
                                    <td class="p-3 text-center flex justify-center gap-1.5 mt-1">
                                        <a href="cargar_nota.php?id=<?php echo $id_estudiante; ?>&periodo=<?php echo $periodo_actual; ?>&editar_id=<?php echo $ng['id']; ?>" class="inline-flex p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-all" title="Editar nota">
                                            <i class="fas fa-edit text-[11px]"></i>
                                        </a>
                                        <a href="cargar_nota.php?id=<?php echo $id_estudiante; ?>&periodo=<?php echo $periodo_actual; ?>&eliminar_id=<?php echo $ng['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta calificación por completo?');" class="inline-flex p-1.5 bg-red-50 text-red-600 rounded hover:bg-red-100 transition-all" title="Eliminar nota">
                                            <i class="fas fa-trash-alt text-[11px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-[11px] text-slate-400 mt-3 italic"><i class="fas fa-info-circle"></i> Usa los iconos de la tabla para modificar rápidamente una nota o borrarla de la libreta escolar.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>