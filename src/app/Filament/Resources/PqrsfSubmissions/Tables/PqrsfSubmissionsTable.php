<?php

namespace App\Filament\Resources\PqrsfSubmissions\Tables;

use App\Mail\PqrsfSubmissionMail;
use App\Models\FormField;
use App\Models\SubmissionLog;
use App\Services\FormFieldService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select as SelectInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PqrsfSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),
                TextColumn::make('sede.nombre')
                    ->sortable()
                    ->searchable()
                    ->label('Sede'),
                TextColumn::make('field_values')
                    ->label('Cliente')
                    ->getStateUsing(fn ($record) => $record->field_values['nombre_completo'] ?? '—')
                    ->searchable(),
                TextColumn::make('field_values')
                    ->label('Opción')
                    ->getStateUsing(fn ($record) => $record->field_values['opcion_a_calificar'] ?? '—')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Queja' => 'danger',
                        'Reclamo' => 'warning',
                        'Petición' => 'info',
                        'Sugerencia' => 'gray',
                        'Felicitación' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::statusLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'validated' => 'info',
                        'sent' => 'success',
                        default => 'gray',
                    })
                    ->label('Estado')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Fecha'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'validated' => 'Validado',
                        'sent' => 'Enviado',
                    ]),
                SelectFilter::make('opcion_a_calificar')
                    ->label('Opción')
                    ->options(static::optionChoices())
                    ->query(function ($query, array $data): void {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }

                        $query->where('field_values->opcion_a_calificar', $data['value']);
                    }),
                SelectFilter::make('sede_id')
                    ->relationship('sede', 'nombre')
                    ->label('Sede'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->modalHeading(fn ($record): string => "Detalle PQRSF #{$record->id}")
                    ->modalDescription(fn ($record): string => 'Registrada el '.$record->created_at->format('d/m/Y H:i').' en '.($record->sede?->nombre ?? 'sede no disponible'))
                    ->modalWidth(Width::SixExtraLarge)
                    ->modalCancelActionLabel('Cerrar')
                    ->extraModalFooterActions(fn (): array => [
                        static::changeOptionAction(),
                        static::changeRatingsAction(),
                    ])
                    ->schema(fn ($record): array => [
                        View::make('filament.pqrsf-submissions.view-modal')
                            ->viewData(static::getViewData($record)),
                    ]),
                Action::make('validate')
                    ->label('Validar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'validated']);

                        SubmissionLog::create([
                            'submission_id' => $record->id,
                            'user_id' => auth()->id(),
                            'action' => 'validated',
                            'notas' => 'Formulario validado',
                        ]);

                        Notification::make()->title('Formulario validado correctamente')->success()->send();
                    }),
                Action::make('send')
                    ->label('Enviar a destinatarios')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'validated')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $recipients = $record->sede->sedeRecipients()->where('activo', true)->get();

                        if ($recipients->isEmpty()) {
                            Notification::make()
                                ->title('No hay destinatarios configurados para esta sede')
                                ->danger()
                                ->send();

                            return;
                        }

                        $sent = [];
                        $failed = [];

                        foreach ($recipients as $recipient) {
                            try {
                                Mail::to($recipient->email, $recipient->nombre)
                                    ->send(new PqrsfSubmissionMail($record));

                                $sent[] = $recipient->email;
                            } catch (Throwable $exception) {
                                report($exception);
                                $failed[] = $recipient->email;
                            }
                        }

                        if ($failed !== []) {
                            SubmissionLog::create([
                                'submission_id' => $record->id,
                                'user_id' => auth()->id(),
                                'action' => 'send_failed',
                                'notas' => 'Enviados: '.implode(', ', $sent).'. Fallidos: '.implode(', ', $failed),
                            ]);

                            Notification::make()
                                ->title('No se pudo enviar a todos los destinatarios')
                                ->body('Fallidos: '.implode(', ', $failed))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update(['status' => 'sent']);

                        SubmissionLog::create([
                            'submission_id' => $record->id,
                            'user_id' => auth()->id(),
                            'action' => 'sent',
                            'notas' => 'Enviado a '.implode(', ', $sent),
                        ]);

                        Notification::make()
                            ->title('Formulario enviado a '.$recipients->count().' destinatario(s)')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                // No bulk actions for now
            ])
            ->recordUrl(null);
    }

    protected static function changeOptionAction(): Action
    {
        return Action::make('changeOption')
            ->label('Cambiar opción')
            ->icon('heroicon-o-pencil-square')
            ->color('warning')
            ->visible(fn ($record): bool => $record->status === 'pending')
            ->modalHeading(fn ($record): string => "Cambiar opción PQRSF #{$record->id}")
            ->modalDescription('Solo se puede reclasificar una PQRSF mientras está pendiente de validación.')
            ->modalWidth(Width::Medium)
            ->modalSubmitActionLabel('Guardar cambio')
            ->fillForm(fn ($record): array => [
                'opcion_a_calificar' => $record->field_values['opcion_a_calificar'] ?? null,
            ])
            ->schema([
                SelectInput::make('opcion_a_calificar')
                    ->label('Opción a Calificar')
                    ->options(static::optionChoices())
                    ->required()
                    ->native(false),
            ])
            ->action(function ($record, array $data): void {
                if ($record->status !== 'pending') {
                    Notification::make()
                        ->title('No se puede cambiar la opción')
                        ->body('La PQRSF ya no está pendiente de validación.')
                        ->danger()
                        ->send();

                    return;
                }

                $values = $record->field_values ?? [];
                $previousOption = $values['opcion_a_calificar'] ?? null;
                $newOption = $data['opcion_a_calificar'];

                if ($previousOption === $newOption) {
                    Notification::make()
                        ->title('La opción no cambió')
                        ->info()
                        ->send();

                    return;
                }

                $values['opcion_a_calificar'] = $newOption;
                $record->update(['field_values' => $values]);

                SubmissionLog::create([
                    'submission_id' => $record->id,
                    'user_id' => auth()->id(),
                    'action' => 'option_changed',
                    'notas' => 'Opción cambiada de '.static::displayValue($previousOption).' a '.$newOption,
                ]);

                Notification::make()
                    ->title('Opción actualizada')
                    ->success()
                    ->send();
            });
    }

    protected static function changeRatingsAction(): Action
    {
        return Action::make('changeRatings')
            ->label('Cambiar calificaciones')
            ->icon('heroicon-o-star')
            ->color('warning')
            ->visible(fn ($record): bool => $record->status === 'pending')
            ->modalHeading(fn ($record): string => "Cambiar calificaciones PQRSF #{$record->id}")
            ->modalDescription('Solo se pueden cambiar las calificaciones mientras la PQRSF está pendiente de validación.')
            ->modalWidth(Width::Medium)
            ->modalSubmitActionLabel('Guardar calificaciones')
            ->fillForm(fn ($record): array => [
                'calificacion_ambientacion' => $record->field_values['calificacion_ambientacion'] ?? null,
                'calificacion_atencion' => $record->field_values['calificacion_atencion'] ?? null,
                'calificacion_comida' => $record->field_values['calificacion_comida'] ?? null,
                'calificacion_tiempo' => $record->field_values['calificacion_tiempo'] ?? null,
            ])
            ->schema([
                SelectInput::make('calificacion_ambientacion')
                    ->label('Ambientación')
                    ->options(static::ratingChoices())
                    ->required()
                    ->native(false),
                SelectInput::make('calificacion_atencion')
                    ->label('Atención a la Mesa')
                    ->options(static::ratingChoices())
                    ->required()
                    ->native(false),
                SelectInput::make('calificacion_comida')
                    ->label('Calidad de la Comida')
                    ->options(static::ratingChoices())
                    ->required()
                    ->native(false),
                SelectInput::make('calificacion_tiempo')
                    ->label('Tiempo de Entrega')
                    ->options(static::ratingChoices())
                    ->required()
                    ->native(false),
            ])
            ->action(function ($record, array $data): void {
                if ($record->status !== 'pending') {
                    Notification::make()
                        ->title('No se pueden cambiar las calificaciones')
                        ->body('La PQRSF ya no está pendiente de validación.')
                        ->danger()
                        ->send();

                    return;
                }

                $ratingKeys = [
                    'calificacion_ambientacion' => 'Ambientación',
                    'calificacion_atencion' => 'Atención',
                    'calificacion_comida' => 'Comida',
                    'calificacion_tiempo' => 'Tiempo',
                ];
                $values = $record->field_values ?? [];
                $changes = [];

                foreach ($ratingKeys as $key => $label) {
                    $previous = is_numeric($values[$key] ?? null) ? (int) $values[$key] : null;
                    $next = (int) $data[$key];

                    if ($previous !== $next) {
                        $changes[] = $label.': '.static::displayValue($previous).' a '.$next;
                    }

                    $values[$key] = $next;
                }

                if ($changes === []) {
                    Notification::make()
                        ->title('Las calificaciones no cambiaron')
                        ->info()
                        ->send();

                    return;
                }

                $record->update(['field_values' => $values]);

                SubmissionLog::create([
                    'submission_id' => $record->id,
                    'user_id' => auth()->id(),
                    'action' => 'ratings_changed',
                    'notas' => implode('; ', $changes),
                ]);

                Notification::make()
                    ->title('Calificaciones actualizadas')
                    ->success()
                    ->send();
            });
    }

    protected static function getViewData($record): array
    {
        $values = $record->field_values ?? [];
        $labels = static::fieldLabels();
        $option = static::displayValue($values['opcion_a_calificar'] ?? null);
        $ratings = static::ratings($values, $labels);
        $scoredRatings = array_filter($ratings, fn (array $rating): bool => $rating['score'] !== null);
        $average = $scoredRatings === []
            ? null
            : array_sum(array_column($scoredRatings, 'score')) / count($scoredRatings);

        $knownKeys = [
            'fecha',
            'sede_id',
            'nombre_completo',
            'numero_movil',
            'correo_electronico',
            'opcion_a_calificar',
            'nombre_mesero',
            'calificacion_ambientacion',
            'calificacion_atencion',
            'calificacion_comida',
            'calificacion_tiempo',
            'recomendaria',
            'observaciones',
            'medio_conocimiento',
            'autorizacion_datos',
        ];

        return [
            'summary' => [
                'id' => $record->id,
                'sede' => $record->sede?->nombre ?? '—',
                'statusLabel' => static::statusLabel($record->status),
                'statusTone' => static::statusTone($record->status),
                'optionLabel' => $option,
                'optionTone' => static::optionTone($option),
                'createdAt' => $record->created_at->format('d/m/Y H:i'),
                'averageRating' => $average === null ? '—' : number_format($average, 1, ',', '.'),
                'averageTone' => static::ratingTone($average),
            ],
            'contactItems' => static::makeItems($values, [
                'nombre_completo',
                'numero_movil',
                'correo_electronico',
            ], $labels),
            'experienceItems' => static::makeItems($values, [
                'fecha',
                'opcion_a_calificar',
                'nombre_mesero',
                'recomendaria',
                'medio_conocimiento',
                'autorizacion_datos',
            ], $labels),
            'ratings' => $ratings,
            'observations' => static::displayValue($values['observaciones'] ?? null),
            'systemItems' => [
                ['label' => 'Sede', 'value' => $record->sede?->nombre ?? '—'],
                ['label' => 'Estado', 'value' => static::statusLabel($record->status)],
                ['label' => 'Fecha de registro', 'value' => $record->created_at->format('d/m/Y H:i:s')],
                ['label' => 'Dirección IP', 'value' => static::displayValue($record->ip_address)],
                ['label' => 'Navegador', 'value' => static::displayValue($record->user_agent)],
            ],
            'extraItems' => collect($values)
                ->reject(fn (mixed $value, string $key): bool => in_array($key, $knownKeys, true))
                ->map(fn (mixed $value, string $key): array => [
                    'label' => static::fieldLabel($key, $labels),
                    'value' => static::displayValue($value),
                ])
                ->values()
                ->all(),
        ];
    }

    protected static function fieldLabels(): array
    {
        $defaults = collect(FormFieldService::defaultFields())
            ->mapWithKeys(fn (array $field, string $key): array => [$key => $field['label']])
            ->all();

        $configured = FormField::query()
            ->pluck('label', 'key')
            ->filter()
            ->all();

        return array_replace($defaults, $configured);
    }

    protected static function optionChoices(): array
    {
        return collect(FormFieldService::defaultFields()['opcion_a_calificar']['options'])
            ->mapWithKeys(fn (string $option): array => [$option => $option])
            ->all();
    }

    protected static function ratingChoices(): array
    {
        return collect(range(1, 5))
            ->mapWithKeys(fn (int $score): array => [$score => $score.' - '.static::ratingDescription($score)])
            ->all();
    }

    protected static function makeItems(array $values, array $keys, array $labels): array
    {
        return collect($keys)
            ->map(fn (string $key): array => [
                'label' => static::fieldLabel($key, $labels),
                'value' => static::displayValue($values[$key] ?? null),
            ])
            ->all();
    }

    protected static function ratings(array $values, array $labels): array
    {
        return collect([
            'calificacion_ambientacion',
            'calificacion_atencion',
            'calificacion_comida',
            'calificacion_tiempo',
        ])
            ->map(function (string $key) use ($values, $labels): array {
                $score = is_numeric($values[$key] ?? null) ? (int) $values[$key] : null;

                return [
                    'label' => match ($key) {
                        'calificacion_ambientacion' => 'Ambientación',
                        'calificacion_atencion' => 'Atención',
                        'calificacion_comida' => 'Comida',
                        'calificacion_tiempo' => 'Tiempo',
                    },
                    'fullLabel' => static::fieldLabel($key, $labels),
                    'score' => $score,
                    'display' => $score === null ? '—' : "{$score}/5",
                    'description' => static::ratingDescription($score),
                    'percent' => $score === null ? 0 : min(100, max(0, $score * 20)),
                    'tone' => static::ratingTone($score),
                ];
            })
            ->all();
    }

    protected static function fieldLabel(string $key, array $labels): string
    {
        return $labels[$key] ?? Str::of($key)->replace('_', ' ')->headline()->toString();
    }

    protected static function displayValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if (is_array($value)) {
            $items = collect($value)
                ->map(fn (mixed $item): string => static::displayValue($item))
                ->reject(fn (string $item): bool => $item === '—')
                ->all();

            return $items === [] ? '—' : implode(', ', $items);
        }

        if ($value === null || $value === '') {
            return '—';
        }

        return (string) $value;
    }

    protected static function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'validated' => 'Validado',
            'sent' => 'Enviado',
            default => 'Sin estado',
        };
    }

    protected static function statusTone(?string $status): string
    {
        return match ($status) {
            'pending' => 'status-pending',
            'validated' => 'status-validated',
            'sent' => 'status-sent',
            default => 'status-neutral',
        };
    }

    protected static function optionTone(string $option): string
    {
        return match ($option) {
            'Queja' => 'option-complaint',
            'Reclamo' => 'option-claim',
            'Petición' => 'option-request',
            'Sugerencia' => 'option-suggestion',
            'Felicitación' => 'option-praise',
            default => 'option-neutral',
        };
    }

    protected static function ratingDescription(int|float|null $score): string
    {
        return match ((int) round((float) $score)) {
            1 => 'Muy malo',
            2 => 'Malo',
            3 => 'Regular',
            4 => 'Bueno',
            5 => 'Excelente',
            default => 'Sin calificar',
        };
    }

    protected static function ratingTone(int|float|null $score): string
    {
        if ($score === null) {
            return 'rating-empty';
        }

        return match (true) {
            $score >= 4 => 'rating-good',
            $score >= 3 => 'rating-mid',
            default => 'rating-low',
        };
    }
}
