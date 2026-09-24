<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$rol_usuario = strtolower(trim($_SESSION['rol']));
$id_sesion = intval($_SESSION['id']);

$id_alumno = isset($_GET['id']) ? intval($_GET['id']) : 0;
$periodo_seleccionado = isset($_GET['periodo']) ? intval($_GET['periodo']) : 1;

if ($rol_usuario === 'estudiante') {
    $id_alumno = $id_sesion;
    $enlace_regreso = "estudiante_dashboard.php"; 
} else {
    $enlace_regreso = ($rol_usuario === 'admin' || $rol_usuario === 'administrador') ? 'matricula_excel.php' : 'cursos.php';
}

if ($id_alumno === 0) {
    die("ID de estudiante no válido.");
}

try {
    $stmtUser = $pdo->prepare("SELECT nombre, grado FROM usuarios WHERE id = ? AND rol = 'estudiante'");
    $stmtUser->execute([$id_alumno]);
    $alumno = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$alumno) {
        die("El estudiante no existe.");
    }

    $alumno_nombre = $alumno['nombre'];
    $grado = trim($alumno['grado']);

    // Formatear texto del grado (Evita el "de de Primaria")
    $stmtNomGrado = $pdo->prepare("SELECT numero, nombre FROM grados_admin WHERE id = ?");
    $stmtNomGrado->execute([$grado]);
    $rg = $stmtNomGrado->fetch(PDO::FETCH_ASSOC);

    if ($rg) {
        $texto_grado = $rg['numero'] . "° de " . $rg['nombre'];
    } else {
        // Limpieza si la variable grado ya contiene palabras como "Primaria" o "°"
        $grado_limpio = str_ireplace(['de primaria', 'primaria'], '', $grado);
        $texto_grado = trim($grado_limpio) . "° de Primaria";
        $texto_grado = str_replace("°°", "°", $texto_grado);
    }

    $query_notas = "SELECT materia AS materia_nombre, nota, logro AS logro_texto 
                    FROM calificaciones 
                    WHERE id_usuario = ? AND periodo = ?
                    ORDER BY materia ASC";
    $stmtNotas = $pdo->prepare($query_notas);
    $stmtNotas->execute([$id_alumno, $periodo_seleccionado]);
    $calificaciones = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

function calcularDesempeno($nota) {
    $n = floatval($nota);
    if ($n >= 9.0) return 'Superior';
    if ($n >= 7.5) return 'Alto';
    if ($n >= 6.0) return 'Básico';
    return 'Bajo';
}

$fecha_impresion = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín - <?php echo htmlspecialchars($alumno_nombre); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tabla-boletin {
            width: 100%;
            border-collapse: collapse;
            font-family: sans-serif;
            margin-top: 20px;
        }
        .tabla-boletin th {
            background-color: #111c31 !important;
            color: white !important;
            font-weight: bold;
            font-size: 13px;
            padding: 10px;
            border: 1px solid #000000;
            text-align: left;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .tabla-boletin td {
            border: 1px solid #000000;
            padding: 8px 10px;
            font-size: 13px;
            color: #000000;
            vertical-align: middle;
        }
        .text-center-recuadro {
            text-align: center;
            font-weight: bold;
        }
        @media print {
            .no-imprimir { display: none !important; }
            body { background-color: white !important; padding: 0 !important; }
            .contenedor-boletin { border: none !important; shadow: none !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 md:p-8 flex flex-col items-center">

    <!-- Barra de herramientas superior (No se imprime) -->
    <div class="no-imprimir w-full max-w-4xl bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex justify-between items-center">
        <a href="<?php echo $enlace_regreso; ?>" class="text-slate-600 hover:text-slate-900 text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver al Panel
        </a>
        <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-600">Periodo:</label>
            <select onchange="window.location.href=this.value;" class="bg-slate-50 border border-slate-300 rounded-lg p-1.5 text-xs font-bold text-blue-600 focus:outline-none cursor-pointer">
                <?php for($p=1; $p<=4; $p++): ?>
                    <option value="boletin.php?id=<?php echo $id_alumno; ?>&periodo=<?php echo $p; ?>" <?php echo ($periodo_seleccionado == $p) ? 'selected' : ''; ?>>Periodo <?php echo $p; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button onclick="window.print();" class="bg-[#00a375] hover:bg-[#008761] text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all">
            <i class="fas fa-print mr-1"></i> Imprimir / PDF
        </button>
    </div>

    <!-- Contenedor del Boletín Tradicional -->
    <div class="contenedor-boletin w-full max-w-4xl bg-white p-10 border border-slate-300 shadow-sm">
        
        <!-- Encabezado con Escudo -->
        <div class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-6">
                <div class="w-20 h-24 flex-shrink-0">
                    <img src="escudo_colegio.png" alt="Escudo" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://cdn-icons-png.flaticon.com/512/2210/2210153.png';">
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-black tracking-tight" style="font-family: sans-serif;">INSTITUCION EDUCATIVA AULA PRIMARIA</h1>
                    <p class="text-sm text-black mt-1 font-medium">Resolución Oficial No. 12345</p>
                </div>
            </div>
            <div class="text-right text-xs text-black space-y-1 pt-2">
                <p>Fecha Emisión: <?php echo $fecha_impresion; ?></p>
                <p class="text-blue-600 font-bold text-sm">Periodo: <?php echo $periodo_seleccionado; ?>° Periodo Académico</p>
            </div>
        </div>

        <!-- Información del Alumno -->
        <div class="grid grid-cols-2 gap-4 mb-6 pt-4" style="font-family: sans-serif;">
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider block">ESTUDIANTE</span>
                <span class="text-xl font-bold text-black block mt-0.5"><?php echo htmlspecialchars($alumno_nombre); ?></span>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider block">GRADO / CURSO</span>
                <span class="text-xl font-bold text-black block mt-0.5"><?php echo htmlspecialchars($texto_grado); ?></span>
            </div>
        </div>

        <!-- Tabla Estricta con Bordes Negros -->
        <table class="tabla-boletin">
            <thead>
                <tr>
                    <th style="width: 25%;">ASIGNATURA</th>
                    <th style="width: 8%; text-align: center;">NOTA</th>
                    <th style="width: 12%; text-align: center;">DESEMPEÑO</th>
                    <th>LOGRO OBTENIDO EN EL PERIODO</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($calificaciones)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-400 italic">
                            No se registran calificaciones para este periodo.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($calificaciones as $reg): ?>
                        <tr>
                            <td class="font-bold"><?php echo htmlspecialchars($reg['materia_nombre']); ?></td>
                            <td class="text-center-recuadro"><?php echo number_format($reg['nota'], 1); ?></td>
                            <td class="text-center text-gray-700"><?php echo calcularDesempeno($reg['nota']); ?></td>
                            <td class="text-gray-800"><?php echo htmlspecialchars($reg['logro_texto']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Firmas -->
        <div class="grid grid-cols-2 gap-12 pt-20 mt-8 text-center text-xs text-black" style="font-family: sans-serif;">
            <div>
                <div class="w-48 border-b border-black mx-auto mb-1"></div>
                <p class="font-bold">Docente de Curso</p>
                <p class="text-gray-500">I.E. Aula Primaria</p>
            </div>
            <div>
                <div class="w-48 border-b border-black mx-auto mb-1"></div>
                <p class="font-bold">Administrador / Rector</p>
                <p class="text-gray-500">Control de Registro Escolar</p>
            </div>
        </div>

    </div>
</body>
</html>