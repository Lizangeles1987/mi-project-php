<?php
include 'conexion.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensaje = "";
$tipo_alerta = "";

// 1. PROCESAR EL REGISTRO DE UN NUEVO DOCENTE
if (isset($_POST['registrar_docente'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password_raw = $_POST['password'];
    $rol = 'profesor'; 

    if (!empty($nombre) && !empty($email) && !empty($password_raw)) {
        $buscar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email = '$email'");
        if (mysqli_num_rows($buscar_correo) > 0) {
            $mensaje = "⚠️ Ese correo electrónico ya está registrado.";
            $tipo_alerta = "error";
        } else {
            // ENCRIPTACIÓN DE CONTRASEÑA SEGURA
            $password_encriptada = password_hash($password_raw, PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$password_encriptada', '$rol')";
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "🎉 ¡Profesor(a) registrado con éxito!";
                $tipo_alerta = "success";
            } else {
                $mensaje = "❌ Error en base de datos: " . mysqli_error($conexion);
                $tipo_alerta = "error";
            }
        }
    }
}

// 2. PROCESAR LA ELIMINACIÓN DE UN DOCENTE
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $sql_delete = "DELETE FROM usuarios WHERE id = $id_eliminar AND LOWER(rol) = 'profesor'";
    
    if (mysqli_query($conexion, $sql_delete)) {
        $mensaje = "🗑️ Cuenta de profesor eliminada correctamente.";
        $tipo_alerta = "success";
    } else {
        $mensaje = "❌ Error al eliminar el profesor.";
        $tipo_alerta = "error";
    }
}

// 3. PROCESAR LA ACTUALIZACIÓN/MODIFICACIÓN
if (isset($_POST['editar_docente'])) {
    $id_editar = intval($_POST['id_docente']);
    $nuevo_nombre = mysqli_real_escape_string($conexion, $_POST['nuevo_nombre']);
    $nuevo_email = mysqli_real_escape_string($conexion, $_POST['nuevo_email']);
    $nueva_pass_raw = $_POST['nueva_password'];

    if (!empty($nuevo_nombre) && !empty($nuevo_email)) {
        if (!empty($nueva_pass_raw)) {
            // ENCRIPTACIÓN SI SE CAMBIA LA CONTRASEÑA
            $nueva_pass_encriptada = password_hash($nueva_pass_raw, PASSWORD_BCRYPT);
            $sql_update = "UPDATE usuarios SET nombre = '$nuevo_nombre', email = '$nuevo_email', password = '$nueva_pass_encriptada' WHERE id = $id_editar AND LOWER(rol) = 'profesor'";
        } else {
            $sql_update = "UPDATE usuarios SET nombre = '$nuevo_nombre', email = '$nuevo_email' WHERE id = $id_editar AND LOWER(rol) = 'profesor'";
        }

        if (mysqli_query($conexion, $sql_update)) {
            $mensaje = "✏️ ¡Datos del profesor actualizados correctamente!";
            $tipo_alerta = "success";
        } else {
            $mensaje = "❌ Error al actualizar el profesor.";
            $tipo_alerta = "error";
        }
    }
}

// 4. CONSULTAR TODOS LOS PROFESORES (Filtrado estricto por rol profesor)
$lista_docentes = [];
$resultado_lista = mysqli_query($conexion, "SELECT id, nombre, email FROM usuarios WHERE LOWER(rol) = 'profesor' ORDER BY nombre ASC");
if ($resultado_lista) {
    while ($fila = mysqli_fetch_assoc($resultado_lista)) {
        $lista_docentes[] = $fila;
    }
}

// Variables para activar el modo edición
$modo_edicion = false;
$docente_a_editar = ['id' => '', 'nombre' => '', 'email' => ''];
if (isset($_GET['editar'])) {
    $id_get_editar = intval($_GET['editar']);
    $buscar_edicion = mysqli_query($conexion, "SELECT id, nombre, email FROM usuarios WHERE id = $id_get_editar AND LOWER(rol) = 'profesor'");
    if ($fila_edicion = mysqli_fetch_assoc($buscar_edicion)) {
        $modo_edicion = true;
        $docente_a_editar = $fila_edicion;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Gestión de Docentes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Gestión de Docentes</h2>
                <p class="text-xs text-slate-400 mt-1">Registra, edita, actualiza y remueve el acceso de los profesores del sistema.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="p-4 rounded-2xl text-sm font-semibold max-w-xl <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-1">
                    <?php if ($modo_edicion): ?>
                        <h3 class="text-sm font-black text-amber-600 uppercase tracking-wider mb-4 border-b pb-2">Actualizar Profesor</h3>
                        <form method="POST" action="admin_docentes.php" autocomplete="off" class="space-y-4">
                            <input type="hidden" name="id_docente" value="<?php echo $docente_a_editar['id']; ?>">
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nombre Completo</label>
                                <input type="text" name="nuevo_nombre" required value="<?php echo htmlspecialchars($docente_a_editar['nombre']); ?>" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-amber-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Correo Electrónico</label>
                                <input type="email" name="nuevo_email" required value="<?php echo htmlspecialchars($docente_a_editar['email']); ?>" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-amber-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nueva Contraseña (Opcional)</label>
                                <input type="password" name="nueva_password" placeholder="Dejar en blanco para no cambiar" autocomplete="new-password" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-amber-500 bg-white">
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" name="editar_docente" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                    Actualizar
                                </button>
                                <a href="admin_docentes.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Registrar Profesor</h3>
                        <form method="POST" action="admin_docentes.php" autocomplete="off" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nombre Completo</label>
                                <input type="text" name="nombre" required placeholder="Ej: Prof. Carlos Mendoza" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Correo Electrónico</label>
                                <input type="email" name="email" required placeholder="carlos@colegio.com" autocomplete="off" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Contraseña de Acceso</label>
                                <input type="password" name="password" required placeholder="••••••••" autocomplete="new-password" class="w-full p-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                            </div>

                            <button type="submit" name="registrar_docente" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                <i class="fas fa-chalkboard-teacher mr-1"></i> Habilitar Docente
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-2">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Docentes Activos (<?php echo count($lista_docentes); ?>)</h3>
                    
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm text-xs">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <th class="p-4">Nombre del Docente</th>
                                    <th class="p-4">Correo Electrónico</th>
                                    <th class="p-4 text-center w-20">ID</th>
                                    <th class="p-4 text-center w-32">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                                <?php if(empty($lista_docentes)): ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-slate-400 italic">No hay profesores registrados en el sistema todavía.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lista_docentes as $doc): ?>
                                        <tr class="hover:bg-slate-50/50 transition-all">
                                            <td class="p-4 font-bold text-slate-800">
                                                <i class="fas fa-user-tie text-slate-400 mr-2"></i>
                                                <?php echo htmlspecialchars($doc['nombre']); ?>
                                            </td>
                                            <td class="p-4 text-slate-500"><?php echo htmlspecialchars($doc['email']); ?></td>
                                            <td class="p-4 text-center text-slate-400">#<?php echo $doc['id']; ?></td>
                                            <td class="p-4 text-center space-x-2">
                                                <a href="admin_docentes.php?editar=<?php echo $doc['id']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all" title="Modificar Datos">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                <a href="admin_docentes.php?eliminar=<?php echo $doc['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar la cuenta de este profesor?');" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-all" title="Eliminar">
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