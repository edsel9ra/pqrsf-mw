<?php

namespace App\Services;

use App\Models\Sede;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ReportFilters
{
    public static function rules(): array
    {
        return [
            'sede_id' => ['nullable', 'array'],
            'sede_id.*' => ['integer', 'distinct', 'exists:sedes,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    public static function validate(array $filters): array
    {
        $validated = Validator::make(self::prepare($filters), self::rules())->validate();
        $normalized = self::normalize($validated);

        self::validateDateRange($normalized);

        return $normalized;
    }

    public static function prepare(array $filters): array
    {
        return [
            'sede_id' => self::wrapSedeIds($filters['sede_id'] ?? null),
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ];
    }

    public static function normalize(array $filters): array
    {
        return [
            'sede_id' => self::normalizeSedeIds($filters['sede_id'] ?? null),
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ];
    }

    public static function validateDateRange(array $filters): void
    {
        $from = Carbon::parse($filters['date_from'] ?? now()->subDays(30)->toDateString());
        $to = Carbon::parse($filters['date_to'] ?? now()->toDateString());

        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages([
                'date_from' => 'Seleccione un periodo máximo de 1 año.',
            ]);
        }
    }

    public static function labels(array $filters): array
    {
        $parts = [];

        if ($filters['sede_id'] !== null) {
            $sedeNames = Sede::query()
                ->whereIn('id', $filters['sede_id'])
                ->orderBy('nombre')
                ->pluck('nombre')
                ->all();

            if (count($sedeNames) === 1) {
                $parts[] = 'Sede: '.$sedeNames[0];
            } elseif ($sedeNames !== []) {
                $parts[] = 'Sedes: '.implode(', ', $sedeNames);
            }
        }

        if ($filters['date_from']) {
            $parts[] = 'Desde: '.Carbon::parse($filters['date_from'])->format('d/m/Y');
        }

        if ($filters['date_to']) {
            $parts[] = 'Hasta: '.Carbon::parse($filters['date_to'])->format('d/m/Y');
        }

        return $parts;
    }

    private static function wrapSedeIds(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Arr::wrap($value);
    }

    private static function normalizeSedeIds(mixed $value): ?array
    {
        $ids = collect(Arr::wrap($value))
            ->filter(fn ($sedeId): bool => filled($sedeId))
            ->map(fn ($sedeId): int => (int) $sedeId)
            ->unique()
            ->values()
            ->all();

        return $ids !== [] ? $ids : null;
    }
}
