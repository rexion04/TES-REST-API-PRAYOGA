<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Tambahkan ini

class Transaction extends Model
{
    use HasUuids; // Gunakan ini agar ID otomatis jadi UUID

    protected $fillable = [
        'user_id', 
        'target_user_id', 
        'amount', 
        'type', 
        'status'
    ];
}