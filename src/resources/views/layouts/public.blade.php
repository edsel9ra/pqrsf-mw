<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-blue-50 font-sans antialiased min-h-screen">
    <div class="min-h-screen flex flex-col">
        <main class="flex-1">
            @yield('content')
        </main>
        <footer class="text-center py-6 text-gray-400 text-xs">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </footer>
    </div>
</body>
</html>
