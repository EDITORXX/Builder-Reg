<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'city',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function setMobileAttribute(?string $value): void
    {
        $this->attributes['mobile'] = self::normalizeMobile($value);
    }

    public static function normalizeMobile(?string $mobile): string
    {
        if ($mobile === null || $mobile === '') {
            return '';
        }
        $digits = preg_replace('/\D/', '', $mobile);
        return strlen($digits) >= 10 ? substr($digits, -10) : $digits;
    }
}
