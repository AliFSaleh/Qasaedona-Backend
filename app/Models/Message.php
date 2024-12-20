<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'message',
        'date',
        'status',
    ];

    protected $casts = [
        'status'   => 'boolean',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
