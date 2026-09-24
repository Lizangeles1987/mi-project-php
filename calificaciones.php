<?php
/**
 * ARCHIVO: calificar.php
 * Ubicación: Raíz del proyecto (junto a db.php)
 * Propósito: Interfaz de ingreso de notas con diseño profesional.
 */

// 1. IMPORTANTE: Ajusta el nombre de tu archivo de conexión según tu captura
include 'db.php'; 

// Simulación de datos que normalmente vienen de $_SESSION o $_GET
$docente_nombre = "YOHINER MESTRA";
$docente_id = "1067855890";
$grado_actual = "OCTAVO - 01";
$materia_actual = "GEOMETRIA";
$periodo_actual = "PRIMER PERIODO";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilla de Calificaciones - <?php echo $periodo_actual; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .header-gradient { background: linear-gradient(90deg, #2c7db7 0%, #3b82f6 100%); }
        .table-container { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        <!-- Encabezado Estilo App -->
        <div class="header-gradient text-white rounded-t-lg shadow-lg">
            <div class="flex justify-between items-center p-3 px-6 border-b border-blue-400/30">
                <div class="flex items-center gap-3">
                    <i class="fas fa-edit text-xl"></i>
                    <h1 class="font-bold uppercase tracking-tight text-sm">Planilla de Calificaciones - <?php echo $periodo_actual; ?></h1>
                </div>
                <div class="flex gap-2">
                    <button title="Minimizar" class="hover:bg-white/20 p-1 px-2 rounded">-</button>
                    <button title="Cerrar" class="hover:bg-red-500 p-1 px-2 rounded">×</button>
                </div>
            </div>
            
            <!-- Toolbar de Acciones -->
            <div class="bg-white p-2 flex gap-1 border-b border-gray-200">
                <button class="flex flex-col items-center px-4 py-1 hover:bg-blue-50 rounded group transition-all">
                    <i class="fas fa-save text-gray-400 group-hover:text-blue-600 text-lg"></i>
                    <span class="text-[9px] font-bold text-gray-500 uppercase mt-1">Guardar</span>
                </button>
                <div class="w-[1px] bg-gray-200 my-2 mx-1"></div>
                <button class="flex flex-col items-center px-4 py-1 hover:bg-gray-50 rounded group transition-all">
                    <i class="fas fa-print text-gray-400 group-hover:text-black text-lg"></i>
                    <span class="text-[9px] font-bold text-gray-500 uppercase mt-1">Imprimir</span>
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-0 bg-white shadow-xl rounded-b-lg overflow-hidden">
            
            <!-- Sidebar de Información (Izquierda) -->
            <div class="w-full lg:w-72 bg-slate-50 border-r border-gray-200 p-6">
                <div class="space-y-6">
                    <section>
                        <label class="text-[10px] font-black text-blue-600 uppercase tracking-widest block mb-2">Docente</label>
                        <div class="flex items-center gap-3 bg-white p-3 rounded border border-blue-100 shadow-sm">
                            <div class="bg-blue-600 h-8 w-8 rounded-full flex items-center justify-center text-white text-xs">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-700"><?php echo $docente_nombre; ?></span>
                        </div>
                    </section>

                    <section>
                        <label class="text-[10px] font-black text-blue-600 uppercase tracking-widest block mb-2">Curso / Grado</label>
                        <div class="bg-white p-3 rounded border border-gray-200 text-xs">
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-400">Grado:</span>
                                <span class="font-bold"><?php echo $grado_actual; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Sede:</span>
                                <span class="font-medium italic text-slate-600">Principal</span>
                            </div>
                        </div>
                    </section>

                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-[10px] text-blue-800 leading-relaxed">
                            <i class="fas fa-info-circle mr-1"></i>
                            Recuerde que la nota mínima para aprobar es <strong>6.4</strong>. El sistema guardará automáticamente los cambios al presionar "Guardar".
                        </p>
                    </div>
                </div>
            </div>

            <!-- Área Principal de la Tabla (Derecha) -->
            <div class="flex-1 overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-[#2c7db7] text-white text-[10px] uppercase tracking-wider">
                            <th class="p-3 w-12 text-center border-r border-blue-400">#</th>
                            <th class="p-3 text-left border-r border-blue-400">Apellidos y Nombres del Estudiante</th>
                            <th class="p-3 w-28 text-center border-r border-blue-400">Nota</th>
                            <th class="p-3 w-24 text-center border-r border-blue-400">Fallas</th>
                            <th class="p-3 w-32 text-center border-r border-blue-400">Desempeño</th>
                            <th class="p-3 w-24 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php
                        // Ejemplo de bucle. Mañana lo conectarás a tu: 
                        // $query = mysqli_query($conexion, "SELECT * FROM estudiantes WHERE grado = '...' ");
                        $estudiantes = [
                            ["id" => 1, "nombre" => "BRITO SOÑETH SHAILI LORENA", "nota" => 8.5, "fallas" => 0],
                            ["id" => 2, "nombre" => "CASTRO DE LEON THAEL SOFIA", "nota" => 4.2, "fallas" => 1],
                            ["id" => 3, "nombre" => "GONZALEZ GOMEZ JUAN ANDRES", "nota" => 7.0, "fallas" => 0],
                            ["id" => 4, "nombre" => "GOURIYU ORTIZ JOSE ANGEL", "nota" => 9.2, "fallas" => 0],
                            ["id" => 5, "nombre" => "MENDOZA CARRILLO MANUEL EDGAR", "nota" => 5.5, "fallas" => 2],
                        ];

                        foreach($estudiantes as $e): 
                            $es_aprobado = $e['nota'] >= 6.4;
                            $color_nota = $es_aprobado ? 'text-blue-700' : 'text-red-600';
                            $desempeno = "BAJO";
                            if($e['nota'] >= 9.0) $desempeno = "SUPERIOR";
                            else if($e['nota'] >= 8.0) $desempeno = "ALTO";
                            else if($e['nota'] >= 6.4) $desempeno = "BÁSICO";
                        ?>
                        <tr class="border-b hover:bg-blue-50/50 transition-colors group">
                            <td class="p-3 text-center text-gray-400 font-mono text-xs"><?php echo $e['id']; ?></td>
                            <td class="p-3 font-semibold text-slate-700 uppercase text-[11px]"><?php echo $e['nombre']; ?></td>
                            <td class="p-2">
                                <input type="number" step="0.1" value="<?php echo number_format($e['nota'], 1); ?>" 
                                    class="w-full bg-transparent text-center font-bold <?php echo $color_nota; ?> border-b-2 border-transparent focus:border-blue-500 focus:bg-white outline-none py-1 transition-all">
                            </td>
                            <td class="p-2">
                                <input type="number" value="<?php echo $e['fallas']; ?>" 
                                    class="w-full bg-transparent text-center text-gray-500 border-none outline-none focus:bg-white rounded">
                            </td>
                            <td class="p-3 text-center">
                                <?php
                                    $badge_class = "bg-red-100 text-red-700";
                                    if($desempeno == "SUPERIOR") $badge_class = "bg-emerald-100 text-emerald-700";
                                    else if($desempeno == "ALTO") $badge_class = "bg-blue-100 text-blue-700";
                                    else if($desempeno == "BÁSICO") $badge_class = "bg-orange-100 text-orange-700";
                                ?>
                                <span class="text-[9px] px-3 py-1 rounded-full font-black uppercase <?php echo $badge_class; ?>">
                                    <?php echo $desempeno; ?>
                                </span>
                            </td>
                            <td class="p-3 text-center text-gray-300">
                                <div class="flex justify-center gap-2">
                                    <button title="Observaciones" class="hover:text-blue-600"><i class="far fa-comment-dots"></i></button>
                                    <button title="Historial" class="hover:text-slate-600"><i class="fas fa-history"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Informativo -->
        <div class="bg-white/80 backdrop-blur mt-4 p-3 rounded-lg border border-gray-200 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4 text-[11px] text-gray-500">
                <span><i class="fas fa-users mr-1"></i> Total Estudiantes: <strong><?php echo count($estudiantes); ?></strong></span>
                <span class="w-px h-3 bg-gray-300"></span>
                <span class="text-emerald-600"><i class="fas fa-check-circle mr-1"></i> Conexión activa: <strong>db.php</strong></span>
            </div>
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                Aula Virtual © 2026
            </div>
        </div>
    </div>

</body>
</html>