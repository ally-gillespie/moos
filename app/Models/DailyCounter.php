<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DailyCounter extends Model
{
    protected $fillable = ['trading_date', 'last_number'];

    /**
     * Atomically get the next order number for the given trading date.
     * Uses a row lock inside a transaction so simultaneous web + POS
     * orders can never receive the same number.
     */
    public static function nextNumberFor(string $tradingDate): int
    {
        return DB::transaction(function () use ($tradingDate) {
            $counter = static::where('trading_date', $tradingDate)->lockForUpdate()->first();

            if (! $counter) {
                $counter = static::create([
                    'trading_date' => $tradingDate,
                    'last_number' => 0,
                ]);
                // Re-fetch with lock now that the row exists
                $counter = static::where('trading_date', $tradingDate)->lockForUpdate()->first();
            }

            $counter->last_number += 1;
            $counter->save();

            return $counter->last_number;
        });
    }
}
