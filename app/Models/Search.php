<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    use HasFactory;

    protected $fillable = [
        'radio_type',
        'mcc',
        'mnc',
        'lac',
        'cid',
        'latitude',
        'longitude',
        'accuracy',
        'address',
        'status',
        'error_message',
        'raw_response',
        'ip_address',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getOperatorNameAttribute(): string
    {
        $operators = [
            '10' => 'Telkomsel',
            '11' => 'XL Axiata',
            '01' => 'Indosat Ooredoo',
            '89' => 'Tri (3)',
            '27' => 'Smartfren',
        ];

        return $operators[$this->mnc] ?? "MNC {$this->mnc}";
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'ok');
    }

    public static function getStats()
    {
        $total = self::count();
        $successful = self::successful()->count();
        $successRate = $total > 0 ? round(($successful / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'successful' => $successful,
            'success_rate' => $successRate,
        ];
    }
}