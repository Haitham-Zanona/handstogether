<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // In-request cache: loaded once, reused for all get() calls in the same request
    protected static array $loaded = [];
    protected static bool  $allLoaded = false;

    protected static function loadAll(): void
    {
        if (!static::$allLoaded) {
            static::$loaded    = static::pluck('value', 'key')->toArray();
            static::$allLoaded = true;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        static::loadAll();
        return array_key_exists($key, static::$loaded)
            ? static::$loaded[$key]
            : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        // Invalidate in-request cache so next get() reflects the update
        static::$loaded[$key] = $value;
    }
}
