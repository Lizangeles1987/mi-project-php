<?php
// Conexión a tu base de datos original
include 'conexion.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensaje = "";
$tipo_alerta = "";

// 1. PROCESAR EL REGISTRO DE UNA NUEVA ASIGNATURA
if (isset($_POST['registrar_materia'])) {
    $nombre_materia = trim(mysqli_real_escape_string($conexion, $_POST['nombre_materia']));

    // VALIDACIÓN: Que no contenga números utilizando expresiones regulares
    if (preg_match('/[0-9]/', $nombre_materia)) {
        $mensaje = "⚠️ El nombre de la asignatura no puede contener números.";
        $tipo_alerta = "error";
    } elseif (!empty($nombre_materia)) {
        // Validar duplicados
        $buscar_materia = mysqli_query($conexion, "SELECT * FROM materias WHERE nombre = '$nombre_materia'");
        if (mysqli_num_rows($buscar_materia) > 0) {
            $mensaje = "⚠️ Esta asignatura ya se encuentra registrada.";
            $tipo_alerta = "error";
        } else {
            $sql = "INSERT INTO materias (nombre) VALUES ('$nombre_materia')";
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "🎉 ¡Asignatura registrada con éxito!";
                $tipo_alerta = "success";
            }
        }
    }
}

// 2. PROCESAR LA ELIMINACIÓN DE UNA ASIGNATURA
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $sql_delete = "DELETE FROM materias WHERE id = $id_eliminar";
    
    if (mysqli_query($conexion, $sql_delete)) {
        $mensaje = "🗑️ Asignatura eliminada correctamente.";
        $tipo_alerta = "success";
    } else {
        $mensaje = "❌ Error al eliminar la asignatura.";
        $tipo_alerta = "error";
    }
}

// 3. PROCESAR LA MODIFICACIÓN/EDICIÓN DE UNA ASIGNATURA
if (isset($_POST['editar_materia'])) {
    $id_editar = intval($_POST['id_materia']);
    $nuevo_nombre = trim(mysqli_real_escape_string($conexion, $_POST['nuevo_nombre']));

    if (preg_match('/[0-9]/', $nuevo_nombre)) {
        $mensaje = "⚠️ El nuevo nombre de la asignatura no puede contener números.";
        $tipo_alerta = "error";
    } elseif (!empty($nuevo_nombre)) {
        $sql_update = "UPDATE materias SET nombre = '$nuevo_nombre' WHERE id = $id_editar";
        if (mysqli_query($conexion, $sql_update)) {
            $mensaje = "✏️ ¡Asignatura actualizada con éxito!";
            $tipo_alerta = "success";
        } else {
            $mensaje = "❌ Error al actualizar la asignatura.";
            $tipo_alerta = "error";
        }
    }
}

// 4. CONSULTAR TODAS LAS MATERIAS ACTUALIZADAS
$lista_materias = [];
$resultado_lista = mysqli_query($conexion, "SELECT id, nombre FROM materias ORDER BY nombre ASC");
if ($resultado_lista) {
    while ($fila = mysqli_fetch_assoc($resultado_lista)) {
        $lista_materias[] = $fila;
    }
}

// Variables para el modo edición
$modo_edicion = false;
$materia_a_editar = ['id' => '', 'nombre' => ''];
if (isset($_GET['editar'])) {
    $id_get_editar = intval($_GET['editar']);
    $buscar_edicion = mysqli_query($conexion, "SELECT id, nombre FROM materias WHERE id = $id_get_editar");
    if ($fila_edicion = mysqli_fetch_assoc($buscar_edicion)) {
        $modo_edicion = true;
        $materia_a_editar = $fila_edicion;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Gestión de Asignaturas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Gestión de Asignaturas</h2>
                <p class="text-xs text-slate-400 mt-1">Configura, edita, elimina y restringe las materias del plan de estudios.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="p-4 rounded-2xl text-sm font-semibold max-w-xl <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-1">
                    <?php if ($modo_edicion): ?>
                        <h3 class="text-sm font-black text-amber-600 uppercase tracking-wider mb-4 border-b pb-2">Modificar Asignatura</h3>
                        <form method="POST" action="admin_materias.php" class="space-y-4">
                            <input type="hidden" name="id_materia" value="<?php echo $materia_a_editar['id']; ?>">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nombre de la Asignatura</label>
                                <input type="text" name="nuevo_nombre" required value="<?php echo htmlspecialchars($materia_a_editar['nombre']); ?>" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-amber-500 bg-white">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" name="editar_materia" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                    Actualizar
                                </button>
                                <a href="admin_materias.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Crear Nueva Asignatura</h3>
                        <form method="POST" action="admin_materias.php" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nombre de la Materia (Sin números)</label>
                                <input type="text" name="nombre_materia" required placeholder="Ej: Ciencias Naturales" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                            </div>
                            <button type="submit" name="registrar_materia" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                <i class="fas fa-book-medical mr-1"></i> Guardar Asignatura
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-2">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Materias Habilitadas (<?php echo count($lista_materias); ?>)</h3>
                    
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm text-xs">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <th class="p-4 w-16 text-center">Ícono</th>
                                    <th class="p-4">Nombre de la Asignatura</th>
                                    <th class="p-4 text-center w-20">ID</th>
                                    <th class="p-4 text-center w-32">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                                <?php if(empty($lista_materias)): ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-slate-400 italic">No hay materias registradas en el sistema todavía.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lista_materias as $mat): ?>
                                        <tr class="hover:bg-slate-50/50 transition-all">
                                            <td class="p-4 text-center">
                                                <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-500">
                                                    <i class="fas fa-book text-xs"></i>
                                                </span>
                                            </td>
                                            <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($mat['nombre']); ?></td>
                                            <td class="p-4 text-center text-slate-400">#<?php echo $mat['id']; ?></td>
                                            <td class="p-4 text-center space-x-2">
                                                <a href="admin_materias.php?editar=<?php echo $mat['id']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all" title="Modificar Asignatura">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                <a href="admin_materias.php?eliminar=<?php echo $mat['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta asignatura?');" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-all" title="Eliminar Asignatura">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>
</html>