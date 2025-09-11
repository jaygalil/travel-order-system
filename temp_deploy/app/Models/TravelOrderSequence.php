<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelOrderSequence extends Model
{
    use HasFactory;
    
    protected $fillable = ['year', 'last_sequence'];
    
    /**
     * Get next sequence number for the given year
     */
    public static function getNextSequence($year)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($year) {
            $sequence = static::where('year', $year)->lockForUpdate()->first();
            
            if (!$sequence) {
                $sequence = static::create([
                    'year' => $year,
                    'last_sequence' => 1
                ]);
                return 1;
            }
            
            $nextSequence = $sequence->last_sequence + 1;
            $sequence->update(['last_sequence' => $nextSequence]);
            
            return $nextSequence;
        });
    }
}
