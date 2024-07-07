<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Organization;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $primaryKey = 'userId'; // Specify primary key
    public $incrementing = true; // enable auto-incrementing
    protected $keyType = 'string'; // Set key type to string

    protected $fillable = [
        'userId', 
        'firstName', 
        'lastName', 
        'email', 
        'password', 
        'phone',
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

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user', 'user_id', 'organization_id');
    }

    public function createOrganization()
{
    // Generate a UUID for orgId
    $orgId = \Illuminate\Support\Str::uuid()->toString();

    // Create the Organization record
    $organization = Organization::create([
        'orgId' => $orgId,
        'name' => $this->firstName . "'s Organization",
        'description' => '',
    ]);

    // Attach the organization to the user
    $this->organizations()->attach($organization->orgId);

    return $organization;
}


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
        ];
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
