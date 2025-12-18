<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Wajib untuk UUID
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject; // Wajib untuk JWT

class User extends Authenticatable implements JWTSubject
{
    use HasUuids; // Agar ID otomatis jadi UUID

    protected $fillable = ['name', 'phone_number', 'password', 'balance'];

    // Dua fungsi di bawah ini wajib ada untuk JWT
    public function getJWTIdentifier() { return $this->getKey(); }
    public function getJWTCustomClaims() { return []; }
}