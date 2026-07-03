@extends('layouts.public')

@section('title', 'Gracias - ' . config('app.name'))

@section('content')
<div class="max-w-lg mx-auto px-4 py-16 sm:px-6 lg:px-8 text-center animate-fade-in">
    <div class="animate-scale-in mb-6">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full shadow-md animate-scale-in" style="animation-delay: 0.1s">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
    </div>
    <h1 class="text-3xl font-bold text-gray-900 mb-4 animate-slide-up-sm" style="animation-delay: 0.2s">¡Gracias por tu opinión!</h1>
    <p class="text-gray-500 mb-8 max-w-sm mx-auto leading-relaxed animate-slide-up-sm" style="animation-delay: 0.3s">
        Hemos recibido tu formulario correctamente. Tu opinión es muy importante para nosotros y nos ayuda a mejorar cada día.
    </p>
    <a href="{{ route('pqrsf.create') }}"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3.5 px-8 rounded-xl transition-all duration-300 shadow-md shadow-red-200 hover:shadow-lg hover:shadow-red-300 active:scale-[0.98] animate-slide-up-sm"
       style="animation-delay: 0.4s">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Enviar otro formulario
    </a>
</div>
@endsection
