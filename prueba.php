<?php
// =========================================================================
// 1. SEGURIDAD DE SESIÓN (LOGIN)
// =========================================================================
session_start();
if (!isset($_SESSION['usuario']) && !isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

// =========================================================================
// 2. CONEXIÓN A LA BASE DE DATOS
// =========================================================================
include 'conexion.php'; // Usa tu archivo con la variable $conexion

$mensaje = "";
$tipo_alerta = "";

// =========================================================================
// 3. PROCESAR ACCIÓN: ELIMINAR LOGRO (DELETE)
// =========================================================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    
    $stmt = mysqli_prepare($conexion, "DELETE FROM logros_academicos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_eliminar);
    
    if (mysqli_stmt_execute($stmt)) {
        $mensaje = "Logro académico eliminado de la planilla con éxito.";
        $tipo_alerta = "success";
    } else {
        $mensaje = "Error al eliminar: " . mysqli_error($conexion);
        $tipo_alerta = "error";
    }
    mysqli_stmt_close($stmt);
}

// =========================================================================
// 4. PROCESAR ACCIÓN: GUARDAR (INSERT) O ACTUALIZAR (UPDATE)
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_accion'])) {
    $codigo_materia = strtoupper(trim($_POST['codigo_materia']));
    $nombre_materia = strtoupper(trim($_POST['nombre_materia']));
    $periodo = (int)$_POST['periodo'];
    $tipo_logro = $_POST['tipo_logro'];
    $descripcion = trim($_POST['descripcion']);

    if ($_POST['btn_accion'] == "crear") {
        // Insertar nuevo logro
        $stmt = mysqli_prepare($conexion, "INSERT INTO logros_academicos (codigo_materia, nombre_materia, periodo, tipo_logro, descripcion) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiss", $codigo_materia, $nombre_materia, $periodo, $tipo_logro, $descripcion);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "¡Logro registrado correctamente para la materia $nombre_materia!";
            $tipo_alerta = "success";
        } else {
            $mensaje = "Error al guardar: " . mysqli_error($conexion);
            $tipo_alerta = "error";
        }
        mysqli_stmt_close($stmt);
        
    } elseif ($_POST['btn_accion'] == "editar") {
        // Actualizar logro existente
        $id_editar = (int)$_POST['id_registro'];
        $stmt = mysqli_prepare($conexion, "UPDATE logros_academicos SET codigo_materia = ?, nombre_materia = ?, periodo = ?, tipo_logro = ?, descripcion = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssissi", $codigo_materia, $nombre_materia, $periodo, $tipo_logro, $descripcion, $id_editar);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "¡Logro académico actualizado correctamente!";
            $tipo_alerta = "success";
        } else {
            $mensaje = "Error al actualizar: " . mysqli_error($conexion);
            $tipo_alerta = "error";
        }
        mysqli_stmt_close($stmt);
    }
}

// =========================================================================
// 5. LEER LOS REGISTROS DE LA BASE DE DATOS (SELECT)
// =========================================================================
$query = "SELECT * FROM logros_academicos ORDER BY id DESC";
$resultado_tabla = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilla de Logros - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 flex min-h-screen overflow-hidden">

    <!-- SIDEBAR INTEGRADO -->
    <?php include 'sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Header Superior -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xs bg-blue-50 text-blue-600 border border-blue-200 font-bold px-3 py-1 rounded-full uppercase tracking-wider">Gestión de Logros</span>
                <h2 class="text-xs font-bold text-slate-400">Control Académico</h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-700 uppercase"><?php echo htmlspecialchars($_SESSION['usuario'] ?? $_SESSION['nombre'] ?? 'Docente'); ?></p>
                    <p class="text-[10px] text-green-500 font-bold uppercase tracking-wider">Docente Conectado</p>
                </div>
                <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-black shadow">
                    YM
                </div>
            </div>
        </header>

        <!-- Zona de Trabajo -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">
            
            <!-- Notificación emergente -->
            <?php if($mensaje): ?>
                <div class="bg-white border-l-4 <?php echo ($tipo_alerta == 'success') ? 'border-emerald-500' : 'border-rose-500'; ?> p-4 rounded-r-xl shadow-md flex items-center gap-3">
                    <i class="fas <?php echo ($tipo_alerta == 'success') ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-rose-500'; ?> text-lg"></i>
                    <p class="text-sm font-semibold text-slate-700"><?php echo $mensaje; ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                <!-- COLUMNA IZQUIERDA: FORMULARIO (CREAR / EDITAR) -->
                <div class="xl:col-span-4">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-blue-600 p-4 text-white flex justify-between items-center">
                            <h3 id="form-title" class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-plus-circle"></i> Nuevo Logro
                            </h3>
                            <span class="text-[9px] font-extrabold bg-blue-700 px-2.5 py-1 rounded-md">OCTAVO - 01</span>
                        </div>
                        
                        <!-- El action ahora apunta correctamente a calificaciones1.php -->
                        <form id="form-registro" action="calificaciones1.php" method="POST" class="p-6 space-y-4">
                            <!-- Inputs de Control Ocultos -->
                            <input type="hidden" name="btn_accion" id="form-accion" value="crear">
                            <input type="hidden" name="id_registro" id="form-id" value="">

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Código de la Materia</label>
                                <input type="text" name="codigo_materia" id="input-codigo" required placeholder="Ej: MAT-01"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all uppercase font-medium">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Nombre de la Materia</label>
                                <input type="text" name="nombre_materia" id="input-materia" required placeholder="Ej: MATEMÁTICAS"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all uppercase font-medium">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Periodo</label>
                                    <select name="periodo" id="input-periodo" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                                        <option value="1">Periodo 1</option>
                                        <option value="2">Periodo 2</option>
                                        <option value="3">Periodo 3</option>
                                        <option value="4">Periodo 4</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Tipo de Logro</label>
                                    <select name="tipo_logro" id="input-tipo" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                                        <option value="Cognitivo">Cognitivo</option>
                                        <option value="Procedimental">Procedimental</option>
                                        <option value="Actitudinal">Actitudinal</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Descripción del Desempeño</label>
                                <textarea name="descripcion" id="input-descripcion" required rows="4" placeholder="Escribe el logro o indicador de desempeño..."
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-700"></textarea>
                            </div>

                            <div class="pt-2 space-y-2">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg shadow-blue-200">
                                    <i class="fas fa-save mr-1.5"></i> Guardar Logro
                                </button>
                                <button type="button" id="btn-cancelar" onclick="cancelarEdicion()" class="hidden w-full bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold py-2 rounded-xl text-[10px] uppercase tracking-wider transition-all">
                                    Cancelar Edición
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: TABLA DINÁMICA CON BUSCADOR Y FILTROS -->
                <div class="xl:col-span-8 space-y-4">
                    
                    <!-- BARRA DE BÚSQUEDA Y FILTRADO RÁPIDO -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative w-full md:w-72">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="buscador-logros" onkeyup="filtrarCriterios()" placeholder="Buscar por código o materia..." 
                                class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                        </div>
                        
                        <!-- Filtros por tipo de logro en un clic -->
                        <div class="flex gap-1.5 w-full md:w-auto overflow-x-auto">
                            <button onclick="filtrarPorTipo('Todos', this)" class="btn-filtro-tipo bg-blue-600 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all shadow">Todos</button>
                            <button onclick="filtrarPorTipo('Cognitivo', this)" class="btn-filtro-tipo bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all">Cognitivo</button>
                            <button onclick="filtrarPorTipo('Procedimental', this)" class="btn-filtro-tipo bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all">Procedimental</button>
                            <button onclick="filtrarPorTipo('Actitudinal', this)" class="btn-filtro-tipo bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all">Actitudinal</button>
                        </div>
                    </div>

                    <!-- TABLA -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-4 bg-slate-50/50 border-b border-slate-200 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lista de Logros y Criterios</span>
                            <span id="contador-registros" class="bg-blue-50 text-blue-600 text-[10px] font-bold px-3 py-1 rounded-full border border-blue-100">
                                <?php echo mysqli_num_rows($resultado_tabla); ?> Registros
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse" id="tabla-logros">
                                <thead>
                                    <tr class="bg-slate-100/30 text-[10px] text-slate-400 uppercase font-bold tracking-wider border-b border-slate-100">
                                        <th class="px-6 py-4">Materia / Código</th>
                                        <th class="px-6 py-4">Descripción del Criterio</th>
                                        <th class="px-6 py-4 text-center">Tipo</th>
                                        <th class="px-6 py-4 text-right pr-8">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <?php if(mysqli_num_rows($resultado_tabla) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($resultado_tabla)): ?>
                                        <tr class="fila-logro hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-700 uppercase td-materia"><?php echo htmlspecialchars($row['nombre_materia']); ?></div>
                                                <div class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider td-codigo">CÓD: <?php echo htmlspecialchars($row['codigo_materia']); ?></div>
                                                <div class="text-[9px] text-blue-600 font-bold mt-1">PERIODO <?php echo $row['periodo']; ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 max-w-xs break-words italic td-descripcion">
                                                "<?php echo htmlspecialchars($row['descripcion']); ?>"
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="span-tipo inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase <?php 
                                                    if($row['tipo_logro'] == 'Cognitivo') echo 'bg-blue-50 text-blue-700 border border-blue-100';
                                                    elseif($row['tipo_logro'] == 'Procedimental') echo 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                                                    else echo 'bg-amber-50 text-amber-700 border border-amber-100';
                                                ?>">
                                                    <?php echo $row['tipo_logro']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right pr-6">
                                                <div class="flex justify-end gap-2">
                                                    <!-- Botón de Modificación (JavaScript) -->
                                                    <button onclick="activarEdicion(<?php echo $row['id']; ?>, '<?php echo addslashes($row['codigo_materia']); ?>', '<?php echo addslashes($row['nombre_materia']); ?>', <?php echo $row['periodo']; ?>, '<?php echo $row['tipo_logro']; ?>', '<?php echo addslashes($row['descripcion']); ?>')" 
                                                        class="h-8 w-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all" title="Editar">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </button>
                                                    <!-- Botón de Eliminación (apunta a calificaciones1.php) -->
                                                    <a href="calificaciones1.php?eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este logro académico?')" 
                                                        class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all" title="Eliminar">
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr id="sin-registros">
                                            <td colspan="4" class="px-6 py-16 text-center text-slate-400 italic text-sm">
                                                <i class="fas fa-folder-open text-3xl mb-2 block text-slate-200"></i>
                                                No se encontraron registros de logros académicos en la tabla.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- JS INTERNO PARA LA EDICIÓN DINÁMICA Y BÚSQUEDA AVANZADA -->
    <script>
        let filtroTipoActual = 'Todos';

        function activarEdicion(id, codigo, materia, periodo, tipo, descripcion) {
            // Reconfigurar los textos del panel
            document.getElementById('form-title').innerHTML = '<i class="fas fa-sync-alt"></i> Editar Logro';
            document.getElementById('form-accion').value = "editar";
            document.getElementById('form-id').value = id;
            
            // Subir los datos seleccionados a los inputs
            document.getElementById('input-codigo').value = codigo;
            document.getElementById('input-materia').value = materia;
            document.getElementById('input-periodo').value = periodo;
            document.getElementById('input-tipo').value = tipo;
            document.getElementById('input-descripcion').value = descripcion;
            
            // Mostrar botón cancelar
            document.getElementById('btn-cancelar').classList.remove('hidden');
            document.getElementById('input-codigo').focus();
        }

        function cancelarEdicion() {
            // Devolver el formulario a su estado por defecto
            document.getElementById('form-registro').reset();
            document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle"></i> Nuevo Logro';
            document.getElementById('form-accion').value = "crear";
            document.getElementById('btn-cancelar').classList.add('hidden');
        }

        // =========================================================================
        // MOTOR DE BÚSQUEDA Y FILTRADO CLIENT-SIDE (SUPER RÁPIDO)
        // =========================================================================
        function filtrarCriterios() {
            const buscador = document.getElementById('buscador-logros');
            const textoBuscado = buscador.value.toUpperCase();
            const filas = document.getElementsByClassName('fila-logro');
            let contadorVisibles = 0;

            for (let i = 0; i < filas.length; i++) {
                const fila = filas[i];
                const materia = fila.querySelector('.td-materia').textContent.toUpperCase();
                const codigo = fila.querySelector('.td-codigo').textContent.toUpperCase();
                const descripcion = fila.querySelector('.td-descripcion').textContent.toUpperCase();
                const tipoSpan = fila.querySelector('.span-tipo').textContent.trim();

                // Validar texto buscado
                const coincideTexto = (materia.indexOf(textoBuscado) > -1 || codigo.indexOf(textoBuscado) > -1 || descripcion.indexOf(textoBuscado) > -1);
                
                // Validar filtro de tipo de logro activo
                const coincideTipo = (filtroTipoActual === 'Todos' || tipoSpan === filtroTipoActual);

                if (coincideTexto && coincideTipo) {
                    fila.style.display = "";
                    contadorVisibles++;
                } else {
                    fila.style.display = "none";
                }
            }

            // Actualizar contador en pantalla
            document.getElementById('contador-registros').innerText = `${contadorVisibles} Coincidencias`;
        }

        function filtrarPorTipo(tipo, boton) {
            filtroTipoActual = tipo;

            // Actualizar estilo visual de los botones de filtro
            const botones = document.getElementsByClassName('btn-filtro-tipo');
            for (let i = 0; i < botones.length; i++) {
                botones[i].className = "btn-filtro-tipo bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all";
            }
            boton.className = "btn-filtro-tipo bg-blue-600 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all shadow";

            // Ejecutar la función de filtrado general
            filtrarCriterios();
        }
    </script>
</body>
</html>