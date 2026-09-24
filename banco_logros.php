<?php
include 'config.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) && !isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

$nombre_usuario = isset($_SESSION['nombre']) ? trim($_SESSION['nombre']) : 'Docente';
$rol_actual = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : 'profesor';

$mensaje = "";
$tipo_alerta = "";

$materias_db = [];
try {
    $stmtMat = $pdo->query("SELECT nombre FROM materias ORDER BY nombre ASC");
    $materias_db = $stmtMat->fetchAll();
} catch (PDOException $e) {
    $materias_db = [['nombre' => 'Matemáticas'], ['nombre' => 'Español'], ['nombre' => 'Ciencias']];
}

if (isset($_POST['guardar_logro'])) {
    $grado = intval($_POST['grado']);
    $periodo = intval($_POST['periodo']);
    $materia = trim($_POST['materia']);
    $descripcion = trim($_POST['descripcion']);

    if ($grado > 0 && $periodo > 0 && !empty($materia) && !empty($descripcion)) {
        try {
            // Aseguramos que exista la columna codigo por si acaso
            try { $pdo->query("SELECT codigo FROM logros LIMIT 1"); } 
            catch (Exception $e) { $pdo->query("ALTER TABLE logros ADD COLUMN codigo VARCHAR(50) NULL"); }

            // 1. Insertamos el logro normal
            $stmt = $pdo->prepare("INSERT INTO logros (grado, materia, descripcion, periodo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$grado, $materia, $descripcion, $periodo]);
            
            // 2. Tomamos el ID que se acaba de crear para armar el código automático
            $nuevo_id = $pdo->lastInsertId();
            $codigo_automatico = "LOG-" . $nuevo_id;

            // 3. Actualizamos el registro con su nuevo código automático
            $stmtUpdate = $pdo->prepare("UPDATE logros SET codigo = ? WHERE id = ?");
            $stmtUpdate->execute([$codigo_automatico, $nuevo_id]);

            $mensaje = "✨ ¡Logro guardado con éxito! Dile al profesor que su código es: <span class='bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-black'>$codigo_automatico</span>";
            $tipo_alerta = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al guardar el logro: " . $e->getMessage();
            $tipo_alerta = "error";
        }
    } else {
        $mensaje = "⚠️ Rellena todos los campos obligatorios.";
        $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco de Logros</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="w-full max-w-xl bg-white p-8 rounded-[30px] shadow-sm border border-slate-200">
            
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-3">
                <a href="calificar_planilla.php" class="text-slate-400 hover:text-blue-600 text-xs font-bold transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a la Planilla
                </a>
            </div>

            <div class="mb-6">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Redactar Logros Generales</h2>
                <p class="text-xs text-slate-400 mt-1">El sistema generará un código automático al guardar.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="p-4 mb-6 rounded-2xl text-sm font-semibold <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="banco_logros.php" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Grado / Curso</label>
                        <select name="grado" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                            <option value="">-- Selecciona --</option>
                            <?php for($g=1; $g<=5; $g++): ?>
                                <option value="<?php echo $g; ?>"><?php echo $g; ?>° de Primaria</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Periodo Académico</label>
                        <select name="periodo" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                            <option value="1">Periodo 1</option>
                            <option value="2">Periodo 2</option>
                            <option value="3">Periodo 3</option>
                            <option value="4">Periodo 4</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Asignatura / Materia</label>
                    <select name="materia" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($materias_db as $mat): ?>
                            <option value="<?php echo htmlspecialchars($mat['nombre']); ?>"><?php echo htmlspecialchars($mat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Descripción del Logro</label>
                    <textarea name="descripcion" required rows="4" placeholder="Ej: Aplica correctamente las operaciones..." class="w-full p-3 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none resize-none"></textarea>
                </div>

                <button type="submit" name="guardar_logro" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                    <i class="fas fa-save mr-1"></i> Guardar en la Biblioteca
                </button>
            </form>
        </div>
    </main>

</body>
</html>