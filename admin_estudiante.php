<?php
include 'conexion.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nombre_usuario = isset($_SESSION['nombre']) ? trim($_SESSION['nombre']) : 'Admin';
$rol_actual = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : 'administrador';

$mensaje = "";
$tipo_alerta = "";

// 1. PROCESAR EL REGISTRO DE UN NUEVO ESTUDIANTE
if (isset($_POST['registrar_estudiante'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password_raw = $_POST['password'];
    $grado = intval($_POST['grado']);
    $rol = 'estudiante';

    if (!empty($nombre) && !empty($email) && !empty($password_raw) && $grado > 0) {
        $buscar_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email = '$email'");
        
        if (mysqli_num_rows($buscar_usuario) > 0) {
            $mensaje = "⚠️ Ese correo electrónico ya está registrado con otro usuario.";
            $tipo_alerta = "error";
        } else {
            // ENCRIPTACIÓN DE CONTRASEÑA SEGURA
            $password_encriptada = password_hash($password_raw, PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO usuarios (nombre, email, password, rol, grado) VALUES ('$nombre', '$email', '$password_encriptada', '$rol', $grado)";
            
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "🎉 ¡Estudiante '$nombre' matriculado con éxito!";
                $tipo_alerta = "success";
            } else {
                $mensaje = "❌ Error al registrar: " . mysqli_error($conexion);
                $tipo_alerta = "error";
            }
        }
    } else {
        $mensaje = "⚠️ Por favor, rellena todos los campos obligatorios.";
        $tipo_alerta = "error";
    }
}

// 2. PROCESAR LA ELIMINACIÓN DE UN ESTUDIANTE
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $sql_delete = "DELETE FROM usuarios WHERE id = $id_eliminar AND LOWER(rol) = 'estudiante'";
    
    if (mysqli_query($conexion, $sql_delete)) {
        $mensaje = "🗑️ Estudiante eliminado correctamente.";
        $tipo_alerta = "success";
    } else {
        $mensaje = "❌ Error al eliminar el estudiante.";
        $tipo_alerta = "error";
    }
}

// 3. PROCESAR LA ACTUALIZACIÓN/EDICIÓN
if (isset($_POST['editar_estudiante'])) {
    $id_editar = intval($_POST['id_estudiante']);
    $nuevo_nombre = mysqli_real_escape_string($conexion, $_POST['nuevo_nombre']);
    $nuevo_email = mysqli_real_escape_string($conexion, $_POST['nuevo_email']);
    $nuevo_grado = intval($_POST['nuevo_grado']);
    $nueva_pass_raw = $_POST['nueva_password'];

    if (!empty($nuevo_nombre) && !empty($nuevo_email) && $nuevo_grado > 0) {
        if (!empty($nueva_pass_raw)) {
            $nueva_pass_encriptada = password_hash($nueva_pass_raw, PASSWORD_BCRYPT);
            $sql_update = "UPDATE usuarios SET nombre = '$nuevo_nombre', email = '$nuevo_email', grado = $nuevo_grado, password = '$nueva_pass_encriptada' WHERE id = $id_editar AND LOWER(rol) = 'estudiante'";
        } else {
            $sql_update = "UPDATE usuarios SET nombre = '$nuevo_nombre', email = '$nuevo_email', grado = $nuevo_grado WHERE id = $id_editar AND LOWER(rol) = 'estudiante'";
        }

        if (mysqli_query($conexion, $sql_update)) {
            $mensaje = "✏️ Datos del estudiante actualizados correctamente.";
            $tipo_alerta = "success";
        } else {
            $mensaje = "❌ Error al actualizar el estudiante.";
            $tipo_alerta = "error";
        }
    }
}

// 4. CONSULTAR TODOS LOS ESTUDIANTES (Filtrado estricto por rol estudiante)
$lista_estudiantes = [];
$resultado_lista = mysqli_query($conexion, "SELECT id, nombre, email, grado FROM usuarios WHERE LOWER(rol) = 'estudiante' ORDER BY grado ASC, nombre ASC");
if ($resultado_lista) {
    while ($fila = mysqli_fetch_assoc($resultado_lista)) {
        $lista_estudiantes[] = $fila;
    }
}

// Variables para activar el modo edición
$modo_edicion = false;
$estudiante_a_editar = ['id' => '', 'nombre' => '', 'email' => '', 'grado' => ''];
if (isset($_GET['editar'])) {
    $id_get_editar = intval($_GET['editar']);
    $buscar_edicion = mysqli_query($conexion, "SELECT id, nombre, email, grado FROM usuarios WHERE id = $id_get_editar AND LOWER(rol) = 'estudiante'");
    if ($fila_edicion = mysqli_fetch_assoc($buscar_edicion)) {
        $modo_edicion = true;
        $estudiante_a_editar = $fila_edicion;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Estudiantes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Control de Matrículas</h2>
                <p class="text-xs text-slate-400 mt-1">Registra nuevos estudiantes y asígnales su respectivo grado académico.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="p-4 rounded-2xl text-sm font-semibold max-w-xl <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-1">
                    <?php if ($modo_edicion): ?>
                        <h3 class="text-sm font-black text-amber-600 uppercase tracking-wider mb-4 border-b pb-2">Editar Estudiante</h3>
                        <form method="POST" action="admin_estudiantes.php" autocomplete="off" class="space-y-4">
                            <input type="hidden" name="id_estudiante" value="<?php echo $estudiante_a_editar['id']; ?>">
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nombre Completo</label>
                                <input type="text" name="nuevo_nombre" required value="<?php echo htmlspecialchars($estudiante_a_editar['nombre']); ?>" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-amber-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Correo Electrónico</label>
                                <input type="email" name="nuevo_email" required value="<?php echo htmlspecialchars($estudiante_a_editar['email']); ?>" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-amber-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nueva Contraseña (Opcional)</label>
                                <input type="password" name="nueva_password" placeholder="Dejar en blanco para conservar" autocomplete="new-password" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-amber-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Asignar Grado</label>
                                <select name="nuevo_grado" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                                    <?php for($g=1; $g<=5; $g++): ?>
                                        <option value="<?php echo $g; ?>" <?php echo ($estudiante_a_editar['grado'] == $g) ? 'selected' : ''; ?>><?php echo $g; ?>° de Primaria</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" name="editar_estudiante" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                    Actualizar
                                </button>
                                <a href="admin_estudiantes.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Matricular Alumno</h3>
                        
                        <form method="POST" action="admin_estudiantes.php" autocomplete="off" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nombre Completo</label>
                                <input type="text" name="nombre" required placeholder="Ej: Juanito Pérez" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Correo Electrónico</label>
                                <input type="email" name="email" required placeholder="juanito@colegio.com" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Contraseña de Acceso</label>
                                <input type="password" name="password" required placeholder="••••••••" autocomplete="new-password" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Asignar Grado</label>
                                <select name="grado" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                                    <option value="">-- Selecciona el Curso --</option>
                                    <?php for($g=1; $g<=5; $g++): ?>
                                        <option value="<?php echo $g; ?>"><?php echo $g; ?>° de Primaria</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <button type="submit" name="registrar_estudiante" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                <i class="fas fa-user-plus mr-1"></i> Confirmar Matrícula
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-2">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Estudiantes Registrados (<?php echo count($lista_estudiantes); ?>)</h3>
                    
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm text-xs w-full overflow-x-auto">
                        <table class="w-full text-left min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <th class="p-4">Estudiante</th>
                                    <th class="p-4">Correo</th>
                                    <th class="p-4 text-center w-24">Curso</th>
                                    <th class="p-4 text-center w-36">Boletín</th>
                                    <th class="p-4 text-center w-28">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                                <?php if(empty($lista_estudiantes)): ?>
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400 italic">No hay ningún estudiante matriculado en el sistema todavía.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lista_estudiantes as $est): ?>
                                        <tr class="hover:bg-slate-50/50 transition-all">
                                            <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($est['nombre']); ?></td>
                                            <td class="p-4 text-slate-500"><?php echo htmlspecialchars($est['email']); ?></td>
                                            <td class="p-4 text-center">
                                                <span class="px-2.5 py-1 rounded-lg font-black bg-blue-50 text-blue-700">
                                                    <?php echo (!empty($est['grado'])) ? $est['grado'] . '° Primaria' : 'Sin Curso'; ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <?php for ($p = 1; $p <= 4; $p++): ?>
                                                        <a href="boletin.php?id=<?php echo $est['id']; ?>&periodo=<?php echo $p; ?>" 
                                                           target="_blank" 
                                                           class="px-1.5 py-1 rounded font-bold text-[10px] bg-slate-100 hover:bg-[#181e31] text-slate-700 hover:text-white transition-all shadow-sm" 
                                                           title="Ver Boletín Periodo <?php echo $p; ?>">
                                                            P<?php echo $p; ?>
                                                        </a>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td class="p-4 text-center space-x-1">
                                                <a href="admin_estudiantes.php?editar=<?php echo $est['id']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all" title="Modificar Datos">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                <a href="admin_estudiantes.php?eliminar=<?php echo $est['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar la matrícula de este estudiante?');" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-all" title="Eliminar Matrícula">
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