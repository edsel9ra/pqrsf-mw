<?php

namespace App\Services;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class PqrsfCsvImportService
{
    public const CSV_ID_KEY = 'csv_id';

    public const VALID_STATUSES = ['pending', 'validated', 'sent'];

    /**
     * @return array<string, mixed>
     */
    public function analyze(string $path): array
    {
        [$summary] = $this->prepareImport($path);

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    public function import(string $path, string $status = 'pending'): array
    {
        if (! in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid PQRSF submission status.');
        }

        [$summary, $recordsToImport, $knownSedes] = $this->prepareImport($path);

        if ($summary['errors'] !== []) {
            return $summary;
        }

        $imported = 0;
        $createdSedes = 0;

        DB::transaction(function () use ($recordsToImport, $status, &$knownSedes, &$imported, &$createdSedes): void {
            foreach ($recordsToImport as $record) {
                $sedeKey = $this->sedeKey($record['sede']);

                if (! isset($knownSedes[$sedeKey])) {
                    $knownSedes[$sedeKey] = Sede::create([
                        'nombre' => $record['sede'],
                        'activo' => true,
                    ]);
                    $createdSedes++;
                }

                $fieldValues = $record['field_values'];
                $fieldValues['sede_id'] = $knownSedes[$sedeKey]->id;
                $fieldValues[self::CSV_ID_KEY] = $record['csv_id'];

                $submission = new PqrsfSubmission([
                    'sede_id' => $knownSedes[$sedeKey]->id,
                    'field_values' => $fieldValues,
                    'status' => $status,
                    'ip_address' => null,
                    'user_agent' => 'CSV import',
                ]);

                $submission->created_at = $record['created_at'];
                $submission->updated_at = $record['created_at'];
                $submission->save();

                $imported++;
            }
        });

        $summary['imported'] = $imported;
        $summary['created_sedes'] = $createdSedes;

        return $summary;
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, array<string, mixed>>, 2: array<string, Sede>}
     */
    private function prepareImport(string $path): array
    {
        $summary = $this->emptySummary();

        if (! is_readable($path)) {
            $summary['errors'][] = [0, 'No se puede leer el archivo.', 0];

            return [$summary, [], []];
        }

        [$records, $errors] = $this->parseFile($path);

        $summary['records_count'] = count($records);
        $summary['errors'] = $errors;

        if ($errors !== []) {
            return [$summary, [], []];
        }

        $existingCsvIds = $this->existingCsvIds();
        $recordsToImport = array_values(array_filter(
            $records,
            fn (array $record): bool => ! isset($existingCsvIds[$record['csv_id']]),
        ));

        $knownSedes = $this->sedesByName();
        $sedeNames = collect($recordsToImport)
            ->pluck('sede')
            ->unique()
            ->sort()
            ->values();
        $missingSedes = $sedeNames
            ->reject(fn (string $sede): bool => isset($knownSedes[$this->sedeKey($sede)]))
            ->values()
            ->all();

        $summary['new_count'] = count($recordsToImport);
        $summary['duplicate_count'] = count($records) - count($recordsToImport);
        $summary['missing_sedes_count'] = count($missingSedes);
        $summary['missing_sedes'] = $missingSedes;

        return [$summary, $recordsToImport, $knownSedes];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySummary(): array
    {
        return [
            'records_count' => 0,
            'new_count' => 0,
            'duplicate_count' => 0,
            'missing_sedes_count' => 0,
            'missing_sedes' => [],
            'errors' => [],
            'imported' => 0,
            'created_sedes' => 0,
        ];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<int, mixed>>}
     */
    private function parseFile(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return [[], [[0, 'No se pudo abrir el archivo.', 0]]];
        }

        $records = [];
        $errors = [];
        $buffer = null;
        $bufferLine = null;
        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $columns = $this->cleanColumns($this->parsePhysicalLine($line));

            if ($lineNumber === 1) {
                continue;
            }

            if ($columns === [] || $this->isEmptyRow($columns)) {
                continue;
            }

            if ($this->isStartRow($columns)) {
                $this->appendNormalizedRecord($buffer, $bufferLine, $records, $errors);
                $buffer = $columns;
                $bufferLine = $lineNumber;

                continue;
            }

            if ($buffer === null) {
                $errors[] = [$lineNumber, 'Linea de continuacion sin registro inicial.', count($columns)];

                continue;
            }

            if (($columns[0] ?? null) === '') {
                array_shift($columns);
            }

            $buffer = array_merge($buffer, $columns);
        }

        fclose($handle);

        $this->appendNormalizedRecord($buffer, $bufferLine, $records, $errors);

        return [$records, $errors];
    }

    /**
     * @return array<int, string>
     */
    private function parsePhysicalLine(string $line): array
    {
        $outerColumns = str_getcsv($line, ';');
        $outerColumns = array_values(array_filter(
            $outerColumns,
            fn (?string $column): bool => $column !== null && $column !== '',
        ));

        if ($outerColumns === []) {
            return [];
        }

        return str_getcsv(implode(';', $outerColumns), ',');
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function cleanColumns(array $columns): array
    {
        return array_map(fn (string $value): string => $this->cleanText($value), $columns);
    }

    private function cleanText(string $value): string
    {
        $value = $this->ensureUtf8($value);
        $value = str_replace(["\xEF\xBB\xBF", "\xc2\xa0"], ['', ' '], $value);

        return trim(trim($value), '"');
    }

    private function ensureUtf8(string $value): string
    {
        if ($value === '' || ! function_exists('mb_check_encoding') || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        foreach (['Windows-1252', 'ISO-8859-1'] as $encoding) {
            $converted = @mb_convert_encoding($value, 'UTF-8', $encoding);

            if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);

        return is_string($converted) ? $converted : $value;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function isEmptyRow(array $columns): bool
    {
        foreach ($columns as $column) {
            if ($column !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function isStartRow(array $columns): bool
    {
        return isset($columns[0]) && ctype_digit($columns[0]);
    }

    /**
     * @param  array<int, string>|null  $columns
     * @param  array<int, array<string, mixed>>  $records
     * @param  array<int, array<int, mixed>>  $errors
     */
    private function appendNormalizedRecord(?array $columns, ?int $lineNumber, array &$records, array &$errors): void
    {
        if ($columns === null || $lineNumber === null) {
            return;
        }

        [$record, $error] = $this->normalizeRecord($columns);

        if ($record === null) {
            $errors[] = [$lineNumber, $error, count($columns)];

            return;
        }

        $records[] = $record;
    }

    /**
     * @param  array<int, string>  $columns
     * @return array{0: array<string, mixed>|null, 1: string|null}
     */
    private function normalizeRecord(array $columns): array
    {
        if (count($columns) < 14) {
            return [null, 'Registro demasiado corto.'];
        }

        $csvId = $columns[0] ?? '';
        $sede = $columns[7] ?? '';

        if ($csvId === '' || ! ctype_digit($csvId)) {
            return [null, 'ID de CSV invalido.'];
        }

        if ($sede === '') {
            return [null, 'Sede vacia.'];
        }

        $rest = array_slice($columns, 13);
        $ratingsAt = $this->findRatingsOffset($rest);

        if ($ratingsAt === null) {
            return [null, 'No se encontro el bloque de calificaciones.'];
        }

        $createdAt = $this->parseDateTime($columns[2] ?? '')
            ?? $this->parseDateTime($columns[1] ?? '')
            ?? $this->parseDateTime($columns[6] ?? '')
            ?? CarbonImmutable::now();
        $tail = array_slice($rest, $ratingsAt + 5);
        $authorization = '';
        $medium = '';

        if (count($tail) >= 2) {
            $authorization = (string) array_pop($tail);
            $medium = (string) array_pop($tail);
        }

        $fieldValues = [
            'fecha' => $this->normalizeDate($columns[6] ?? '') ?? $createdAt->toDateString(),
            'nombre_completo' => $columns[8] ?? '',
            'numero_movil' => $columns[9] ?? '',
            'correo_electronico' => $columns[10] ?? '',
            'opcion_a_calificar' => $this->normalizeOption($columns[11] ?? ''),
            'nombre_mesero' => $this->joinText(array_slice($rest, 0, $ratingsAt)),
            'calificacion_ambientacion' => (int) $rest[$ratingsAt],
            'calificacion_atencion' => (int) $rest[$ratingsAt + 1],
            'calificacion_comida' => (int) $rest[$ratingsAt + 2],
            'calificacion_tiempo' => (int) $rest[$ratingsAt + 3],
            'recomendaria' => $this->toBoolean($rest[$ratingsAt + 4]),
            'observaciones' => $this->joinText($tail),
            'medio_conocimiento' => $medium === '' ? [] : [$medium],
            'autorizacion_datos' => $this->toBoolean($authorization),
        ];

        return [[
            'csv_id' => $csvId,
            'sede' => $sede,
            'created_at' => $createdAt,
            'field_values' => $fieldValues,
        ], null];
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function findRatingsOffset(array $columns): ?int
    {
        $lastPossibleOffset = count($columns) - 5;

        for ($offset = 0; $offset <= $lastPossibleOffset; $offset++) {
            if (
                $this->isRating($columns[$offset])
                && $this->isRating($columns[$offset + 1])
                && $this->isRating($columns[$offset + 2])
                && $this->isRating($columns[$offset + 3])
                && $this->isBooleanLike($columns[$offset + 4])
            ) {
                return $offset;
            }
        }

        return null;
    }

    private function isRating(string $value): bool
    {
        return in_array($value, ['1', '2', '3', '4', '5'], true);
    }

    private function isBooleanLike(string $value): bool
    {
        return in_array($this->normalizedKey($value), ['si', 's', 'yes', 'true', '1', 'no', 'n', 'false', '0'], true);
    }

    private function toBoolean(string $value): bool
    {
        return in_array($this->normalizedKey($value), ['si', 's', 'yes', 'true', '1'], true);
    }

    private function normalizeOption(string $value): string
    {
        return match ($this->normalizedKey($value)) {
            'felicitacion', 'felicitaciones' => 'Felicitación',
            'peticion', 'peticiones' => 'Petición',
            'queja', 'quejas' => 'Queja',
            'reclamo', 'reclamos' => 'Reclamo',
            'sugerencia', 'sugerencias' => 'Sugerencia',
            default => Str::of($value)->lower()->headline()->toString(),
        };
    }

    private function normalizedKey(string $value): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower(Str::ascii($value))), '_');
    }

    /**
     * @param  array<int, string>  $parts
     */
    private function joinText(array $parts): string
    {
        $parts = array_values(array_filter($parts, fn (string $part): bool => $part !== ''));

        return implode(', ', $parts);
    }

    private function normalizeDate(string $value): ?string
    {
        return $this->parseDateTime($value)?->toDateString();
    }

    private function parseDateTime(string $value): ?CarbonImmutable
    {
        if ($value === '') {
            return null;
        }

        foreach (['n/j/Y g:i:s A', 'm/d/Y g:i:s A', 'n/j/Y H:i:s', 'm/d/Y H:i:s', 'Y-m-d H:i:s', 'Y-m-d'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat($format, $value);
            } catch (Throwable) {
                $date = false;
            }

            if ($date instanceof CarbonImmutable) {
                return $format === 'Y-m-d' ? $date->startOfDay() : $date;
            }
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, true>
     */
    private function existingCsvIds(): array
    {
        return PqrsfSubmission::query()
            ->get(['field_values'])
            ->map(fn (PqrsfSubmission $submission): mixed => $submission->field_values[self::CSV_ID_KEY] ?? null)
            ->filter(fn (mixed $csvId): bool => $csvId !== null && $csvId !== '')
            ->mapWithKeys(fn (mixed $csvId): array => [(string) $csvId => true])
            ->all();
    }

    /**
     * @return array<string, Sede>
     */
    private function sedesByName(): array
    {
        return Sede::query()
            ->get()
            ->mapWithKeys(fn (Sede $sede): array => [$this->sedeKey($sede->nombre) => $sede])
            ->all();
    }

    private function sedeKey(string $name): string
    {
        return $this->normalizedKey($name);
    }
}
