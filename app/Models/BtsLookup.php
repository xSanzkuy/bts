<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BtsLookup extends Model
{
    use HasFactory;

    protected $fillable = [
        'radio',
        'mcc',
        'mnc',
        'lac',
        'cid',
        'latitude',
        'longitude',
        'accuracy',
        'address',
        'range',
        'raw_response',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeByRadio(Builder $query, string $radio): Builder
    {
        return $query->where('radio', $radio);
    }

    public function scopeByOperator(Builder $query, int $mcc, int $mnc): Builder
    {
        return $query->where('mcc', $mcc)->where('mnc', $mnc);
    }

    public function getOperatorNameAttribute(): string
    {
        return $this->getOperatorName($this->mcc, $this->mnc);
    }

    public function getCountryNameAttribute(): string
    {
        return $this->getCountryName($this->mcc);
    }

    public static function getOperatorName(int $mcc, int $mnc): string
    {
        $operators = [
            '510-00' => 'PSN (Kominfo)',
            '510-01' => 'Indosat Ooredoo',
            '510-03' => 'StarOne',
            '510-07' => 'Telkomsel',
            '510-08' => 'Axis (XL Axiata)',
            '510-09' => 'Smartfren',
            '510-10' => 'Telkomsel',
            '510-11' => 'XL Axiata',
            '510-21' => 'Indosat Ooredoo',
            '510-27' => 'Sampoerna Telekom',
            '510-28' => 'Smartfren',
            '510-89' => 'Three (Hutchison)',
            '510-95' => 'Sampoerna Telekom',
            '510-96' => 'Three (Hutchison)',
            '510-97' => 'Sampoerna Telekom',
            '510-99' => 'Smartfren',
        ];

        $key = sprintf('%d-%02d', $mcc, $mnc);
        return $operators[$key] ?? "Unknown ($mcc-$mnc)";
    }

    public static function getCountryName(int $mcc): string
    {
        $countries = [
            510 => 'Indonesia',
            525 => 'Singapore',
            502 => 'Malaysia',
            520 => 'Thailand',
            515 => 'Philippines',
        ];

        return $countries[$mcc] ?? "Unknown ($mcc)";
    }

    public function getFormattedAccuracyAttribute(): string
    {
        if (!$this->accuracy) {
            return 'N/A';
        }

        if ($this->accuracy >= 1000) {
            return number_format($this->accuracy / 1000, 2) . ' km';
        }

        return number_format($this->accuracy) . ' m';
    }

    public function getGoogleMapsUrlAttribute(): string
    {
        if (!$this->latitude || !$this->longitude) {
            return '#';
        }

        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function hasValidLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    // ⭐ METHOD YANG HILANG - INI YANG PENTING!
    public static function generateCacheKey(string $radio, int $mcc, int $mnc, int $lac, int $cid): string
    {
        return "bts_lookup:{$radio}:{$mcc}:{$mnc}:{$lac}:{$cid}";
    }
}   