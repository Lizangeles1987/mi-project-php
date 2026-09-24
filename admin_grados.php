<?php
include 'conexion.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensaje = "";
$tipo_alerta = "";

if (isset($_POST['registrar_grado'])) {
    $nombre_grado = mysqli_real_escape_string($conexion, $_POST['nombre_grado']);
    $numero_grado = intval($_POST['numero_grado']);

    if (!empty($nombre_grado) && $numero_grado > 0) {
        $buscar_grado = mysqli_query($conexion, "SELECT * FROM grados_admin WHERE numero = $numero_grado");
        
        if (mysqli_num_rows($buscar_grado) > 0) {
            $mensaje = "⚠️ Ese número de grado ya se encuentra registrado en el sistema.";
            $tipo_alerta = "error";
        } else {
            $sql = "INSERT INTO grados_admin (nombre, numero) VALUES ('$nombre_grado', $numero_grado)";
            
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "🎉 ¡Grado '$nombre_grado' creado con éxito!";
                $tipo_alerta = "success";
            } else {
                $mensaje = "❌ Error al registrar en la base de datos: " . mysqli_error($conexion);
                $tipo_alerta = "error";
            }
        }
    } else {
        $mensaje = "⚠️ Por favor, rellena todos los campos obligatorios.";
        $tipo_alerta = "error";
    }
}

$lista_grados = [];
$resultado_lista = mysqli_query($conexion, "SELECT id, nombre, numero FROM grados_admin ORDER BY numero ASC");
if ($resultado_lista) {
    while ($fila = mysqli_fetch_assoc($resultado_lista)) {
        $lista_grados[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Gestión de Grados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Gestión de Grados Académicos</h2>
                <p class="text-xs text-slate-400 mt-1">Configura y habilita los cursos de primaria disponibles en la institución.</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="p-4 rounded-2xl text-sm font-semibold max-w-xl <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-1">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Crear Nuevo Curso</h3>
                    
                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Número de Grado</label>
                            <select name="numero_grado" required class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                                <option value="">-- Selecciona el número --</option>
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?>°</option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nombre del Grado</label>
                            <input type="text" name="nombre_grado" required placeholder="Ej: Primero de Primaria" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 bg-white">
                        </div>

                        <button type="submit" name="registrar_grado" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                            <i class="fas fa-folder-plus mr-1"></i> Crear Grado
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-[30px] shadow-sm border border-slate-200 lg:col-span-2">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider mb-4 border-b pb-2">Grados Habilitados (<?php echo count($lista_grados); ?>)</h3>
                    
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm text-xs">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <th class="p-4 w-24 text-center">Nivel</th>
                                    <th class="p-4">Nombre del Curso</th>
                                    <th class="p-4 text-center w-28">ID Interno</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                                <?php if(empty($lista_grados)): ?>
                                    <tr>
                                        <td colspan="3" class="p-8 text-center text-slate-400 italic">No hay grados creados en la base de datos todavía.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lista_grados as $grd): ?>
                                        <tr class="hover:bg-slate-50/50 transition-all">
                                            <td class="p-4 text-center">
                                                <span class="px-2.5 py-1 rounded-lg font-black bg-blue-50 text-blue-700">
                                                    <?php echo $grd['numero']; ?>°
                                                </span>
                                            </td>
                                            <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($grd['nombre']); ?></td>
                                            <td class="p-4 text-center text-slate-400">#<?php echo $grd['id']; ?></td>
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