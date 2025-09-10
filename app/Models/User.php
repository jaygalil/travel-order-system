<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'position',
        'approver_sequence',
        'approver_title',
        'department',
        'division_agency',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Travel orders submitted by this user
     */
    public function travelOrders()
    {
        return $this->hasMany(TravelOrder::class);
    }

    /**
     * Travel orders prepared by this user
     */
    public function preparedTravelOrders()
    {
        return $this->hasMany(TravelOrder::class, 'prepared_by_user_id');
    }

    /**
     * Approvals assigned to this user
     */
    public function approvals()
    {
        return $this->hasMany(TravelOrderApproval::class, 'approver_user_id');
    }
}
