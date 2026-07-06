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
        <header class="px-4 pt-6 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-3xl justify-center">
                <a href="{{ route('pqrsf.create') }}"
                   class="inline-flex items-center rounded-2xl bg-white/85 px-5 py-3 shadow-sm ring-1 ring-gray-100 backdrop-blur transition duration-300 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                   aria-label="Ir al formulario PQRSF">
                    <img src="{{ asset('logo_mw.png') }}" alt="Logo MW" class="h-16 w-auto sm:h-20" decoding="async">
                </a>
            </div>
        </header>
        <main class="flex-1">
            @yield('content')
        </main>
        <footer class="text-center py-6 text-gray-400 text-xs">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </footer>
    </div>
</body>
</html>
