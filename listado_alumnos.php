<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectamos el grado que viene por la URL
$grado_seleccionado = isset($_GET['grado']) ? intval($_GET['grado']) : 2;

// Evaluamos los roles reales de la sesión
$nombre_usuario = isset($_SESSION['nombre']) ? trim($_SESSION['nombre']) : '';
$rol_sistema = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : 'profesor';

$es_admin = ($rol_sistema === 'admin' || $rol_sistema === 'administrador' || $nombre_usuario === 'Usuario Prueba');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos de <?php echo $grado_seleccionado; ?>° - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background-color: #0f172a; min-height: 100vh; width: 260px; }
        .main-content { background-color: #f8fafc; flex: 1; }
    </style>
</head>
<body class="bg-slate-50 flex">

    <?php include 'sidebar.php'; ?>

    <main class="main-content p-8">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Estudiantes de <?php echo $grado_seleccionado; ?>° de Primaria</h2>
                <p class="text-slate-500 text-sm">Profesor: Indique la nota y escriba el logro correspondiente para cada estudiante</p>
            </div>
        </header>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white text-sm">
                        <th class="p-4">ID</th>
                        <th class="p-4">Nombre del Estudiante</th>
                        <th class="p-4">Grado</th>
                        <th class="p-4 text-center">Asignación de Notas y Logros (Acción del Profesor)</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                    
                    <tr class="hover:bg-slate-50/80 transition-all">
                        <td class="p-4 font-bold">1</td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-800">Carlos Andrés Mendoza</div>
                        </td>
                        <td class="p-4"><?php echo $grado_seleccionado; ?>° Primaria</td>
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-2">
                                <?php if (!$es_admin): ?>
                                    <form action="guardar_nota.php" method="POST" class="flex items-center gap-2 bg-slate-100 p-2 rounded-xl border border-slate-200 w-full max-w-xl">
                                        <input type="hidden" name="alumno_id" value="1">
                                        <input type="hidden" name="grado" value="<?php echo $grado_seleccionado; ?>">
                                        
                                        <input type="number" step="0.1" min="1" max="5" name="nota" placeholder="Nota" class="w-16 px-2 py-1.5 text-center bg-white border border-slate-300 rounded-lg text-xs font-bold focus:outline-none" required>
                                        
                                        <input type="text" name="logro" placeholder="Escriba el logro obtenido aquí..." class="flex-1 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none" required>
                                        
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                                            Guardar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">Vista de Administrador (Solo lectura)</span>
                                <?php endif; ?>
                                
                                <a href="boletin.php?id=1" class="border border-slate-300 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-slate-100 transition-all" title="Ver Boletín">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50/80 transition-all">
                        <td class="p-4 font-bold">2</td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-800">Mariana Sofia Delgado</div>
                        </td>
                        <td class="p-4"><?php echo $grado_seleccionado; ?>° Primaria</td>
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-2">
                                <?php if (!$es_admin): ?>
                                    <form action="guardar_nota.php" method="POST" class="flex items-center gap-2 bg-slate-100 p-2 rounded-xl border border-slate-200 w-full max-w-xl">
                                        <input type="hidden" name="alumno_id" value="2">
                                        <input type="hidden" name="grado" value="<?php echo $grado_seleccionado; ?>">
                                        
                                        <input type="number" step="0.1" min="1" max="5" name="nota" placeholder="Nota" class="w-16 px-2 py-1.5 text-center bg-white border border-slate-300 rounded-lg text-xs font-bold focus:outline-none" required>
                                        
                                        <input type="text" name="logro" placeholder="Escriba el logro obtenido aquí..." class="flex-1 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none" required>
                                        
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                                            Guardar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">Vista de Administrador (Solo lectura)</span>
                                <?php endif; ?>
                                
                                <a href="boletin.php?id=2" class="border border-slate-300 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-slate-100 transition-all" title="Ver Boletín">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            <a href="cursos.php" class="text-slate-600 hover:text-slate-900 text-sm font-semibold"><i class="fas fa-arrow-left mr-1"></i> Volver a los Grados</a>
        </div>
    </main>

</body>
</html>