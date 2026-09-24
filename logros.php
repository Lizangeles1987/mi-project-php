<?php
/**
 * ARCHIVO: logros_v3.php
 * Sistema Integral de Calificaciones con CRUD (Editar/Eliminar)
 */

// --- 1. CONFIGURACIÓN Y CONEXIÓN ---
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "aula_virtual";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";
$tipo_alerta = "";

// --- 2. LÓGICA CRUD (ACCIONES) ---

// A. ELIMINAR REGISTRO
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM logros_academicos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensaje = "Registro eliminado correctamente.";
        $tipo_alerta = "success";
    }
}

// B. GUARDAR O ACTUALIZAR
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    $nombre = $_POST['nombre_estudiante'];
    $nota = (float)$_POST['nota_logro'];
    $materia = "INFORMÁTICA";
    $periodo = 1;

    if ($_POST['accion'] == "crear") {
        $stmt = $conn->prepare("INSERT INTO logros_academicos (nombre, nota, nombre_materia, periodo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $nombre, $nota, $materia, $periodo);
        if ($stmt->execute()) {
            $mensaje = "¡Nota guardada correctamente!";
            $tipo_alerta = "success";
        }
    } 
    elseif ($_POST['accion'] == "editar") {
        $id = (int)$_POST['id_registro'];
        $stmt = $conn->prepare("UPDATE logros_academicos SET nombre = ?, nota = ? WHERE id = ?");
        $stmt->bind_param("sdi", $nombre, $nota, $id);
        if ($stmt->execute()) {
            $mensaje = "¡Registro actualizado!";
            $tipo_alerta = "success";
        }
    }
}

// --- 3. OBTENER DATOS ---
$result = $conn->query("SELECT * FROM logros_academicos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aula Virtual - Panel de Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">

    <!-- SIDEBAR (Navegación de Módulos) -->
    <aside class="w-full md:w-64 bg-slate-900 text-slate-300 flex flex-col shadow-2xl z-20">
        <div class="p-6">
            <div class="flex items-center gap-3 text-white mb-8">
                <div class="p-2 bg-indigo-500 rounded-lg"><i class="fas fa-university"></i></div>
                <span class="font-extrabold tracking-tight">AULA VIRTUAL</span>
            </div>
            
            <nav class="space-y-1">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 px-3">Académico</p>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-600/10 text-indigo-400 border border-indigo-600/20">
                    <i class="fas fa-chart-line w-5"></i> <span class="text-sm font-semibold">Calificaciones</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all">
                    <i class="fas fa-tasks w-5 text-slate-500"></i> <span class="text-sm">Tareas</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all">
                    <i class="fas fa-file-export w-5 text-slate-500"></i> <span class="text-sm">Entregas</span>
                </a>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-6 mb-2 px-3">Administración</p>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all">
                    <i class="fas fa-users w-5 text-slate-500"></i> <span class="text-sm">Usuarios</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition-all">
                    <i class="fas fa-cog w-5 text-slate-500"></i> <span class="text-sm">Configuración</span>
                </a>
            </nav>
        </div>
        <div class="mt-auto p-6 border-t border-slate-800">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-bold">YM</div>
                <div class="text-xs">
                    <p class="text-white font-bold">Yohiner Mestra</p>
                    <p class="text-slate-500">Docente Admin</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-4 md:p-8 lg:p-12 overflow-y-auto">
        
        <!-- Alertas -->
        <?php if($mensaje): ?>
            <div class="mb-6 flex items-center gap-3 bg-white border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm animate-bounce">
                <i class="fas fa-check-circle text-green-500"></i>
                <p class="text-sm font-bold text-slate-700"><?php echo $mensaje; ?></p>
            </div>
        <?php endif; ?>

        <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">Planilla de Calificaciones</h1>
                <p class="text-slate-500 text-sm italic">Gestión de Logros Académicos - Primer Periodo</p>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                    <i class="fas fa-download mr-2"></i> EXPORTAR
                </button>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            
            <!-- FORMULARIO DINÁMICO (CREAR/EDITAR) -->
            <div class="xl:col-span-4">
                <div class="glass-card rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                    <div class="bg-indigo-600 p-4 text-white">
                        <h2 id="form-title" class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-user-plus"></i> Nuevo Registro
                        </h2>
                    </div>
                    <form id="main-form" action="" method="POST" class="p-6 space-y-5">
                        <input type="hidden" name="accion" id="form-accion" value="crear">
                        <input type="hidden" name="id_registro" id="form-id" value="">

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Nombre del Estudiante</label>
                            <input type="text" name="nombre_estudiante" id="input-nombre" required placeholder="Ej: Matias Cuello"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Nota Final (0.0 - 10.0)</label>
                            <input type="number" step="0.1" min="0" max="10" name="nota_logro" id="input-nota" required placeholder="0.0"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-mono">
                        </div>
                        
                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl text-xs uppercase tracking-widest shadow-lg shadow-indigo-200 transition-all">
                                <i class="fas fa-save mr-2"></i> Guardar Calificación
                            </button>
                            <button type="button" id="btn-cancelar" onclick="resetForm()" class="hidden w-full bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold py-3 rounded-xl text-[10px] uppercase tracking-widest transition-all">
                                Cancelar Edición
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLA DE RESULTADOS -->
            <div class="xl:col-span-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] text-slate-400 uppercase font-bold tracking-widest">
                                <th class="px-6 py-5 text-left">Estudiante</th>
                                <th class="px-6 py-5 text-center">Nota</th>
                                <th class="px-6 py-5 text-center">Estado</th>
                                <th class="px-6 py-5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="group hover:bg-indigo-50/30 transition-all">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-700 uppercase text-sm"><?php echo htmlspecialchars($row['nombre']); ?></p>
                                    <p class="text-[10px] text-slate-400 font-medium tracking-wide">CURSO: OCTAVO-01 • <?php echo $row['nombre_materia']; ?></p>
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold">
                                    <span class="inline-block px-3 py-1 bg-slate-100 rounded-lg text-indigo-600 border border-slate-200">
                                        <?php echo number_format($row['nota'], 1); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if($row['nota'] >= 6.0): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-[9px] font-black rounded-full uppercase border border-green-200 italic shadow-sm shadow-green-50">Aprobó</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-rose-100 text-rose-700 text-[9px] font-black rounded-full uppercase border border-rose-200 italic shadow-sm shadow-rose-50">Reprobó</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <!-- Botón Editar con JS -->
                                        <button onclick="prepararEdicion(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nombre']); ?>', <?php echo $row['nota']; ?>)" 
                                            class="h-9 w-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <!-- Botón Eliminar con Confirmación -->
                                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar este registro?')" 
                                            class="h-9 w-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS PARA DINAMISMO -->
    <script>
        function prepararEdicion(id, nombre, nota) {
            // Cambiar textos del formulario
            document.getElementById('form-title').innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar Registro';
            document.getElementById('form-accion').value = "editar";
            document.getElementById('form-id').value = id;
            
            // Llenar inputs
            document.getElementById('input-nombre').value = nombre;
            document.getElementById('input-nota').value = nota;
            
            // Mostrar botón cancelar
            document.getElementById('btn-cancelar').classList.remove('hidden');
            
            // Focus al primer input
            document.getElementById('input-nombre').focus();
            
            // Scroll suave al form si es móvil
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        function resetForm() {
            document.getElementById('main-form').reset();
            document.getElementById('form-title').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Registro';
            document.getElementById('form-accion').value = "crear";
            document.getElementById('btn-cancelar').classList.add('hidden');
        }
    </script>

</body>
</html>