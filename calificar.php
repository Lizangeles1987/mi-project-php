<?php
/**
 * Archivo: calificar.php
 * Propósito: Interfaz y procesamiento para calificar la entrega de una tarea de forma segura.
 * Conectado con: admin_tareas.php y db.php
 */

session_start();
// Usamos tu archivo de conexión real
require_once 'db.php';

// Verificar si el usuario tiene sesión activa
if (!isset($_SESSION['usuario']) && !isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

$usuario_docente = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : "Docente");

$mensaje = "";
$tipo_alerta = "";

// 1. OBTENER INFORMACIÓN DE LA ENTREGA A CALIFICAR (Si viene de admin_tareas.php por GET)
$id_entrega = 0;
$estudiante_nombre = "";
$materia_nombre = "";
$archivo_ruta = "";
$nota_actual = "";

if (isset($_GET['id'])) {
    $id_entrega = (int)$_GET['id'];
    
    // Consulta para obtener los detalles de la entrega específica
    // Ajustado para la estructura de tu tabla 'entregas'
    $query_detalles = "SELECT * FROM entregas WHERE id = ?";
    if ($stmt_get = mysqli_prepare($conexion, $query_detalles)) {
        mysqli_stmt_bind_param($stmt_get, "i", $id_entrega);
        mysqli_stmt_execute($stmt_get);
        $resultado = mysqli_stmt_get_result($stmt_get);
        
        if ($entrega = mysqli_fetch_assoc($resultado)) {
            // Adaptado a tus columnas reales: 'alumno_nombre' o 'nombre_estudiante', 'materia', 'archivo_nombre'
            $estudiante_nombre = isset($entrega['alumno_nombre']) ? $entrega['alumno_nombre'] : (isset($entrega['nombre_estudiante']) ? $entrega['nombre_estudiante'] : "Estudiante");
            $materia_nombre = isset($entrega['materia']) ? $entrega['materia'] : "Materia";
            $archivo_ruta = isset($entrega['archivo_nombre']) ? $entrega['archivo_nombre'] : (isset($entrega['archivo_ruta']) ? $entrega['archivo_ruta'] : "");
            $nota_actual = isset($entrega['nota']) ? $entrega['nota'] : "";
        }
        mysqli_stmt_close($stmt_get);
    }
}

// 2. PROCESAR EL ENVÍO DE LA CALIFICACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_guardar_nota'])) {
    $id_entrega = isset($_POST['id_entrega']) ? intval($_POST['id_entrega']) : 0;
    $nota = isset($_POST['nota']) ? (float)$_POST['nota'] : null;

    if ($id_entrega > 0 && $nota !== null) {
        // Actualizamos la nota en tu tabla entregas
        $sql_update = "UPDATE entregas SET nota = ? WHERE id = ?";
        
        if ($stmt_update = mysqli_prepare($conexion, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "di", $nota, $id_entrega);
            
            if (mysqli_stmt_execute($stmt_update)) {
                mysqli_stmt_close($stmt_update);
                // Éxito: Redirigir al panel con confirmación
                header("Location: admin_tareas.php?status=success&msg=calificado");
                exit();
            } else {
                $mensaje = "Error al guardar la calificación en la base de datos.";
                $tipo_alerta = "error";
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $mensaje = "Error de preparación en la consulta.";
            $tipo_alerta = "error";
        }
    } else {
        $mensaje = "Los datos ingresados no son válidos.";
        $tipo_alerta = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar Entrega - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .header-gradient { background: linear-gradient(90deg, #2c7db7 0%, #3b82f6 100%); }
    </style>
</head>
<body class="p-4 md:p-8 flex items-center justify-center min-h-screen">

    <div class="max-w-xl w-full bg-white shadow-2xl rounded-xl overflow-hidden border border-slate-200">
        
        <!-- Cabecera de la ventana estilo institucional -->
        <div class="header-gradient text-white p-4 flex justify-between items-center shadow-md">
            <div class="flex items-center gap-3">
                <i class="fas fa-edit text-lg"></i>
                <h1 class="font-bold uppercase tracking-tight text-xs md:text-sm">Calificar Entrega de Tarea</h1>
            </div>
            <a href="admin_tareas.php" class="text-white hover:text-red-200 text-lg font-bold px-2 rounded">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <!-- Alertas de estado -->
        <?php if($mensaje): ?>
            <div class="m-6 p-4 rounded-lg flex items-center gap-3 <?php echo ($tipo_alerta == 'success') ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                <i class="fas <?php echo ($tipo_alerta == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> text-lg"></i>
                <span class="text-xs font-semibold"><?php echo $mensaje; ?></span>
            </div>
        <?php endif; ?>

        <!-- Contenido principal -->
        <div class="p-6 md:p-8 space-y-6">
            
            <?php if ($id_entrega > 0 && !empty($estudiante_nombre)): ?>
                <!-- Información del estudiante y la tarea -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-3 text-sm text-slate-700">
                    <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                        <span class="text-slate-400 uppercase font-black text-[10px]">Estudiante:</span>
                        <span class="font-bold text-slate-800 uppercase"><?php echo $estudiante_nombre; ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                        <span class="text-slate-400 uppercase font-black text-[10px]">Asignatura:</span>
                        <span class="font-semibold text-blue-600 uppercase"><?php echo $materia_nombre; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 uppercase font-black text-[10px]">Archivo Entregado:</span>
                        <?php if(!empty($archivo_ruta)): ?>
                            <a href="subidas/<?php echo basename($archivo_ruta); ?>" target="_blank" class="text-blue-500 hover:underline font-medium flex items-center gap-1">
                                <i class="fas fa-file-pdf"></i> Ver Documento
                            </a>
                        <?php else: ?>
                            <span class="text-slate-400 italic">No disponible</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Formulario de Evaluación -->
                <form action="calificar.php" method="POST" class="space-y-4">
                    <input type="hidden" name="id_entrega" value="<?php echo $id_entrega; ?>">

                    <div>
                        <label class="block text-[10px] font-black text-blue-600 uppercase tracking-wider mb-2">Asignar Nota Final (0.0 - 10.0)</label>
                        <input type="number" step="0.1" min="0" max="10" name="nota" required 
                            value="<?php echo !empty($nota_actual) ? number_format((float)$nota_actual, 1) : ''; ?>"
                            placeholder="Ej: 8.5"
                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3.5 text-center text-xl font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-blue-700">
                    </div>

                    <div class="pt-4 flex gap-3">
                        <a href="admin_tareas.php" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3.5 rounded-xl text-center text-xs uppercase tracking-wider transition-all">
                            Volver al Listado
                        </a>
                        <button type="submit" name="btn_guardar_nota" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-blue-200 transition-all">
                            Confirmar Nota
                        </button>
                    </div>
                </form>

            <?php else: ?>
                <!-- En caso de que se intente acceder sin un ID de entrega válido -->
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-amber-500 text-5xl mb-4"></i>
                    <h3 class="font-bold text-slate-800 text-lg">No se seleccionó ninguna entrega</h3>
                    <p class="text-slate-500 text-sm mt-1">Por favor regresa al panel de revisión para seleccionar el alumno que deseas calificar.</p>
                    <a href="admin_tareas.php" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl text-xs uppercase tracking-wider transition-all">
                        Ir al panel de revisión
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>