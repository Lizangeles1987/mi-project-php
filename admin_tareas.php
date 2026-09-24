<?php
session_start();
include 'db.php';

// Seguridad: Si no hay sesión, al login
if (!isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

// Consultamos todas las entregas de la base de datos
// Nota: Usamos los nombres de columna exactos de tu tabla: alumno_nombre, materia, archivo_nombre
$sql = "SELECT * FROM entregas ORDER BY fecha_entrega DESC";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Profesor - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="max-w-6xl mx-auto">
            
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Panel de Revisión</h1>
                    <p class="text-slate-500 font-medium">Gestiona las tareas entregadas por los alumnos</p>
                </div>
                <div class="bg-white px-6 py-4 rounded-3xl shadow-sm border border-slate-200 text-center">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Total Recibidas</span>
                    <span class="text-3xl font-black text-blue-600"><?php echo mysqli_num_rows($resultado); ?></span>
                </div>
            </header>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-200 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Estudiante</th>
                            <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Materia</th>
                            <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Archivo</th>
                            <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Calificar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php while($fila = mysqli_fetch_assoc($resultado)) { ?>
                        <tr class="hover:bg-blue-50/30 transition-colors duration-200">
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-200">
                                        <?php echo strtoupper(substr($fila['alumno_nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800"><?php echo $fila['alumno_nombre']; ?></p>
                                        <p class="text-[10px] text-slate-400 font-medium"><?php echo $fila['fecha_entrega']; ?></p>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold uppercase tracking-tighter">
                                    <?php echo $fila['materia']; ?>
                                </span>
                            </td>

                            <td class="px-8 py-6 text-center">
                                <a href="subidas/<?php echo $fila['archivo_nombre']; ?>" download class="inline-flex items-center space-x-2 text-blue-600 hover:text-blue-800 font-bold text-sm bg-blue-50 px-4 py-2 rounded-xl transition">
                                    <i class="fas fa-download"></i>
                                    <span>Bajar Archivo</span>
                                </a>
                            </td>

                            <td class="px-8 py-6">
                                <form action="calificar.php" method="POST" class="flex items-center justify-center space-x-2">
                                    <input type="hidden" name="id_entrega" value="<?php echo $fila['id']; ?>">
                                    <input type="number" name="nota" step="0.1" max="10" min="0" 
                                           placeholder="0.0" 
                                           class="w-20 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-blue-500 outline-none transition"
                                           value="<?php echo isset($fila['nota']) ? $fila['nota'] : ''; ?>">
                                    <button type="submit" class="bg-slate-900 hover:bg-green-600 text-white p-2.5 rounded-xl transition shadow-md">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php if(mysqli_num_rows($resultado) == 0) { ?>
                        <tr>
                            <td colspan="4" class="py-24 text-center">
                                <div class="opacity-20 mb-4 text-6xl"><i class="fas fa-folder-open"></i></div>
                                <p class="text-slate-400 font-bold italic tracking-wide">No hay tareas para revisar por ahora.</p>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>