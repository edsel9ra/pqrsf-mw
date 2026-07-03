<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PQRSF') }}</title>
</head>
<body>
    <main style="min-height: 100vh; display: grid; place-items: center; font-family: sans-serif; background: #f8fafc; color: #1f2937; padding: 2rem;">
        <section style="max-width: 34rem; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 2rem; text-align: center; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);">
            <p style="margin: 0 0 0.5rem; color: #d97706; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;">Plataforma PQRSF</p>
            <h1 style="margin: 0; font-size: 2rem; line-height: 1.1;">Bienvenido</h1>
            <p style="margin: 1rem 0 1.5rem; color: #667085;">La plataforma redirige automáticamente al formulario público de PQRSF.</p>
            <a href="{{ route('pqrsf.create') }}" style="display: inline-flex; align-items: center; justify-content: center; min-height: 2.5rem; padding: 0 1rem; border-radius: 999px; background: #d97706; color: #ffffff; font-weight: 700; text-decoration: none;">Ir al formulario</a>
        </section>
    </main>
</body>
</html>
