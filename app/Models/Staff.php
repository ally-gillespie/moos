<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = ['name', 'pin', 'role', 'active'];

    protected $hidden = ['pin'];

    protected static function booted(): void
    {
        static::saving(function (Staff $staff) {
            // Re-hash only if the pin was just set/changed (not already a bcrypt hash)
            if ($staff->isDirty('pin') && ! str_starts_with($staff->pin, '$2y$')) {
                $staff->pin = Hash::make($staff->pin);
            }
        });
    }

    public function checkPin(string $pin): bool
    {
        return $this->active && Hash::check($pin, $this->pin);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
