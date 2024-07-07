<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;
    protected $primaryKey = 'orgId'; // Primary key is 'orgId'
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Key type is string
    protected $fillable = [
        'orgId', 'name', 'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user', 'organization_id', 'user_id');
    }
}
