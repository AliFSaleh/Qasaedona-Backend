<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FCMToken extends Model
{
    use HasFactory;

    protected $table = 'fcm_tokens';

    protected $fillable =[
        'user_id',
        'device_token',
        'fcm_token',
        'language',
    ];

    public function notifications(){
        return $this->belongsToMany(Notification::class, 'notification_user','fcm_token_id','notification_id')->with('source','target')->withPivot('read');
    }
}
