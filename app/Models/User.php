<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar_url',
        'is_active',
        'plain_password',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function judge(): HasOne
    {
        return $this->hasOne(Judge::class);
    }

    public function ledGroup(): HasOne
    {
        return $this->hasOne(Group::class, 'leader_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'program_coordinator', 'stage_coordinator', 'program_committee']);
    }

    public function isProgramCommittee(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'program_committee', 'program_coordinator']);
    }

    public function isJudge(): bool
    {
        return $this->role === 'judge';
    }

    public function isLeader(): bool
    {
        return $this->role === 'group_leader';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isGreenRoom(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'green_room_coordinator']);
    }

    public function isMediaTeam(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'media_team', 'media_manager']);
    }
}
