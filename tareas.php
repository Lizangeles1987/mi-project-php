<?php
session_start();
if (!isset($_SESSION['nombre'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entregar Tareas - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8">
        <h2 class="text-3xl font-bold text-slate-800 mb-8">Entregar Mis Tareas</h2>

        <div class="max-w-4xl bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b bg-slate-50">
                <h3 class="font-bold">Tarea: Investigación de Derivadas</h3>
                <p class="text-sm text-slate-500">Materia: Matemáticas | Fecha límite: 25 de Octubre</p>
            </div>
            
            <form action="subir_tarea.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                <div class="border-2 border-dashed border-slate-200 rounded-xl p-10 text-center hover:border-blue-400 transition cursor-pointer">
                    <i class="fas fa-cloud-upload-alt text-4xl text-blue-500 mb-4"></i>
                    <p class="text-slate-600 block mb-2">Selecciona tu archivo PDF o Word</p>
                    <input type="file" name="archivo_tarea" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-200">
                    Enviar Tarea al Profesor
                </button>
            </form>
        </div>
    </main>
</body>
</html>