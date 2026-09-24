<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aula Primaria - Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
        <div class="text-center mb-8">
            <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Aula Primaria</h2>
            <p class="text-slate-400 text-sm mt-1">Ingresa al panel de control docente</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="p-3 mb-4 rounded-xl text-xs bg-red-50 text-red-700 border border-red-100 font-medium text-center">
                ❌ <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- autocomplete="off" evita que el navegador meta contraseñas viejas automáticamente -->
        <form action="controlador_login.php" method="POST" autocomplete="off" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-envelope"></i></span>
                    <input type="text" name="email" id="email" required autocomplete="off" placeholder="ejemplo@aula.com" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm text-slate-800">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Contraseña</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password" required autocomplete="new-password" placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-500 text-sm text-slate-800">
                </div>
            </div>

            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-all flex items-center justify-center gap-2 mt-2 shadow-md">
                Ingresar al Sistema <i class="fas fa-arrow-right text-xs"></i>
            </button>
        </form>
    </div>

</body>
</html>