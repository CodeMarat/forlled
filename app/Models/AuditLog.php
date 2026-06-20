<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'page',
        'record_title',
        'event',
        'changed_fields',
        'old_values',
        'new_values',
        'request_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getChangedFieldsSummaryAttribute(): string
    {
        return implode(', ', $this->changed_fields ?? []);
    }

    public function getChangesPreviewAttribute(): string
    {
        $changes = collect($this->changed_fields ?? [])
            ->map(function (string $field): string {
                $oldValue = $this->formatValueForDisplay(data_get($this->old_values, $field));
                $newValue = $this->formatValueForDisplay(data_get($this->new_values, $field));

                return "{$field}: {$oldValue} -> {$newValue}";
            })
            ->implode(' | ');

        return $changes !== '' ? $changes : '—';
    }

    /**
     * @return array<string, string>
     */
    public function getOldValuesDisplayAttribute(): array
    {
        return $this->formatValuesForKeyValue($this->old_values);
    }

    /**
     * @return array<string, string>
     */
    public function getNewValuesDisplayAttribute(): array
    {
        return $this->formatValuesForKeyValue($this->new_values);
    }

    /**
     * @param  array<string, mixed> | null  $values
     * @return array<string, string>
     */
    protected function formatValuesForKeyValue(?array $values): array
    {
        if ($values === null || $values === []) {
            return [];
        }

        return collect($values)
            ->mapWithKeys(fn (mixed $value, string $key): array => [$key => $this->formatValueForDisplay($value)])
            ->all();
    }

    protected function formatValueForDisplay(mixed $value): string
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        if ($value === null || $value === '') {
            return '—';
        }

        return Str::of((string) $value)
            ->replace("\r\n", "\n")
            ->replace("\r", "\n")
            ->toString();
    }
}
