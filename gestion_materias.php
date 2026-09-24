<?php
session_start();
if (!isset($_SESSION['nombre'])) { header("Location: login.php"); exit(); }
include 'conexion.php';

$mensaje = "";
$tipo_alerta = "";

// AÑADIR MATERIA
if (isset($_POST['guardar_materia'])) {
    $nombre_materia = mysqli_real_escape_string($conexion, trim($_POST['nombre_materia']));
    if (!empty($nombre_materia)) {
        $sql = "INSERT INTO materias (nombre) VALUES ('$nombre_materia')";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = "📚 Materia añadida correctamente al sistema.";
            $tipo_alerta = "success";
        } else {
            $mensaje = "❌ La materia ya existe o hubo un error.";
            $tipo_alerta = "error";
        }
    }
}

// ELIMINAR MATERIA
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM materias WHERE id='$id_eliminar'");
    header("Location: gestion_materias.php");
    exit();
}

$resultado_materias = mysqli_query($conexion, "SELECT * FROM materias ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Materias - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>.sidebar { background-color: #0f172a; min-height: 100vh; width: 260px; }</style>
</head>
<body class="bg-slate-50 flex">

    <aside class="sidebar text-slate-400 p-6 shadow-xl">
        <h2 class="text-white text-2xl font-bold italic mb-10 text-blue-400">Aula Primaria</h2>
        <nav class="space-y-2">
            <a href="cursos.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all text-slate-300"><i class="fas fa-th-large w-5"></i> Mis Cursos</a>
            <a href="gestion_notas.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all"><i class="fas fa-graduation-cap w-5"></i> Notas y Logros</a>
            <a href="crear_logros.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all"><i class="fas fa-bullseye w-5"></i> Banco de Logros</a>
            <a href="gestion_materias.php" class="flex items-center gap-3 p-3 rounded-xl bg-blue-600 text-white font-semibold"><i class="fas fa-book w-5"></i> Administrar Materias</a>
            <a href="matricula_excel.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800 transition-all"><i class="fas fa-file-upload w-5"></i> Matrícula de Alumnos</a>
            <div class="pt-8 mt-8 border-t border-slate-800">
                <a href="logout.php" class="flex items-center gap-3 p-3 rounded-xl text-red-400 hover:bg-red-900/10"><i class="fas fa-sign-out-alt w-5"></i> Cerrar Sesión</a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <header class="mb-6">
            <h2 class="text-3xl font-bold text-slate-800">Configuración de Materias Escolares</h2>
            <p class="text-slate-500 text-sm">Agrega nuevas asignaturas para que estén disponibles en las planillas de notas.</p>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div class="p-4 mb-6 rounded-xl text-sm <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
                <h3 class="text-xl font-bold text-slate-800 mb-4">Nueva Asignatura</h3>
                <form action="gestion_materias.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nombre de la Materia</label>
                        <input type="text" name="nombre_materia" placeholder="Ej: Informática y Tecnología" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-400">
                    </div>
                    <button type="submit" name="guardar_materia" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition-all shadow-md">Habilitar Materia</button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100"><h3 class="text-xl font-bold text-slate-800">Materias en el Sistema</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 text-xs uppercase font-semibold">
                                <th class="p-4">ID</th>
                                <th class="p-4">Nombre de la Asignatura</th>
                                <th class="p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                            <?php while($mat = mysqli_fetch_assoc($resultado_materias)): ?>
                            <tr class="hover:bg-slate-50/80 transition-all">
                                <td class="p-4 font-mono text-xs"><?php echo $mat['id']; ?></td>
                                <td class="p-4 font-bold text-slate-700"><?php echo htmlspecialchars($mat['nombre']); ?></td>
                                <td class="p-4 text-center">
                                    <a href="gestion_materias.php?eliminar=<?php echo $mat['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta materia?')" class="text-slate-400 hover:text-red-600 transition-all p-2"><i class="fas fa-trash"></i></a>
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