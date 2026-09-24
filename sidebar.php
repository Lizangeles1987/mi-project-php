<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nombre_usuario = isset($_SESSION['nombre']) ? trim($_SESSION['nombre']) : '';
$email_actual = isset($_SESSION['email']) ? trim($_SESSION['email']) : '';
$rol_actual = isset($_SESSION['rol']) ? trim($_SESSION['rol']) : '';

// Condición de Admin absoluta
$es_admin = ($email_actual === 'admin@aula.com' || $nombre_usuario === 'Usuario Prueba' || $rol_actual === 'admin');

$pagina_activa = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar text-white p-6 flex flex-col justify-between" style="background-color: #0f172a; min-height: 100vh; width: 260px;">
    <div>
        <div class="flex items-center gap-3 mb-8">
            <div class="bg-blue-600 p-2 rounded-xl text-lg">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="font-bold text-xl tracking-wider">Aula Primaria</h1>
        </div>

        <nav class="space-y-3">
            
            <a href="cursos.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-medium <?php echo ($pagina_activa == 'cursos.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-400'; ?>">
                <i class="fas fa-th-large"></i> Inicio / Cursos
            </a>

            <?php if ($es_admin): ?>
                <a href="admin_docentes.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-medium <?php echo ($pagina_activa == 'admin_docentes.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-400'; ?>">
                    <i class="fas fa-chalkboard-teacher"></i> Gestión de Docentes
                </a>

                <a href="admin_estudiante.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-medium <?php echo ($pagina_activa == 'admin_estudiante.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-400'; ?>">
                    <i class="fas fa-user-graduate"></i> Gestión Estudiantes
                </a>

                <a href="admin_materias.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-medium <?php echo ($pagina_activa == 'admin_materias.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-400'; ?>">
                    <i class="fas fa-book"></i> Gestión Materias
                </a>

                <a href="admin_asignaciones.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-medium <?php echo ($pagina_activa == 'admin_asignaciones.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-400'; ?>">
                    <i class="fas fa-link"></i> Asignar Materias
                </a>
                
                <a href="matricula_excel.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-medium <?php echo ($pagina_activa == 'matricula_excel.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-400'; ?>">
                    <i class="fas fa-file-excel"></i> Matrícula Masiva Excel
                </a>
            <?php endif; ?>

        </nav>
    </div>

    <div>
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-all text-sm font-medium">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</div>