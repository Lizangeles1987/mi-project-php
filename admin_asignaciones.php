<?php
include 'conexion.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensaje = "";
$tipo_alerta = "";

// 1. PROCESAR EL REGISTRO DE UNA ASIGNACIÓN
if (isset($_POST['registrar_asignacion'])) {
    $id_profesor = intval($_POST['id_profesor']);
    $id_materia = intval($_POST['id_materia']);
    $id_grado = intval($_POST['id_grado']);

    if ($id_profesor > 0 && $id_materia > 0 && $id_grado > 0) {
        // Validar duplicados
        $buscar_duplicado = mysqli_query($conexion, "SELECT * FROM carga_academica WHERE id_profesor = $id_profesor AND id_materia = $id_materia AND id_grado = $id_grado");
        
        if (mysqli_num_rows($buscar_duplicado) > 0) {
            $mensaje = "⚠️ Esta asignación exacta ya se encuentra registrada en el sistema.";
            $tipo_alerta = "error";
        } else {
            $sql = "INSERT INTO carga_academica (id_profesor, id_materia, id_grado) VALUES ($id_profesor, $id_materia, $id_grado)";
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "🎉 ¡Carga académica asignada correctamente al docente!";
                $tipo_alerta = "success";
            } else {
                $mensaje = "❌ Error en base de datos: " . mysqli_error($conexion);
                $tipo_alerta = "error";
            }
        }
    } else {
        $mensaje = "⚠️ Por favor, selecciona todas las opciones obligatorias.";
        $tipo_alerta = "error";
    }
}

// 2. PROCESAR LA ELIMINACIÓN DE UNA ASIGNACIÓN
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $sql_delete = "DELETE FROM carga_academica WHERE id = $id_eliminar";
    if (mysqli_query($conexion, $sql_delete)) {
        $mensaje = "🗑️ Asignación removida con éxito.";
        $tipo_alerta = "success";
    }
}

// 3. CONSULTAR PROFESORES, MATERIAS Y GRADOS (Consultas limpias e independientes)
$profesores = mysqli_query($conexion, "SELECT id, nombre FROM usuarios WHERE rol = 'profesor' ORDER BY nombre ASC");
$materias = mysqli_query($conexion, "SELECT id, nombre FROM materias ORDER BY nombre ASC");
$grados = mysqli_query($conexion, "SELECT id, nombre, numero FROM grados_admin ORDER BY numero ASC");

// 4. CONSULTAR LA CARGA COMPLETA CON UNIONES (FINAL)
$lista_carga = [];
$sql_carga = "SELECT c.id, u.nombre AS profesor, m.nombre AS materia, g.numero, g.nombre AS grado 
              FROM carga_academica c
              JOIN usuarios u ON c.id_profesor = u.id
              JOIN materias m ON c.id_materia = m.id
              JOIN grados_admin g ON c.id_grado = g.id
              ORDER BY u.nombre ASC";
$resultado_carga = mysqli_query($conexion, $sql_carga);
if ($resultado_carga) {
    while ($fila = mysqli_fetch_assoc($resultado_carga)) {
        $lista_carga[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Asignación Académica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Distribución de Carga Académica</h2>
                <p class="text-xs text-slate-400 mt-1">Vincula qué asignaturas dictará cada profesor y en qué grados específicos.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="p-4 rounded-2xl text-sm font-semibold max-w-xl <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-1">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Asignar Materia</h3>
                    
                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">1. Selecciona el Docente</label>
                            <select name="id_profesor" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                                <option value="">-- Elige un profesor --</option>
                                <?php while($p = mysqli_fetch_assoc($profesores)): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">2. Selecciona la Asignatura</label>
                            <select name="id_materia" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                                <option value="">-- Elige una materia --</option>
                                <?php while($m = mysqli_fetch_assoc($materias)): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">3. Selecciona el Grado/Curso</label>
                            <select name="id_grado" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                                <option value="">-- Elige un grado --</option>
                                <?php while($g = mysqli_fetch_assoc($grados)): ?>
                                    <option value="<?php echo $g['id']; ?>"><?php echo $g['numero']; ?>° - <?php echo htmlspecialchars($g['nombre']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <button type="submit" name="registrar_asignacion" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                            <i class="fas fa-link mr-1"></i> Confirmar Asignación
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-2">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Distribución Actual (<?php echo count($lista_carga); ?>)</h3>
                    
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm text-xs">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <th class="p-4">Docente</th>
                                    <th class="p-4">Asignatura asignada</th>
                                    <th class="p-4 text-center">Grado</th>
                                    <th class="p-4 text-center w-20">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                                <?php if(empty($lista_carga)): ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-slate-400 italic">No se han realizado asignaciones académicas todavía.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lista_carga as $item): ?>
                                        <tr class="hover:bg-slate-50/50 transition-all">
                                            <td class="p-4 font-bold text-slate-800">
                                                <i class="fas fa-user-chalkboard text-slate-400 mr-2"></i>
                                                <?php echo htmlspecialchars($item['profesor']); ?>
                                            </td>
                                            <td class="p-4 text-slate-600 font-semibold"><?php echo htmlspecialchars($item['materia']); ?></td>
                                            <td class="p-4 text-center">
                                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold">
                                                    <?php echo $item['numero']; ?>° - <?php echo htmlspecialchars($item['grado']); ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <a href="admin_asignaciones.php?eliminar=<?php echo $item['id']; ?>" onclick="return confirm('¿Remover esta materia de la carga del docente?');" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-all" title="Eliminar Asignación">
                                                    <i class="fas fa-unlink text-xs"></i>
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