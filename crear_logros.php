<?php
session_start();
if (!isset($_SESSION['nombre'])) { 
    header("Location: login.php"); 
    exit(); 
}
include 'conexion.php';

$mensaje = "";
$tipo_alerta = "";

// GUARDAR NUEVO LOGRO
if (isset($_POST['guardar_logro'])) {
    $grado = (int)$_POST['grado'];
    $materia = mysqli_real_escape_string($conexion, $_POST['materia']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

    if (!empty($grado) && !empty($materia) && !empty($descripcion)) {
        $sql = "INSERT INTO logros (grado, materia, descripcion) VALUES ('$grado', '$materia', '$descripcion')";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = "🎯 ¡Logro académico registrado con éxito en el banco!";
            $tipo_alerta = "success";
        } else {
            $mensaje = "❌ Error al guardar en la base de datos: " . mysqli_error($conexion);
            $tipo_alerta = "error";
        }
    }
}

// ELIMINAR LOGRO
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM logros WHERE id='$id_eliminar'");
    header("Location: crear_logros.php");
    exit();
}

// TRAER TODOS LOS LOGROS REGISTRADOS
$resultado_logros = mysqli_query($conexion, "SELECT * FROM logros ORDER BY grado ASC, materia ASC");

// --- AQUÍ ESTÁ EL CAMBIO DINÁMICO PARA EL BLOC DE NOTAS ---
$resultado_lista_materias = mysqli_query($conexion, "SELECT nombre FROM materias ORDER BY nombre ASC");
$materias_primaria = [];
while($m_row = mysqli_fetch_assoc($resultado_lista_materias)) {
    $materias_primaria[] = $m_row['nombre'];
}
// ---------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco de Logros - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background-color: #0f172a; min-height: 100vh; width: 260px; }
        .main-content { background-color: #f8fafc; flex: 1; }
    </style>
</head>
<body class="bg-slate-50 flex">

    <aside class="sidebar text-slate-400 p-6">
        <h2 class="text-white text-2xl font-bold italic mb-10 text-blue-400">Aula Primaria</h2>
        <nav class="space-y-2">
            <a href="cursos.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-th-large w-5"></i> Mis Cursos
            </a>
            <a href="gestion_notas.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-graduation-cap w-5"></i> Notas y Logros
            </a>
            <a href="crear_logros.php" class="flex items-center gap-3 p-3 rounded-xl bg-blue-600/10 text-blue-400 border border-blue-600/20">
                <i class="fas fa-bullseye w-5"></i> Banco de Logros
            </a>
            <a href="gestion_materias.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-book w-5"></i> Administrar Materias
            </a>
            <a href="subir_alumnos.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-file-upload w-5"></i> Matrícula Masiva Excel
            </a>
            <div class="pt-8 mt-8 border-t border-slate-800">
                <a href="logout.php" class="flex items-center gap-3 p-3 rounded-xl text-red-400 hover:bg-red-900/10">
                    <i class="fas fa-sign-out-alt w-5"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content p-8">
        <header class="mb-6">
            <h2 class="text-3xl font-bold text-slate-800">Banco de Logros Pedagógicos</h2>
            <p class="text-slate-500 text-sm">Crea y administra los logros para que luego aparezcan automáticamente al calificar.</p>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div class="p-4 mb-6 rounded-xl text-sm <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
                <h3 class="text-xl font-bold text-slate-800 mb-4">Nuevo Logro</h3>
                <form action="crear_logros.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Grado / Salón</label>
                        <select name="grado" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-400">
                            <option value="">-- Seleccione el Grado --</option>
                            <option value="2">2° de Primaria</option>
                            <option value="3">3° de Primaria</option>
                            <option value="4">4° de Primaria</option>
                            <option value="5">5° de Primaria</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Materia Asociada</label>
                        <select name="materia" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-400">
                            <option value="">-- Seleccione la Asignatura --</option>
                            <?php foreach($materias_primaria as $mat): ?>
                                <option value="<?php echo $mat; ?>"><?php echo $mat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Descripción del Logro Competencia</label>
                        <textarea name="descripcion" placeholder="Ejemplo: Comprende y aplica la estructura de las operaciones de adición..." required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg outline-none h-28 resize-none focus:border-blue-400"></textarea>
                    </div>
                    <button type="submit" name="guardar_logro" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition-all shadow-md">
                        <i class="fas fa-plus mr-1"></i> Registrar en el Banco
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100"><h3 class="text-xl font-bold text-slate-800">Logros Disponibles</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 text-xs uppercase font-semibold">
                                <th class="p-4">Grado</th>
                                <th class="p-4">Materia</th>
                                <th class="p-4">Descripción del Logro</th>
                                <th class="p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                            <?php if(mysqli_num_rows($resultado_logros) == 0): ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400 italic">El banco de logros está vacío. Crea el primero a la izquierda.</td>
                                </tr>
                            <?php endif; ?>
                            <?php while($logro = mysqli_fetch_assoc($resultado_logros)): ?>
                            <tr class="hover:bg-slate-50/80 transition-all">
                                <td class="p-4 font-bold text-slate-700"><?php echo $logro['grado']; ?>°</td>
                                <td class="p-4"><span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md font-medium text-xs"><?php echo htmlspecialchars($logro['materia']); ?></span></td>
                                <td class="p-4"><?php echo htmlspecialchars($logro['descripcion']); ?></td>
                                <td class="p-4 text-center">
                                    <a href="crear_logros.php?eliminar=<?php echo $logro['id']; ?>" onclick="return confirm('¿Eliminar este logro del banco?')" class="text-slate-400 hover:text-red-600 transition-all p-2"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>