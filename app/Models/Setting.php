<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    // Retrieve a setting by key with optional casting and default
    public static function get(string $key, $default = null, ?string $cast = null)
    {
        $setting = static::where('key', $key)->first();
        $value = $setting?->value;

        if ($value === null) {
            return $default;
        }

        $cast = $cast ?: $setting?->type;

        switch ($cast) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                $decoded = json_decode($value, true);
                return $decoded === null ? $default : $decoded;
            default:
                return $value;
        }
    }
}
