<?php
session_start();
if (!isset($_SESSION['nombre'])) { header("Location: login.php"); exit(); }
$materia = isset($_GET['materia']) ? $_GET['materia'] : "Curso";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clase de <?php echo $materia; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex h-screen">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold text-slate-800 mb-4">Bienvenido a <?php echo $materia; ?></h2>
        <div class="bg-white p-8 rounded-2xl shadow-sm border">
            <p class="text-slate-600 italic">Aquí aparecerá el video de la clase y el material de estudio.</p>
            <div class="mt-10 aspect-video bg-slate-900 rounded-xl flex items-center justify-center text-white">
                <i class="fas fa-play-circle text-6xl opacity-50"></i>
            </div>
        </div>
    </main>
</body>
</html>