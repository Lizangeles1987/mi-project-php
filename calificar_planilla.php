<?php
include 'config.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) && !isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

$nombre_usuario = isset($_SESSION['nombre']) ? trim($_SESSION['nombre']) : 'Docente';
$rol_actual = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : 'profesor';

$grado_sel = isset($_GET['grado']) ? intval($_GET['grado']) : 0;
$estudiantes = [];
$mensaje = "";
$tipo_alerta = "";

// Cargamos los códigos automáticos generados para el buscador inteligente
$todos_los_logros = [];
try {
    $stmtAllL = $pdo->query("SELECT IFNULL(codigo, CONCAT('LOG-', id)) as codigo, descripcion FROM logros");
    $todos_los_logros = $stmtAllL->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e){}

if ($grado_sel > 0) {
    try {
        $stmtEst = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE rol = 'estudiante' AND grado = ? ORDER BY nombre ASC");
        $stmtEst->execute([$grado_sel]);
        $estudiantes = $stmtEst->fetchAll();
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_alerta = "error";
    }
}

if (isset($_POST['guardar_planilla'])) {
    $logro_texto = trim($_POST['logro_seleccionado']);
    $estudiantes_marcados = isset($_POST['marcador']) ? $_POST['marcador'] : [];
    $notas_array = $_POST['notas'];

    if (!empty($logro_texto) && !empty($estudiantes_marcados)) {
        try {
            $pdo->beginTransaction();
            foreach ($estudiantes_marcados as $id_estudiante) {
                $nota_estudiante = floatval($notas_array[$id_estudiante]);
                if ($nota_estudiante >= 1.0 && $nota_estudiante <= 10.0) {
                    
                    $stmtNom = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                    $stmtNom->execute([$id_estudiante]);
                    $user_data = $stmtNom->fetch();
                    $nombre_estudiante = $user_data ? $user_data['nombre'] : 'Estudiante';

                    $nom_materia = 'Matemáticas';
                    $periodo_actual = 1;

                    $stmtCheck = $pdo->prepare("SELECT id FROM logros_academicos WHERE nombre = ? AND nombre_materia = ? AND periodo = ?");
                    $stmtCheck->execute([$nombre_estudiante, $nom_materia, $periodo_actual]);
                    $existe = $stmtCheck->fetch();

                    if ($existe) {
                        $pdo->prepare("UPDATE logros_academicos SET nota = ?, descripcion = ? WHERE id = ?")->execute([$nota_estudiante, $logro_texto, $existe['id']]);
                    } else {
                        $pdo->prepare("INSERT INTO logros_academicos (codigo_materia, nombre_materia, periodo, nombre, nota, descripcion) VALUES ('MAT01', ?, ?, ?, ?, ?)")->execute([$nom_materia, $periodo_actual, $nombre_estudiante, $nota_estudiante, $logro_texto]);
                    }
                }
            }
            $pdo->commit();
            $mensaje = "🚀 ¡Calificaciones guardadas exitosamente para los alumnos seleccionados!";
            $tipo_alerta = "success";
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = "❌ Error: " . $e->getMessage();
            $tipo_alerta = "error";
        }
    } else {
        $mensaje = "⚠️ Por favor busca un código de logro válido y marca las casillas de los alumnos a calificar.";
        $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla Masiva Interactiva</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-4xl bg-white p-8 rounded-[30px] shadow-sm border border-slate-200">
            
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Planilla de Calificación Masiva</h2>
                    <p class="text-xs text-slate-400">Digita el código del logro, selecciona alumnos y haz doble clic en la nota para copiarla a los marcados.</p>
                </div>
                <a href="banco_logros.php" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Redactar en Biblioteca
                </a>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="p-4 mb-6 rounded-xl text-sm font-semibold <?php echo ($tipo_alerta == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">CURSO / GRADO</label>
                <select onchange="window.location.href='calificar_planilla.php?grado='+this.value" class="p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer">
                    <option value="">-- Selecciona el Grado --</option>
                    <?php for($g=1; $g<=5; $g++): ?>
                        <option value="<?php echo $g; ?>" <?php echo ($grado_sel == $g) ? 'selected' : ''; ?>><?php echo $g; ?>° de Primaria</option>
                    <?php endfor; ?>
                </select>
            </div>

            <?php if ($grado_sel > 0): ?>
                <form method="POST" action="calificar_planilla.php?grado=<?php echo $grado_sel; ?>" class="space-y-4">
                    
                    <div class="bg-blue-50/50 border border-blue-200 p-4 rounded-2xl">
                        <label class="block text-xs font-bold text-blue-800 mb-1">🔍 1. DIGITE EL CÓDIGO AUTOMÁTICO:</label>
                        <div class="flex gap-2">
                            <input type="text" id="codigo_buscar" placeholder="Ej: LOG-1" class="w-1/4 p-2.5 bg-white border border-blue-200 rounded-xl text-xs font-black uppercase outline-none text-blue-600 tracking-wider">
                            <button type="button" onclick="buscarLogroPorCodigo()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                                <i class="fas fa-search"></i> Buscar Logro
                            </button>
                            <input type="text" id="logro_visible" readonly placeholder="Presiona la lupa derecha para buscar en tu banco de logros..." required class="w-3/4 p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 outline-none font-medium">
                            <input type="hidden" name="logro_seleccionado" id="logro_real">
                        </div>
                    </div>

                    <div id="contador_seleccion" class="hidden bg-amber-50 border border-amber-200 text-amber-800 p-3.5 rounded-xl text-xs font-bold flex items-center gap-2 transition-all animate-pulse">
                        <i class="fas fa-user-check text-sm text-amber-600"></i>
                        <span id="texto_contador">Has seleccionado 0 estudiantes para este logro.</span>
                    </div>

                    <div class="mt-6">
                        <label class="block text-xs font-bold text-slate-500 mb-2">👥 2. SELECCIONE LOS ALUMNOS Y DIGITE SUS NOTAS:</label>
                        <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm text-xs">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                        <th class="p-4 text-center w-16"><input type="checkbox" onclick="toggleTodos(this)" class="scale-110 cursor-pointer"></th>
                                        <th class="p-4">Nombre del Alumno</th>
                                        <th class="p-4 text-center w-48">Nota Definitiva</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                                    <?php if(empty($estudiantes)): ?>
                                        <tr>
                                            <td colspan="3" class="p-8 text-center text-slate-400 italic">No hay estudiantes registrados en este curso dentro de tu base de datos.</td>
                                        </tr>
                                    <?php id: else: ?>
                                        <?php foreach ($estudiantes as $est): ?>
                                            <tr id="fila-<?php echo $est['id']; ?>" class="hover:bg-slate-50/50 transition-all">
                                                <td class="p-4 text-center">
                                                    <input type="checkbox" name="marcador[]" value="<?php echo $est['id']; ?>" onchange="actualizarContadorYAjustarFila(this)" class="chk-alumno scale-110 cursor-pointer">
                                                </td>
                                                <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($est['nombre']); ?></td>
                                                <td class="p-4 text-center">
                                                    <div class="flex flex-col items-center gap-1">
                                                        <input type="number" name="notas[<?php echo $est['id']; ?>]" step="0.1" min="1.0" max="10.0" value="0.0" 
                                                               ondblclick="duplicarNotaMasiva(this)"
                                                               class="input-nota w-24 p-2 bg-slate-50 border border-slate-200 rounded-xl text-center font-black font-mono text-slate-700 outline-none focus:border-blue-500 cursor-pointer" title="Doble clic para copiar esta nota a todos los marcados">
                                                        <span class="text-[9px] text-slate-400 tracking-tight font-normal">💡 Doble clic para clonar</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if(!empty($estudiantes)): ?>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" name="guardar_planilla" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3.5 rounded-xl text-xs uppercase tracking-wider transition-all shadow-md">
                                Guardar Calificaciones
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const bibliotecaLogros = <?php echo json_encode($todos_los_logros); ?>;

        function buscarLogroPorCodigo() {
            const codigoBuscado = document.getElementById('codigo_buscar').value.trim().toUpperCase();
            if(!codigoBuscado) {
                alert("Por favor, escribe un código primero (Ej: LOG-1).");
                return;
            }

            const logroEncontrado = bibliotecaLogros.find(l => l.codigo.toUpperCase() === codigoBuscado);

            if(logroEncontrado) {
                document.getElementById('logro_visible').value = logroEncontrado.descripcion;
                document.getElementById('logro_real').value = logroEncontrado.descripcion;
            } else {
                alert("❌ Código no encontrado en tu banco de logros. Intenta con otro número.");
                document.getElementById('logro_visible').value = "";
                document.getElementById('logro_real').value = "";
            }
        }

        // 🌟 ACTUALIZA EL CONTADOR Y CAMBIA EL COLOR DE LA FILA SELECCIONADA
        function actualizarContadorYAjustarFila(checkbox) {
            const idUser = checkbox.value;
            const fila = document.getElementById('fila-' + idUser);
            
            if (checkbox.checked) {
                fila.classList.add('bg-blue-50/60');
            } else {
                fila.classList.remove('bg-blue-50/60');
            }

            let chks = document.querySelectorAll('.chk-alumno:checked');
            const cajaContador = document.getElementById('contador_seleccion');
            const textoContador = document.getElementById('texto_contador');

            if (chks.length > 0) {
                cajaContador.classList.remove('hidden');
                textoContador.innerText = `✨ ¡Has seleccionado ${chks.length} estudiantes para este logro!`;
            } else {
                cajaContador.classList.add('hidden');
            }
        }

        // 🌟 CLONA LA NOTA POR DOBLE CLIC A TODOS LOS QUE TIENEN EL CHECKBOX ACTIVO
        function duplicarNotaMasiva(inputOrigen) {
            const valorNota = parseFloat(inputOrigen.value);
            
            if (valorNota < 1.0 || valorNota > 10.0 || isNaN(valorNota)) {
                alert("⚠️ Ingresa una nota válida entre 1.0 y 10.0 antes de clonarla.");
                return;
            }

            let chksMarcados = document.querySelectorAll('.chk-alumno:checked');
            if (chksMarcados.length === 0) {
                alert("💡 Primero selecciona a los estudiantes usando las casillas de la izquierda.");
                return;
            }

            // Recorremos los marcados para inyectarles el valor
            chksMarcados.forEach(chk => {
                const idEstudiante = chk.value;
                const inputDestino = document.querySelector(`input[name="notas[${idEstudiante}]"]`);
                if (inputDestino) {
                    inputDestino.value = valorNota.toFixed(1);
                }
            });
        }

        function toggleTodos(master) {
            let chks = document.getElementsByClassName('chk-alumno');
            for (let i=0; i<chks.length; i++) { 
                chks[i].checked = master.checked; 
                actualizarContadorYAjustarFila(chks[i]);
            }
        }
    </script>
</body>
</html>