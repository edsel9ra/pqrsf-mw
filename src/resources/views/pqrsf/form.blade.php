@extends('layouts.public')

@section('title', 'PQRSF - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 sm:px-6 lg:px-8 animate-fade-in">
    <div class="text-center mb-10 animate-slide-up">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4 shadow-sm">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ config('app.name') }}</h1>
        <p class="mt-2 text-gray-500 max-w-md mx-auto">Formulario de Peticiones, Quejas, Reclamos, Sugerencias y Felicitaciones</p>
    </div>

    <form action="{{ route('pqrsf.store') }}" method="POST" class="bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 p-6 sm:p-8 animate-form-card" style="animation-delay: 0.1s" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="flex items-center gap-2 mb-6">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <h2 class="text-base font-semibold text-gray-800">Datos de la PQRSF</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            @foreach($fields as $field)
                @php
                    $key = $field->key;
                    $label = $field->label;
                    $required = (bool) $field->requerido;
                    $options = array_values($field->options ?? []);
                    $fullWidth = in_array($field->type, ['textarea', 'rating', 'checkbox_list', 'boolean'], true) || in_array($key, ['fecha', 'sede_id'], true);
                @endphp

                <div class="{{ $fullWidth ? 'sm:col-span-2' : '' }} animate-slide-up-sm">
                    @if($key === 'fecha')
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
                        <div class="field-card rounded-xl border border-gray-200 bg-gray-50/50 p-3">
                            <span class="text-gray-500 text-sm">{{ now()->format('d/m/Y') }}</span>
                            <input type="hidden" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}">
                        </div>
                    @elseif($key === 'sede_id')
                        <label for="sede_id" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }} <span class="text-red-500">*</span></label>
                        <select name="sede_id" id="sede_id" required class="field-card w-full rounded-xl border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 px-4 py-2.5 text-gray-700 bg-white">
                            <option value="">Seleccione una sede</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}" @selected(old('sede_id') == $sede->id)>{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                    @elseif($field->type === 'textarea')
                        <label for="{{ $key }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
                        <textarea name="{{ $key }}" id="{{ $key }}" rows="4" @required($required)
                                  class="field-card w-full rounded-xl border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 px-4 py-2.5 text-gray-700 placeholder-gray-400 transition-all duration-200 resize-none"
                                  placeholder="Comparta cualquier comentario adicional...">{{ old($key) }}</textarea>
                    @elseif($field->type === 'select')
                        <label for="{{ $key }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
                        <select name="{{ $key }}" id="{{ $key }}" @required($required)
                                class="field-card w-full rounded-xl border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 px-4 py-2.5 text-gray-700 bg-white">
                            <option value="">Seleccione una opción</option>
                            @foreach($options as $option)
                                <option value="{{ $option }}" @selected(old($key) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif($field->type === 'rating')
                        @php
                            $ratingLabels = ['', 'Muy Malo', 'Malo', 'Regular', 'Bueno', 'Excelente'];
                            $ratingColors = ['', 'text-red-500', 'text-orange-500', 'text-yellow-500', 'text-lime-500', 'text-green-500'];
                        @endphp
                        <div class="p-4 rounded-xl bg-gray-50/50 border border-gray-100 transition-all duration-200 hover:border-gray-200" x-data="{ rating: @js((int) old($key, 0)), hover: 0 }" @mouseleave="hover = 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2.5">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
                            <div class="flex items-center gap-1 star-group">
                                <template x-for="star in 5" :key="star">
                                    <button type="button" @click="rating = star" @mouseenter="hover = star" class="star-btn focus:outline-none focus:ring-2 focus:ring-blue-500/30 rounded-md p-0.5">
                                        <svg class="w-7 h-7 fill-current transition-all duration-200"
                                             :class="{
                                                 'text-blue-400': star <= (hover || rating),
                                                 'text-gray-300': star > (hover || rating),
                                                 'drop-shadow-sm': star <= (hover || rating),
                                                 'scale-110': hover && star <= hover
                                             }"
                                             viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                </template>
                                <span class="ml-2.5 text-sm font-medium transition-all duration-200"
                                      x-text="rating ? rating + ' - ' + @js($ratingLabels)[rating] : 'Sin calificar'"
                                      :class="rating ? @js($ratingColors)[rating] : 'text-gray-400'"></span>
                            </div>
                            <input type="hidden" name="{{ $key }}" :value="rating">
                        </div>
                    @elseif($field->type === 'boolean')
                        @if($key === 'autorizacion_datos')
                            <div class="p-5 rounded-xl bg-gradient-to-br from-blue-50/50 to-white border border-blue-100">
                                <label class="inline-flex items-start cursor-pointer group">
                                    <input type="checkbox" name="{{ $key }}" value="1" {{ old($key) ? 'checked' : '' }} @required($required)
                                           class="mt-0.5 text-blue-500 focus:ring-blue-500 rounded transition-all duration-200 group-hover:scale-110">
                                    <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-900 transition-colors duration-200">
                                        Autorizo el manejo de mis datos personales de acuerdo con la política de tratamiento de datos del restaurante. @if($required)<span class="text-red-500">*</span>@endif
                                    </span>
                                </label>
                            </div>
                        @else
                            <label class="block text-sm font-medium text-gray-700 mb-2.5">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center px-4 py-2.5 rounded-xl border border-gray-200 cursor-pointer transition-all duration-200 hover:border-blue-300 hover:bg-blue-50/50 has-checked:border-blue-500 has-checked:bg-blue-50">
                                    <input type="radio" name="{{ $key }}" value="1" {{ old($key) == '1' ? 'checked' : '' }} @required($required) class="text-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-gray-700 text-sm font-medium">Sí</span>
                                </label>
                                <label class="inline-flex items-center px-4 py-2.5 rounded-xl border border-gray-200 cursor-pointer transition-all duration-200 hover:border-blue-300 hover:bg-blue-50/50 has-checked:border-blue-500 has-checked:bg-blue-50">
                                    <input type="radio" name="{{ $key }}" value="0" {{ old($key) == '0' ? 'checked' : '' }} @required($required) class="text-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-gray-700 text-sm font-medium">No</span>
                                </label>
                            </div>
                        @endif
                    @elseif($field->type === 'checkbox_list')
                        @php
                            $oldSelected = old($key, []);
                            $oldSelected = is_array($oldSelected) ? $oldSelected : [];
                        @endphp
                        <label class="block text-sm font-medium text-gray-700 mb-2.5">{{ $label }} @if($required)<span class="text-red-500">*</span>@else<span class="text-gray-400 font-normal">(puede seleccionar varias)</span>@endif</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ selected: @js($oldSelected) }">
                            @foreach($options as $option)
                                <label class="inline-flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all duration-200"
                                       :class="selected.includes(@js($option)) ? 'border-blue-500 bg-blue-50 shadow-sm scale-[1.02]' : 'border-gray-100 bg-gray-50/50 hover:border-gray-200 hover:bg-gray-50'">
                                    <input type="checkbox" name="{{ $key }}[]" value="{{ $option }}"
                                           @checked(in_array($option, $oldSelected, true))
                                           @click="selected = $el.checked ? [...selected, @js($option)] : selected.filter(item => item !== @js($option))"
                                           class="text-blue-500 focus:ring-blue-500 rounded">
                                    <span class="ml-2.5 text-gray-700 text-sm">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <label for="{{ $key }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
                        <input type="{{ $field->type === 'email' ? 'email' : ($field->type === 'tel' ? 'tel' : 'text') }}" name="{{ $key }}" id="{{ $key }}" value="{{ old($key) }}" @required($required)
                               class="field-card w-full rounded-xl border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 px-4 py-2.5 text-gray-700 placeholder-gray-400 transition-all duration-200">
                    @endif

                    @error($key)
                        <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            <button type="submit"
                    :disabled="submitting"
                    class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3.5 px-6 rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed shadow-md shadow-red-200 hover:shadow-lg hover:shadow-red-300 active:scale-[0.98]">
                <span x-show="!submitting" class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Enviar Formulario
                </span>
                <span x-show="submitting" class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Enviando...
                </span>
            </button>
        </div>
    </form>
</div>
@endsection
