<?php

namespace App\Http\Requests;

use App\Models\FormField;
use App\Services\FormFieldService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StorePqrsfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'sede_id' => [
                'required',
                Rule::exists('sedes', 'id')->where('activo', true),
            ],
        ];

        foreach ($this->activeFields() as $field) {
            if ($field->key === 'sede_id') {
                continue;
            }

            $rules[$field->key] = $this->rulesForField($field);

            if ($field->type === 'checkbox_list') {
                $rules[$field->key.'.*'] = $this->rulesForOption($field);
            }
        }

        return $rules;
    }

    public function normalizedData(): array
    {
        $validated = $this->validated();
        $data = [
            'sede_id' => (int) $validated['sede_id'],
        ];

        foreach ($this->activeFields() as $field) {
            $key = $field->key;

            if ($key === 'sede_id') {
                continue;
            }

            if ($key === 'fecha') {
                $data[$key] = $validated[$key] ?? now()->toDateString();

                continue;
            }

            if (! array_key_exists($key, $validated)) {
                continue;
            }

            $data[$key] = match ($field->type) {
                'rating' => (int) $validated[$key],
                'boolean' => (bool) filter_var($validated[$key], FILTER_VALIDATE_BOOLEAN),
                'checkbox_list' => array_values($validated[$key] ?? []),
                default => $validated[$key],
            };
        }

        return $data;
    }

    protected function activeFields(): Collection
    {
        return FormFieldService::activeFields();
    }

    protected function rulesForField(FormField $field): array
    {
        $rules = $field->requerido ? ['required'] : ['nullable'];

        return match ($field->type) {
            'email' => [...$rules, 'email', ...$this->configuredRules($field)],
            'tel', 'text' => [...$rules, 'string', ...$this->configuredRules($field)],
            'textarea' => [...$rules, 'string', ...$this->configuredRules($field)],
            'select' => [...$rules, 'string', ...$this->inRules($field), ...$this->configuredRules($field)],
            'rating' => [...$rules, 'integer', ...$this->configuredRules($field)],
            'boolean' => $field->key === 'autorizacion_datos'
                ? [...$rules, 'accepted']
                : [...$rules, 'boolean', ...$this->configuredRules($field)],
            'checkbox_list' => [
                ...$rules,
                'array',
                ...($field->requerido ? ['min:1'] : []),
                ...($this->optionValues($field) !== [] ? ['max:'.count($this->optionValues($field))] : []),
                ...$this->configuredRules($field),
            ],
            default => [...$rules, 'string', ...$this->configuredRules($field)],
        };
    }

    protected function rulesForOption(FormField $field): array
    {
        return ['string', ...$this->inRules($field)];
    }

    protected function configuredRules(FormField $field): array
    {
        if (! $field->validation_rules) {
            return [];
        }

        return is_array($field->validation_rules)
            ? $field->validation_rules
            : [$field->validation_rules];
    }

    protected function inRules(FormField $field): array
    {
        $options = $this->optionValues($field);

        return $options === [] ? [] : [Rule::in($options)];
    }

    protected function optionValues(FormField $field): array
    {
        return collect($field->options ?? [])
            ->filter(fn ($option): bool => is_scalar($option))
            ->map(fn ($option): string => (string) $option)
            ->values()
            ->all();
    }

    public function messages(): array
    {
        return [
            'sede_id.required' => 'Debe seleccionar una sede.',
            'sede_id.exists' => 'La sede seleccionada no es válida o está inactiva.',
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'numero_movil.required' => 'El número móvil es obligatorio.',
            'correo_electronico.required' => 'El correo electrónico es obligatorio.',
            'correo_electronico.email' => 'Ingrese un correo electrónico válido.',
            'opcion_a_calificar.required' => 'Debe seleccionar una opción a calificar.',
            'calificacion_ambientacion.required' => 'Debe calificar la ambientación.',
            'calificacion_atencion.required' => 'Debe calificar la atención a la mesa.',
            'calificacion_comida.required' => 'Debe calificar la calidad de la comida.',
            'calificacion_tiempo.required' => 'Debe calificar el tiempo de entrega.',
            'recomendaria.required' => 'Debe indicar si recomendaría el restaurante.',
            'autorizacion_datos.required' => 'Debe autorizar el manejo de datos personales.',
            'autorizacion_datos.accepted' => 'Debe aceptar la autorización de datos personales.',
        ];
    }
}
