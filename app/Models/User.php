<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_country_id',
        'phone',
        'country_id',
        'bio',
        'image',
        'password',
        'has_account',
        'profile_status',
        'email_verification_token',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'email_verification_token',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roleModel()
    {
        return $this->roles()->first();
    }

    public function nationality()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function phone_country()
    {
        return $this->hasOne(Country::class, 'id', 'phone_country_id');
    }

    public function join_requests()
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function last_join_request(){
        return $this->hasOne(JoinRequest::class)->ofMany([
            'id' => 'max',
        ], function ($query) {
            //
        });
    }

    public function notifications(){
        return $this->belongsToMany(Notification::class, 'notification_user','user_id','notification_id')->with('source','target')->withPivot('read');
    }
}
