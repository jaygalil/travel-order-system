<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelOrderParticipant extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'travel_order_id',
        'user_id',
        'employee_name',
        'employee_id',
        'position',
        'division_agency',
        'phone',
        'email',
        'is_primary',
        'special_requirements',
    ];
    
    protected $casts = [
        'is_primary' => 'boolean',
    ];
    
    /**
     * Get the travel order this participant belongs to
     */
    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }
    
    /**
     * Get the user record if linked to a system user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope to get primary participant
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
    
    /**
     * Scope to get secondary participants
     */
    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }
}
