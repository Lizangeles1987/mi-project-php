<?php
session_start();
include 'db.php'; // CONEXIÓN A LA BASE DE DATOS

if (!isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

// CONSULTA A LA BASE DE DATOS
$query = "SELECT * FROM cursos";
$resultado = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex h-screen">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-8">
            <h2 class="text-lg font-semibold text-slate-700">Panel de Control</h2>
            <div class="flex items-center space-x-4">
                <span class="italic text-slate-600">Hola, <?php echo $_SESSION['nombre']; ?></span>
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                    <?php echo substr($_SESSION['nombre'], 0, 1); ?>
                </div>
            </div>
        </header>

        <div class="p-8 overflow-y-auto">
            <h3 class="text-3xl font-bold text-slate-800 mb-8">Mis Cursos Reales</h3>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <?php 
                // EL CICLO MÁGICO: Crea una tarjeta por cada fila en la base de datos
                while($fila = mysqli_fetch_assoc($resultado)) { 
                ?>
                <a href="ver_curso.php?materia=<?php echo $fila['nombre']; ?>" class="block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 <?php echo $fila['color_bg']; ?> <?php echo $fila['color_text']; ?> rounded-xl flex items-center justify-center mb-5 text-2xl">
                        <i class="fas <?php echo $fila['icono']; ?>"></i>
                    </div>
                    <h4 class="font-bold text-xl text-slate-800"><?php echo $fila['nombre']; ?></h4>
                    <p class="text-slate-500 text-sm mb-6"><?php echo $fila['descripcion']; ?></p>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold">
                            <span>Progreso</span>
                            <span><?php echo $fila['progreso']; ?>%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full" style="width: <?php echo $fila['progreso']; ?>%"></div>
                        </div>
                    </div>
                </a>
                <?php } ?>

            </div>
        </div>
    </main>
</body>
</html>