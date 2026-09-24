<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Blindaje de seguridad: Solo alumnos autorizados
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: login.php");
    exit();
}

// Capturamos las variables de la sesión activa
$id_alumno = intval($_SESSION['id']);
$alumno_nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Estudiante';

// Capturamos el periodo que el alumno selecciona (por defecto el Periodo 1)
$periodo_seleccionado = isset($_GET['periodo']) ? intval($_GET['periodo']) : 1;

$calificaciones = [];
$grado = "2"; // Por defecto 2° según tu interfaz

try {
    // 2. Traemos el grado y nombre real desde la tabla usuarios
    $stmtUser = $pdo->prepare("SELECT nombre, grado FROM usuarios WHERE id = ?");
    $stmtUser->execute([$id_alumno]);
    $user_data = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
    if ($user_data) {
        $grado = $user_data['grado'];
        $alumno_nombre = trim($user_data['nombre']);
    }

    // 3. CONSULTA CON FILTRO DE PERIODO REAL
    // Buscamos tus notas asociadas al ID de estudiante y al periodo seleccionado en el menú superior
    $query = "SELECT materia AS materia_nombre, nota, logro AS logro_texto 
              FROM calificaciones 
              WHERE id_usuario = ? AND periodo = ?
              ORDER BY materia ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_alumno, $periodo_seleccionado]);
    $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Evitamos pantallas en blanco ante cualquier imprevisto técnico
    $calificaciones = [];
}

// Función para calcular la escala institucional de desempeño
function calcularDesempenoEstudiante($nota) {
    $n = floatval($nota);
    if ($n >= 9.0) return ['texto' => 'Superior', 'clase' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'];
    if ($n >= 7.5) return ['texto' => 'Alto', 'clase' => 'bg-blue-50 text-blue-700 border border-blue-200'];
    if ($n >= 6.0) return ['texto' => 'Básico', 'clase' => 'bg-amber-50 text-amber-700 border border-amber-200'];
    return ['texto' => 'Bajo', 'clase' => 'bg-red-50 text-red-700 border border-red-200'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Aula Virtual - Notas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen font-sans flex flex-col items-center p-4 md:p-8">

    <div class="w-full max-w-4xl bg-white p-6 md:p-8 rounded-[30px] shadow-sm border border-slate-200/80">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-100 pb-6 mb-6 gap-4">
            <div>
                <span class="text-[10px] bg-blue-50 text-blue-600 font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">Portal del Estudiante</span>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight mt-1">¡Hola, <?php echo htmlspecialchars($alumno_nombre); ?>!</h1>
                <p class="text-xs text-slate-400 font-medium">Aquí puedes consultar tus valoraciones y logros académicos en tiempo real.</p>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl">
                <label class="text-xs font-bold text-slate-500">Periodo Académico:</label>
                <select onchange="window.location.href=this.value;" class="bg-white border border-slate-200 rounded-lg p-1.5 text-xs font-bold text-blue-600 focus:outline-none cursor-pointer">
                    <?php for($p=1; $p<=4; $p++): ?>
                        <option value="estudiante_dashboard.php?periodo=<?php echo $p; ?>" <?php echo ($periodo_seleccionado == $p) ? 'selected' : ''; ?>>Periodo <?php echo $p; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-wrap justify-between items-center gap-4 mb-6 text-xs font-semibold text-slate-500">
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Curso Actual</p>
                <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($grado); ?>° de Primaria</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Institución</p>
                <p class="text-sm font-bold text-slate-800">I.E. Aula Primaria</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="generar_boletin.php?periodo=<?php echo $periodo_seleccionado; ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-print"></i> Descargar Boletín
                </a>
                
                <a href="logout.php" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-1.5">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">Mis Calificaciones del Periodo <?php echo $periodo_seleccionado; ?></h3>
            
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 text-xs shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                            <th class="p-4 w-1/4">Asignatura</th>
                            <th class="p-4 text-center w-20">Nota</th>
                            <th class="p-4 text-center w-28">Desempeño</th>
                            <th class="p-4">Logros Alcanzados</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                        <?php if (empty($calificaciones)): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400 italic bg-slate-50/20">
                                    👋 Tus profesores aún no han registrado calificaciones para ti en el Periodo <?php echo $periodo_seleccionado; ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($calificaciones as $reg): 
                                $desempeno = calcularDesempenoEstudiante($reg['nota']);
                            ?>
                                <tr class="align-top hover:bg-slate-50/30 transition-all">
                                    <td class="p-4 font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($reg['materia_nombre'] ?? 'Asignatura'); ?></td>
                                    <td class="p-4 text-center font-mono font-black text-base text-slate-900 bg-slate-50/50">
                                        <?php echo number_format($reg['nota'] ?? 0, 1); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="<?php echo $desempeno['clase']; ?> px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider text-[10px]">
                                            <?php echo $desempeno['texto']; ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-slate-500 leading-relaxed text-justify whitespace-pre-line"><?php echo htmlspecialchars($reg['logro_texto'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>