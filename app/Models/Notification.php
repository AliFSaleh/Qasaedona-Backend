<?php

namespace App\Models;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mosab\Translation\Middleware\RequestLanguage;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'data',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function target()
    {
        return $this->morphTo();
    }

    public static function create_notification($type, $source, $target, $data)
    {
        return Notification::create([
            'type'        => $type,
            'source_type' => $source?get_class($source):null,
            'source_id'   => $source?->id,
            'target_type' => $target?get_class($target):null,
            'target_id'   => $target?->id,
            'data'        => json_encode($data),
        ]);
    }
    
    public function users(){
        return $this->belongsToMany(User::class, 'notification_user')->withPivot('read');
    }

    public function send_to_user($user_id)
    {
        $this->users()->attach([$user_id =>['read' => 0]]);
        $this->send([$user_id]);
    }

    public function send_to_users($users_ids)
    {
        $data = [];
        foreach($users_ids as $user_id)
            $data[$user_id] = ['read' => 0];
        $this->users()->attach($data);
        $this->send($users_ids, true);
    }

    public function send_to_all_users()
    {
        $users_ids = DB::table('model_has_roles')->where('model_type', User::class)
                            ->whereIn('role_id', [2, 3])->pluck('model_id')->toArray();
        $this->send_to_users($users_ids);
        $this->send($users_ids);
    }

    public function send_to_admins()
    {
        $users_ids = DB::table('model_has_roles')->where('model_type', User::class)
                        ->whereNotIn('role_id', [2, 3])->pluck('model_id')->toArray();
        $data = [];
        foreach($users_ids as $user_id)
            $data[$user_id] = ['read' => 0];
        $this->users()->attach($data);
        $this->send($users_ids);
    }

    public function send($users_ids = null, $from_admin = false)
    {
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            config('services.fcm.credentialsPath')
        );

        $authToken = $credentials->fetchAuthToken()['access_token'];
        $url = "https://fcm.googleapis.com/v1/projects/".config('services.fcm.project_id')."/messages:send";

        $language = 'ar';
        $title = null;
        $message = null;
        $registration_ids = [];
        $icon = $this->get_icon();

        if($from_admin){
            $data = json_decode($this->data, true);
            $title = $data['title'];
            $message = $data['body'];
        } else {
            $title = $this->get_title($language);
            $message = $this->get_message($language);
        }
        $registration_ids['ar'] = [];

        if($users_ids)    
            $fcm_tokens = FCMToken::whereIn('user_id', $users_ids)->get();
        else
            $fcm_tokens = FCMToken::where('user_id', null)->get();

        foreach($fcm_tokens as $fcm_token)
            $registration_ids[$fcm_token->language][] = $fcm_token->fcm_token;

        if(count($registration_ids['ar'])>0)
            foreach($registration_ids['ar'] as $fcm_token){
                Http::withToken($authToken)->post($url, [
                    'message' => [
                            'token' => $fcm_token,
                            'data' => [
                                'title' => $title,
                                'body' => $message,
                                // 'icon'  => $icon,
                            ],
                            'notification' => [
                                'title' => $title,
                                'body'  => $message,
                                // 'icon'  => $icon,
                            ],
                            'android' => [
                                'priority' => "high",
                            ],
                            'apns' => [
                                'headers' => [
                                    'apns-priority' => '10',
                                ],
                                'payload' => [
                                    'aps' => [
                                        'content-available' => 1,
                                        'badge' => 5,
                                        'priority' => "high",
                                    ]
                                ]
                            ]
                        ]
                ]);
            }
    }

    public function get_title($language)
    {
        $params = [];

        if($this->type == "preparing")
            $params['order_id'] = ($this->target->id);
        if($this->type == "out_of_delivery")
            $params['order_id'] = ($this->target->id);
        if($this->type == "delivered")
            $params['order_id'] = ($this->target->id);
        if($this->type == "cancelled")
            $params['order_id'] = ($this->target->id);

        return __("notificationsTitle.".$this->type, $params, $language);
    }

    public function get_message($language)
    {
        $params = [];

        if($this->type == "new_order")
            $params['user_name'] = ($this->target->customer_full_name);

        return __("notificationsMessages.".$this->type, $params, $language);
    }

    public function get_icon(){
        $icon = storage_path('app/public/logo.jpg');
        return $icon;
    }

    public function get_destination_type(){
        $result = null;

        $source_user =['new_order'];
        $target_order =['preparing','out_of_delivery','delivered','cancelled'];

        if(in_array($this->type, $source_user))
            $result = 'order';
        if(in_array($this->type, $target_order))
            $result = 'order';

        return $result;
    }

    public function get_destination_id(){
        $result = null;

        $source_user =['new_order'];
        $target_order =['preparing','out_of_delivery','delivered','cancelled'];

        if(in_array($this->type, $source_user))
            $result = $this->target_id;
        if(in_array($this->type, $target_order))
            $result = $this->target_id;

        return $result;
    }

}
