<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validar seguridad: Solo estudiantes autorizados
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: login.php");
    exit();
}

$id_alumno = intval($_SESSION['id']);

// 2. Obtener datos del alumno para la bienvenida
$stmtUser = $pdo->prepare("SELECT nombre, grado FROM usuarios WHERE id = ?");
$stmtUser->execute([$id_alumno]);
$user_data = $stmtUser->fetch(PDO::FETCH_ASSOC);

$alumno_nombre = $user_data ? trim($user_data['nombre']) : 'Estudiante';
$grado = $user_data ? $user_data['grado'] : 'No asignado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Estudiante | IE Aula Primaria</title>
    <!-- Tailwind CSS para un diseño ultra moderno -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col justify-between">

    <!-- Barra de Navegación -->
    <nav class="bg-[#181e31] text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <?php if (file_exists('escudo_colegio.png')): ?>
                    <img src="escudo_colegio.png" alt="Escudo" class="h-10 w-auto">
                <?php endif; ?>
                <div>
                    <h1 class="text-sm md:text-base font-bold tracking-wide">IE AULA PRIMARIA</h1>
                    <p class="text-[10px] text-gray-300">Resolución Oficial No. 12345</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="hidden md:inline text-sm text-gray-300">
                    <i class="fa-solid fa-user-circle mr-1"></i> Estudiante Activo
                </span>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-2 rounded-lg transition duration-200 flex items-center space-x-1">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Salir</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="max-w-4xl mx-auto px-4 py-8 flex-grow w-full">
        
        <!-- Tarjeta de Bienvenida -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 text-[#181e31] p-4 rounded-full">
                    <i class="fa-solid fa-graduation-cap text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">¡Hola, <?php echo htmlspecialchars($alumno_nombre); ?>!</h2>
                    <p class="text-gray-500 mt-1">Grado escolar asignado: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($grado); ?> de Primaria</span></p>
                </div>
            </div>
            <div class="bg-green-50 text-green-700 border border-green-200 px-4 py-2 rounded-full text-xs font-semibold self-start md:self-center">
                <span class="h-2 w-2 inline-block rounded-full bg-green-500 mr-1.5 animate-pulse"></span> Año Lectivo 2026
            </div>
        </div>

        <!-- Sección de Consultas -->
        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- Tarjeta: Descargar Boletines -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-2 text-[#1c2c54] mb-4">
                        <i class="fa-solid fa-file-pdf text-xl text-red-500"></i>
                        <h3 class="font-bold text-lg text-gray-800">Descargar Boletín</h3>
                    </div>
                    <p class="text-gray-500 text-sm mb-6">
                        Selecciona el período académico del cual deseas generar, visualizar o descargar tu reporte oficial de calificaciones en formato PDF.
                    </p>
                </div>
                
                <!-- Formulario dinámico que conecta con nuestro generar_boletin.php -->
                <form action="generar_boletin.php" method="GET" target="_blank" class="space-y-4">
                    <div>
                        <label for="periodo" class="block text-xs font-bold text-gray-700 mb-1">SELECCIONE EL PERÍODO:</label>
                        <select name="periodo" id="periodo" class="w-full bg-gray-50 border border-gray-300 text-gray-800 rounded-lg p-2.5 focus:ring-2 focus:ring-[#1c2c54] focus:border-[#1c2c54] outline-none font-semibold">
                            <option value="1">1° Periodo Académico</option>
                            <option value="2">2° Periodo Académico</option>
                            <option value="3">3° Periodo Académico</option>
                            <option value="4">4° Periodo Académico</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-[#1c2c54] hover:bg-[#111c38] text-white font-bold py-3 px-4 rounded-lg shadow transition duration-200 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-download"></i>
                        <span>Generar Boletín en PDF</span>
                    </button>
                </form>
            </div>

            <!-- Tarjeta: Información de Ayuda -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-2 text-[#1c2c54] mb-4">
                        <i class="fa-solid fa-circle-info text-xl text-blue-500"></i>
                        <h3 class="font-bold text-lg text-gray-800">Soporte Escolar</h3>
                    </div>
                    <p class="text-gray-500 text-sm mb-4">
                        Si notas inconsistencias en tus calificaciones, notas pendientes por registrar o problemas para descargar tus archivos, por favor comunícate directamente con tu docente de grupo.
                    </p>
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 text-xs text-blue-800 space-y-1">
                        <p class="font-bold"><i class="fa-solid fa-school mr-1"></i> Contacto de Rectoría:</p>
                        <p>contacto@institucion-aulaprimaria.edu.co</p>
                    </div>
                </div>
                <div class="text-center text-xs text-gray-400 mt-6 md:mt-0">
                    Sistema de Gestión de Notas v1.2
                </div>
            </div>

        </div>

    </main>

    <!-- Pie de página -->
    <footer class="bg-gray-800 text-gray-400 text-xs py-4 text-center border-t border-gray-700 mt-12">
        <div class="max-w-6xl mx-auto px-4">
            &copy; 2026 Institución Educativa Aula Primaria. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>