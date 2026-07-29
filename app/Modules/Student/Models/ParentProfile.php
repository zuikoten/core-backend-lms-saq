<?php

namespace Modules\Student\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentProfile extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'phone_number',
        'father_name',
        'mother_name',
        'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

     /**
     * Normalisasi nomor HP ke format 62xxxxxxxxxx setiap kali di-assign,
     * dari jalur mana pun (form, seeder, Tinker, import) — bukan cuma
     * yang lewat Form Request yang sudah punya normalisasi sendiri.
     */
    protected function phoneNumber(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if ($value === null) {
                    return null;
                }

                $digits = preg_replace('/\D/', '', $value);

                return str_starts_with($digits, '0')
                    ? '62'.substr($digits, 1)
                    : $digits;
            },
        );
    }
}
